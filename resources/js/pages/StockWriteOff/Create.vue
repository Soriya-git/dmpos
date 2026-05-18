<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CalendarDays,
    Filter,
    PackageMinus,
    Plus,
    RotateCcw,
    Search,
    Trash2,
    X,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import AlertError from '@/components/AlertError.vue';
import InputError from '@/components/InputError.vue';
import ApprovalActionMenu from '@/components/master-data/ApprovalActionMenu.vue';
import SearchDropdown from '@/components/SearchDropdown.vue';
import TablePagination from '@/components/TablePagination.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { usePagination } from '@/composables/usePagination';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

type WriteOffStatus = 'draft' | 'approved' | 'rejected' | 'cancelled';

type InventoryBalance = {
    id: number;
    itemId: number;
    unitId: number;
    warehouseId: number;
    stockLocationId: number;
    itemCode: string;
    itemName: string;
    itemType: string;
    branch: string;
    warehouse: string;
    location: string;
    locationCode: string;
    locationType: string;
    unit: string;
    quantityOnHand: string;
    quantityAvailable: string;
    averageCost: string;
    stockValue: string;
};

type StockedLocation = {
    id: number;
    warehouseId: number;
    code: string;
    name: string;
    warehouse: string;
    branch: string;
};

type WarehouseOption = {
    id: number;
    name: string;
    branch: string;
};

type InventoryItem = {
    id: number;
    name: string;
    code: string;
    unitCode: string;
    cost: number;
    hasStock: boolean;
};

type WriteOffLine = {
    id: number;
    itemCode: string;
    itemName: string;
    unit: string;
    location?: string | null;
    locationCode?: string | null;
    systemQuantity: number;
    writeOffQuantity: number;
    unitCost: number;
    totalCost: number;
    reason?: string | null;
    note?: string | null;
};

type WriteOffRecord = {
    id: number;
    code: string;
    date?: string | null;
    displayDate?: string | null;
    warehouse: string;
    location: string;
    locationCode: string;
    branch: string;
    itemCount: number;
    totalQuantity: number;
    totalCost: number;
    reason?: string | null;
    createdBy: string;
    status: WriteOffStatus;
    lines: WriteOffLine[];
};

type FormLine = {
    item_id: number | '';
    stock_balance_id: number | '';
    quantity: number;
    note: string;
};

const props = defineProps<{
    writeOffs: WriteOffRecord[];
    inventory: InventoryBalance[];
    locations: StockedLocation[];
    warehouses: WarehouseOption[];
    items: InventoryItem[];
    nextWriteOffNo: string;
    filters: {
        search?: string | null;
        status?: string | null;
        location_id?: number | string | null;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Stock Operations' },
    { title: 'Stock Movements' },
    {
        title: 'Stock Write-off',
        href: '/stock-movements/write-off',
    },
    {
        title: 'Create',
        href: '/stock-movements/write-off/create',
    },
];

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const locationId = ref<number | string | ''>(props.filters.location_id ?? '');
const detailWriteOff = ref<WriteOffRecord | null>(null);
const page = usePage();

const {
    currentPage,
    totalRows,
    totalPages,
    pageStart,
    pageEnd,
    paginatedRows,
    goToPage,
    pageSize,
    setRowsPerPage,
} = usePagination(() => props.writeOffs, 10);

const today = () => {
    const date = new Date();
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
};

const makeLine = (): FormLine => ({
    item_id: '',
    stock_balance_id: '',
    quantity: 1,
    note: '',
});

const form = useForm({
    warehouse_id: (props.warehouses[0]?.id ?? '') as number | '',
    adjustment_date: today(),
    reason: 'Physical count discrepancy',
    note: '',
    lines: [makeLine()],
});

const totalWriteOffs = computed(() => props.writeOffs.length);
const draftWriteOffs = computed(
    () =>
        props.writeOffs.filter((writeOff) => writeOff.status === 'draft')
            .length,
);
const approvedWriteOffs = computed(
    () =>
        props.writeOffs.filter((writeOff) => writeOff.status === 'approved')
            .length,
);
const approvedCost = computed(() =>
    props.writeOffs
        .filter((writeOff) => writeOff.status === 'approved')
        .reduce(
            (total, writeOff) => total + Number(writeOff.totalCost || 0),
            0,
        ),
);

const warehouseOptions = computed(() =>
    props.warehouses.map((warehouse) => ({
        value: warehouse.id,
        label: warehouse.name,
        description: warehouse.branch,
    })),
);

const itemOptions = computed(() =>
    props.items.map((item) => ({
        value: item.id,
        label: item.name,
        description: item.code,
        meta: item.hasStock ? item.unitCode : 'No stock',
    })),
);

const warehouseInventory = computed(() =>
    props.inventory.filter(
        (balance) => Number(balance.warehouseId) === Number(form.warehouse_id),
    ),
);

const canSubmit = computed(
    () =>
        Boolean(form.warehouse_id) &&
        form.reason.trim().length > 0 &&
        form.lines.some((line) => line.stock_balance_id && line.quantity > 0),
);

const draftTotal = computed(() =>
    form.lines.reduce((total, line) => total + lineTotal(line), 0),
);

const validationErrors = computed(() =>
    Object.values(form.errors as Record<string, string>).filter(Boolean),
);

const pageErrors = computed(() =>
    Object.values((page.props.errors ?? {}) as Record<string, string>).filter(
        Boolean,
    ),
);

const firstLineError = computed(() => {
    const errors = form.errors as Record<string, string>;

    return (
        errors.lines ||
        Object.entries(errors).find(([key]) => key.startsWith('lines.'))?.[1]
    );
});

function money(value: number | string | null | undefined) {
    return Number(value ?? 0).toLocaleString(undefined, {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

function numberValue(value: number | string | null | undefined) {
    return Number(value ?? 0).toLocaleString(undefined, {
        minimumFractionDigits: 0,
        maximumFractionDigits: 4,
    });
}

function statusLabel(value: string) {
    const labels: Record<string, string> = {
        draft: 'Draft',
        approved: 'Approved',
        rejected: 'Rejected',
        cancelled: 'Cancelled',
    };

    return labels[value] ?? value.replaceAll('_', ' ');
}

function statusClass(value: string) {
    const classes: Record<string, string> = {
        draft: 'bg-amber-100 text-amber-700',
        approved: 'bg-green-100 text-green-700',
        rejected: 'bg-red-100 text-red-700',
        cancelled: 'bg-slate-100 text-slate-600',
    };

    return classes[value] ?? 'bg-slate-100 text-slate-600';
}

function applyFilters() {
    router.get(
        '/stock-movements/write-off',
        {
            search: search.value || undefined,
            status: status.value || undefined,
            location_id: locationId.value || undefined,
        },
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        },
    );
}

function resetFilters() {
    search.value = '';
    status.value = '';
    locationId.value = '';

    router.get(
        '/stock-movements/write-off',
        {},
        { preserveScroll: true, replace: true },
    );
}

function showDashboard() {
    router.visit('/stock-movements/write-off');
}

function selectWarehouse() {
    form.lines = [makeLine()];
}

function addLine() {
    form.lines.push(makeLine());
}

function removeLine(index: number) {
    if (form.lines.length === 1) {
        form.lines = [makeLine()];
        return;
    }

    form.lines.splice(index, 1);
}

function selectedBalance(balanceId: number | '') {
    return props.inventory.find((balance) => balance.id === balanceId);
}

function itemHasStock(line: FormLine) {
    return warehouseInventory.value.some(
        (balance) => Number(balance.itemId) === Number(line.item_id),
    );
}

function locationOptionsFor(line: FormLine) {
    if (!line.item_id) {
        return [];
    }

    return warehouseInventory.value
        .filter((balance) => Number(balance.itemId) === Number(line.item_id))
        .map((balance) => ({
            value: balance.id,
            label: `${balance.locationCode} - ${balance.location}`,
            description: `${balance.quantityAvailable} ${balance.unit}`,
            meta: balance.warehouse,
        }));
}

function selectItem(line: FormLine) {
    line.stock_balance_id = '';
    line.quantity = 1;
}

function selectLocation(line: FormLine) {
    const balance = selectedBalance(line.stock_balance_id);

    if (!balance) return;

    line.quantity = Math.min(1, Number(balance.quantityAvailable || 0));
}

function lineTotal(line: FormLine) {
    const balance = selectedBalance(line.stock_balance_id);

    return Number(line.quantity || 0) * Number(balance?.averageCost ?? 0);
}

function availableFor(line: FormLine) {
    return Number(
        selectedBalance(line.stock_balance_id)?.quantityAvailable ?? 0,
    );
}

function quantityUsedFor(line: FormLine) {
    if (!line.stock_balance_id) {
        return Number(line.quantity || 0);
    }

    return form.lines
        .filter(
            (otherLine) => otherLine.stock_balance_id === line.stock_balance_id,
        )
        .reduce(
            (total, otherLine) => total + Number(otherLine.quantity || 0),
            0,
        );
}

function overLimitLine() {
    return form.lines.find((line) => {
        if (!line.stock_balance_id) {
            return false;
        }

        return quantityUsedFor(line) > availableFor(line);
    });
}

function submitWriteOff() {
    form.clearErrors();

    if (!canSubmit.value) {
        if (!form.warehouse_id) {
            form.setError('warehouse_id', 'Warehouse is required.');
        }

        if (!form.lines.some((line) => line.item_id)) {
            form.setError('lines', 'Add at least one inventory item.');
        }

        if (form.lines.some((line) => line.item_id && !line.stock_balance_id)) {
            form.setError('lines', 'Select a stocked location for each item.');
        }

        if (!form.reason.trim()) {
            form.setError('reason', 'Reason is required.');
        }

        return;
    }

    const invalidLine = overLimitLine();

    if (invalidLine) {
        const balance = selectedBalance(invalidLine.stock_balance_id);
        const itemName = balance?.itemName ?? 'Selected item';
        const available = numberValue(availableFor(invalidLine));
        const requested = numberValue(quantityUsedFor(invalidLine));

        form.setError(
            'lines',
            `${itemName} write-off quantity is ${requested}, but only ${available} ${balance?.unit ?? ''} is available.`,
        );
        return;
    }

    form.post('/stock-movements/write-off', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            form.warehouse_id = props.warehouses[0]?.id ?? '';
            form.adjustment_date = today();
            form.reason = 'Physical count discrepancy';
            form.lines = [makeLine()];
            showDashboard();
        },
    });
}

function openDetail(writeOff: WriteOffRecord) {
    detailWriteOff.value = writeOff;
}

function closeDetail() {
    detailWriteOff.value = null;
}

function isDraft(writeOff: WriteOffRecord) {
    return writeOff.status === 'draft';
}

function updateWriteOffStatus(
    writeOff: WriteOffRecord,
    action: 'approve' | 'reject' | 'cancel',
) {
    router.patch(
        `/stock-movements/write-off/${writeOff.id}/${action}`,
        {},
        {
            preserveScroll: true,
            preserveState: false,
        },
    );
}
</script>

<template>
    <Head title="Create Stock Write-off" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <main
            class="h-[calc(100dvh-4rem)] w-full [scrollbar-gutter:stable] overflow-y-scroll bg-[#f8fafc] p-4 text-slate-800 md:h-[calc(100dvh-5rem)] md:p-6 xl:p-8 2xl:p-10"
        >
            <section class="w-full">
                <div
                    class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-center"
                >
                    <div class="flex items-center gap-4">
                        <Button
                            type="button"
                            variant="outline"
                            class="h-10 w-10 rounded-full border-slate-200 bg-white p-0 text-slate-500 hover:text-[#007882]"
                            title="Back"
                            @click="showDashboard"
                        >
                            <ArrowLeft class="size-4" />
                        </Button>
                        <div>
                            <h2 class="text-2xl font-bold text-slate-800">
                                Create Stock Write-off
                            </h2>
                            <p class="text-sm text-slate-500">
                                {{ nextWriteOffNo }}
                            </p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <Button
                            type="button"
                            variant="ghost"
                            class="font-semibold text-slate-600 hover:text-red-500"
                            :disabled="form.processing"
                            @click="showDashboard"
                            >Cancel</Button
                        >
                        <Button
                            type="button"
                            class="rounded-lg bg-[#007882] px-6 font-bold text-white shadow-md hover:bg-[#006773]"
                            :disabled="form.processing"
                            @click="submitWriteOff"
                            >Save Draft</Button
                        >
                    </div>
                </div>

                <div class="grid gap-6 xl:grid-cols-4">
                    <div class="space-y-6 xl:col-span-1">
                        <div
                            class="overflow-hidden rounded-lg border border-slate-100 bg-white shadow-sm"
                        >
                            <div class="border-b border-slate-100 p-5">
                                <h2
                                    class="text-xs font-bold tracking-wider text-slate-700 uppercase"
                                >
                                    Write-off Details
                                </h2>
                            </div>
                            <div class="space-y-4 p-5">
                                <div>
                                    <label
                                        class="mb-1.5 block text-xs font-bold text-slate-500 uppercase"
                                    >
                                        Warehouse
                                    </label>
                                    <SearchDropdown
                                        v-model="form.warehouse_id"
                                        :options="warehouseOptions"
                                        placeholder="Select warehouse"
                                        search-placeholder="Search warehouse..."
                                        empty-text="No stocked warehouse found."
                                        @select="selectWarehouse"
                                    />
                                    <InputError
                                        :message="form.errors.warehouse_id"
                                    />
                                </div>

                                <div>
                                    <label
                                        class="mb-1.5 block text-xs font-bold text-slate-500 uppercase"
                                    >
                                        Write-off Date
                                    </label>
                                    <Input
                                        v-model="form.adjustment_date"
                                        type="date"
                                        class="h-10 rounded-lg border-slate-200"
                                    />
                                    <InputError
                                        :message="form.errors.adjustment_date"
                                    />
                                </div>

                                <div>
                                    <label
                                        class="mb-1.5 block text-xs font-bold text-slate-500 uppercase"
                                    >
                                        Reason
                                    </label>
                                    <select
                                        v-model="form.reason"
                                        class="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-[#007882] focus:ring-2 focus:ring-[#007882]/20"
                                    >
                                        <option>
                                            Physical count discrepancy
                                        </option>
                                        <option>Damaged stock</option>
                                        <option>Expired stock</option>
                                        <option>Obsolete stock</option>
                                        <option>Missing stock</option>
                                    </select>
                                    <InputError :message="form.errors.reason" />
                                </div>

                                <div>
                                    <label
                                        class="mb-1.5 block text-xs font-bold text-slate-500 uppercase"
                                    >
                                        Note
                                    </label>
                                    <textarea
                                        v-model="form.note"
                                        class="min-h-20 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#007882] focus:ring-2 focus:ring-[#007882]/20"
                                        placeholder="Optional write-off note"
                                    />
                                </div>
                            </div>
                        </div>

                        <div
                            class="rounded-lg bg-[#2a4858] p-6 text-white shadow-lg"
                        >
                            <div class="space-y-3">
                                <div>
                                    <span class="text-lg font-bold">
                                        Estimated Write-off Cost
                                    </span>
                                    <p class="mt-1 text-sm text-white/60">
                                        Draft status until approved.
                                    </p>
                                </div>
                                <span
                                    class="block text-2xl font-bold text-[#fafa6e]"
                                >
                                    {{ money(draftTotal) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6 xl:col-span-3">
                        <div
                            class="overflow-hidden rounded-lg border border-slate-100 bg-white shadow-sm"
                        >
                            <div
                                class="flex items-center justify-between border-b border-slate-100 bg-slate-50 p-4"
                            >
                                <h3
                                    class="text-xs font-bold tracking-wider text-slate-700 uppercase"
                                >
                                    Inventory Items
                                </h3>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead
                                        class="border-b border-slate-100 text-slate-500"
                                    >
                                        <tr>
                                            <th
                                                class="min-w-64 px-4 py-3 text-left font-semibold"
                                            >
                                                Item
                                            </th>
                                            <th
                                                class="min-w-56 px-4 py-3 text-left font-semibold"
                                            >
                                                Location
                                            </th>
                                            <th
                                                class="px-4 py-3 text-right font-semibold"
                                            >
                                                Available
                                            </th>
                                            <th
                                                class="px-4 py-3 text-center font-semibold"
                                            >
                                                Write-off Qty
                                            </th>
                                            <th
                                                class="px-4 py-3 text-right font-semibold"
                                            >
                                                Unit Cost
                                            </th>
                                            <th
                                                class="px-4 py-3 text-right font-semibold"
                                            >
                                                Total
                                            </th>
                                            <th class="w-12 px-4 py-3"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="(line, index) in form.lines"
                                            :key="index"
                                            class="border-b border-slate-50 last:border-0"
                                        >
                                            <td class="px-4 py-4">
                                                <SearchDropdown
                                                    v-model="line.item_id"
                                                    :options="itemOptions"
                                                    placeholder="Select item"
                                                    search-placeholder="Search item..."
                                                    empty-text="No item found."
                                                    input-class="border-transparent font-medium"
                                                    @select="selectItem(line)"
                                                />
                                            </td>
                                            <td class="px-4 py-4">
                                                <SearchDropdown
                                                    v-model="
                                                        line.stock_balance_id
                                                    "
                                                    :options="
                                                        locationOptionsFor(line)
                                                    "
                                                    :disabled="
                                                        !line.item_id ||
                                                        !itemHasStock(line)
                                                    "
                                                    placeholder="Select location"
                                                    search-placeholder="Search location..."
                                                    empty-text="No stock location for this item."
                                                    input-class="border-transparent font-medium"
                                                    @select="
                                                        selectLocation(line)
                                                    "
                                                />
                                            </td>
                                            <td
                                                class="px-4 py-4 text-right font-bold text-[#2a4858]"
                                            >
                                                {{
                                                    numberValue(
                                                        availableFor(line),
                                                    )
                                                }}
                                                {{
                                                    selectedBalance(
                                                        line.stock_balance_id,
                                                    )?.unit ?? ''
                                                }}
                                            </td>
                                            <td class="px-4 py-4">
                                                <Input
                                                    v-model.number="
                                                        line.quantity
                                                    "
                                                    type="number"
                                                    min="0.0001"
                                                    step="0.0001"
                                                    :max="availableFor(line)"
                                                    class="h-9 w-28 rounded border-slate-200 text-center"
                                                />
                                            </td>
                                            <td
                                                class="px-4 py-4 text-right font-mono font-bold text-slate-700"
                                            >
                                                {{
                                                    money(
                                                        selectedBalance(
                                                            line.stock_balance_id,
                                                        )?.averageCost,
                                                    )
                                                }}
                                            </td>
                                            <td
                                                class="px-4 py-4 text-right font-mono font-bold text-red-600"
                                            >
                                                {{ money(lineTotal(line)) }}
                                            </td>
                                            <td class="px-4 py-4 text-center">
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    class="h-8 w-8 rounded-lg p-0 text-slate-300 hover:bg-red-50 hover:text-red-500"
                                                    title="Remove item"
                                                    @click="removeLine(index)"
                                                >
                                                    <Trash2 class="size-4" />
                                                </Button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div
                                class="flex flex-col gap-3 border-t border-slate-100 px-4 py-4 sm:flex-row sm:items-center sm:justify-between"
                            >
                                <InputError :message="firstLineError" />
                                <Button
                                    type="button"
                                    variant="outline"
                                    class="h-10 rounded-lg border-[#007882]/20 bg-white font-bold text-[#007882]"
                                    @click="addLine"
                                >
                                    <Plus class="size-4" />
                                    Add Line
                                </Button>
                            </div>
                        </div>

                        <AlertError
                            v-if="validationErrors.length"
                            title="Stock write-off cannot be saved."
                            :errors="validationErrors"
                        />
                    </div>
                </div>
            </section>
        </main>
    </AppLayout>
</template>
