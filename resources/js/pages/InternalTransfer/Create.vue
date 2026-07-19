<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import {
    ArrowRightLeft,
    Boxes,
    CalendarDays,
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

type StockLocationOption = {
    id: number;
    warehouseId: number;
    code: string;
    name: string;
    warehouse: string;
    type: string;
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
    hasStock: boolean;
};

type TransferLine = {
    id: number;
    itemCode: string;
    itemName: string;
    unit: string;
    quantity: number | '';
    unitCost: number;
    totalCost: number;
    fromWarehouse?: string | null;
    fromLocation?: string | null;
    fromLocationCode?: string | null;
    fromLocationType?: string | null;
    toWarehouse?: string | null;
    toLocation?: string | null;
    toLocationCode?: string | null;
    toLocationType?: string | null;
    note?: string | null;
};

type TransferRecord = {
    id: number;
    code: string;
    date?: string | null;
    displayDate?: string | null;
    fromWarehouse: string;
    toWarehouse: string;
    fromLocation: string;
    toLocation: string;
    itemCount: number;
    totalQuantity: number;
    totalCost: number;
    note?: string | null;
    createdBy: string;
    status: string;
    lines: TransferLine[];
};

type FormLine = {
    item_id: number | '';
    from_warehouse_id: number | '';
    stock_balance_id: number | '';
    quantity: number;
    to_warehouse_id: number | '';
    to_location_id: number | '';
    note: string;
};

const props = defineProps<{
    transfers: TransferRecord[];
    inventory: InventoryBalance[];
    locations: StockLocationOption[];
    warehouses: WarehouseOption[];
    items: InventoryItem[];
    nextTransferNo: string;
    filters: {
        search?: string | null;
        location_id?: number | string | null;
        warehouse_id?: number | string | null;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Stock Operations' },
    { title: 'Stock Movements' },
    {
        title: 'Internal Transfer',
        href: '/stock-movements/internal-transfer',
    },
    {
        title: 'Create',
        href: '/stock-movements/internal-transfer/create',
    },
];

const search = ref(props.filters.search ?? '');
const warehouseId = ref<number | string | ''>(props.filters.warehouse_id ?? '');
const locationId = ref<number | string | ''>(props.filters.location_id ?? '');
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
} = usePagination(() => props.transfers, 10);

const allowedTypeLabels: Record<string, string> = {
    putaway: 'Putaway',
    damage: 'Damage',
    obsolete: 'Obsolete',
    scrap: 'Scrap',
};

const today = () => {
    const date = new Date();
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
};

const makeLine = (): FormLine => ({
    item_id: '',
    from_warehouse_id: '',
    stock_balance_id: '',
    quantity: '',
    to_warehouse_id: '',
    to_location_id: '',
    note: '',
});

const form = useForm({
    transfer_date: today(),
    note: '',
    lines: [makeLine()],
});

const totalTransfers = computed(() => props.transfers.length);
const totalQuantity = computed(() =>
    props.transfers.reduce(
        (total, transfer) => total + Number(transfer.totalQuantity || 0),
        0,
    ),
);
const totalCost = computed(() =>
    props.transfers.reduce(
        (total, transfer) => total + Number(transfer.totalCost || 0),
        0,
    ),
);
const crossWarehouseTransfers = computed(
    () =>
        props.transfers.filter(
            (transfer) => transfer.fromWarehouse !== transfer.toWarehouse,
        ).length,
);

const itemOptions = computed(() =>
    props.items.map((item) => ({
        value: item.id,
        label: item.name,
        description: item.code,
        meta: item.hasStock ? item.unitCode : 'No stock',
        disabled: !item.hasStock,
    })),
);

const warehouseOptions = computed(() =>
    props.warehouses.map((warehouse) => ({
        value: warehouse.id,
        label: warehouse.name,
        description: warehouse.branch,
    })),
);

const sourceWarehouseOptions = computed(() => {
    const availableWarehouseIds = new Set(
        props.inventory.map((balance) => Number(balance.warehouseId)),
    );

    return warehouseOptions.value.filter((warehouse) =>
        availableWarehouseIds.has(Number(warehouse.value)),
    );
});

const canSubmit = computed(
    () =>
        Boolean(form.transfer_date) &&
        form.lines.length > 0 &&
        form.lines.every(
            (line) =>
                line.item_id &&
                line.from_warehouse_id &&
                line.stock_balance_id &&
                line.quantity > 0 &&
                line.to_warehouse_id &&
                (Number(line.from_warehouse_id) !==
                    Number(line.to_warehouse_id) ||
                    line.to_location_id),
        ),
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

function decimalValue(value: number | string | null | undefined) {
    return Number(String(value ?? 0).replaceAll(',', ''));
}

function money(value: number | string | null | undefined) {
    return decimalValue(value).toLocaleString(undefined, {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

function numberValue(value: number | string | null | undefined) {
    return decimalValue(value).toLocaleString(undefined, {
        minimumFractionDigits: 0,
        maximumFractionDigits: 4,
    });
}

function locationTypeLabel(value: string | null | undefined) {
    return (
        allowedTypeLabels[value ?? ''] ??
        String(value ?? '').replaceAll('_', ' ')
    );
}

function locationTypeClass(value: string | null | undefined) {
    const classes: Record<string, string> = {
        putaway: 'bg-[#23aa8f]/10 text-[#007882]',
        damage: 'bg-red-100 text-red-700',
        obsolete: 'bg-amber-100 text-amber-700',
        scrap: 'bg-slate-200 text-slate-700',
    };

    return classes[value ?? ''] ?? 'bg-slate-100 text-slate-600';
}

function statusLabel(value: string) {
    const labels: Record<string, string> = {
        draft: 'Draft',
        approved: 'Approved',
        received: 'Completed',
        cancelled: 'Cancelled',
        rejected: 'Rejected',
    };

    return labels[value] ?? value.replaceAll('_', ' ');
}

function statusClass(value: string) {
    const classes: Record<string, string> = {
        draft: 'bg-amber-100 text-amber-700',
        approved: 'bg-blue-100 text-blue-700',
        received: 'bg-green-100 text-green-700',
        cancelled: 'bg-slate-100 text-slate-600',
        rejected: 'bg-red-100 text-red-700',
    };

    return classes[value] ?? 'bg-slate-100 text-slate-600';
}

function applyFilters() {
    router.get(
        '/stock-movements/internal-transfer',
        {
            search: search.value || undefined,
            warehouse_id: warehouseId.value || undefined,
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
    warehouseId.value = '';
    locationId.value = '';

    router.get(
        '/stock-movements/internal-transfer',
        {},
        { preserveScroll: true, replace: true },
    );
}

function showDashboard() {
    router.visit('/stock-movements/internal-transfer');
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
    return props.inventory.find((balance) => balance.id === Number(balanceId));
}

function itemHasStock(line: FormLine) {
    return props.inventory.some(
        (balance) =>
            Number(balance.itemId) === Number(line.item_id) &&
            (!line.from_warehouse_id ||
                Number(balance.warehouseId) === Number(line.from_warehouse_id)),
    );
}

function sourceLocationOptionsFor(line: FormLine) {
    if (!line.item_id || !line.from_warehouse_id) {
        return [];
    }

    return props.inventory
        .filter(
            (balance) =>
                Number(balance.itemId) === Number(line.item_id) &&
                Number(balance.warehouseId) === Number(line.from_warehouse_id),
        )
        .map((balance) => ({
            value: balance.id,
            label: `${balance.locationCode} - ${balance.location}`,
            description: `${balance.quantityAvailable} ${balance.unit}`,
            meta: locationTypeLabel(balance.locationType),
        }));
}

function destinationLocationOptionsFor(line: FormLine) {
    if (!line.to_warehouse_id) {
        return [];
    }

    return props.locations
        .filter(
            (location) =>
                Number(location.warehouseId) === Number(line.to_warehouse_id) &&
                Number(location.id) !==
                    Number(
                        selectedBalance(line.stock_balance_id)?.stockLocationId,
                    ),
        )
        .map((location) => ({
            value: location.id,
            label: `${location.code} - ${location.name}`,
            description: location.warehouse,
            meta: locationTypeLabel(location.type),
        }));
}

function selectItem(line: FormLine) {
    line.stock_balance_id = '';
    line.quantity = 1;
}

function selectSourceWarehouse(line: FormLine) {
    line.stock_balance_id = '';
    line.quantity = 1;
}

function selectSourceLocation(line: FormLine) {
    const balance = selectedBalance(line.stock_balance_id);

    if (!balance) return;

    line.item_id = balance.itemId;
    line.from_warehouse_id = balance.warehouseId;
    line.quantity = Math.min(1, decimalValue(balance.quantityAvailable));

    if (Number(line.to_location_id) === Number(balance.stockLocationId)) {
        line.to_location_id = '';
    }
}

function selectDestinationWarehouse(line: FormLine) {
    line.to_location_id = '';
}

function lineTotal(line: FormLine) {
    const balance = selectedBalance(line.stock_balance_id);

    return Number(line.quantity || 0) * decimalValue(balance?.averageCost);
}

function availableFor(line: FormLine) {
    return decimalValue(
        selectedBalance(line.stock_balance_id)?.quantityAvailable,
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

function sameLocationLine() {
    return form.lines.find((line) => {
        const balance = selectedBalance(line.stock_balance_id);

        return (
            balance &&
            line.to_location_id &&
            Number(balance.stockLocationId) === Number(line.to_location_id)
        );
    });
}

function submitTransfer() {
    form.clearErrors();

    if (!canSubmit.value) {
        if (!form.transfer_date) {
            form.setError('transfer_date', 'Transfer date is required.');
        }

        if (!form.lines.some((line) => line.item_id)) {
            form.setError('lines', 'Add at least one stocked item.');
        }

        if (
            form.lines.some(
                (line) =>
                    line.item_id &&
                    (!line.from_warehouse_id ||
                        !line.stock_balance_id ||
                        !line.to_warehouse_id ||
                        (Number(line.from_warehouse_id) ===
                            Number(line.to_warehouse_id) &&
                            !line.to_location_id)),
            )
        ) {
            form.setError(
                'lines',
                'Select the source and destination warehouse. A destination location is required only within the same warehouse.',
            );
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
            `${itemName} transfer quantity is ${requested}, but only ${available} ${balance?.unit ?? ''} is available.`,
        );
        return;
    }

    if (sameLocationLine()) {
        form.setError(
            'lines',
            'Source and destination location must be different for each row.',
        );
        return;
    }

    form.post('/stock-movements/internal-transfer', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            form.transfer_date = today();
            form.lines = [makeLine()];
            showDashboard();
        },
    });
}

function openDetail(transfer: TransferRecord) {
    router.visit(`/stock-movements/internal-transfer/${transfer.id}`);
}

function updateTransferStatus(
    transfer: TransferRecord,
    action: 'approve' | 'reject' | 'cancel',
) {
    router.patch(
        `/stock-movements/internal-transfer/${transfer.id}/${action}`,
        {},
        {
            preserveScroll: true,
            preserveState: false,
        },
    );
}
</script>

<template>
    <Head title="Create Internal Transfer" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #actions>
            <div class="flex gap-2">
                <Button
                    type="button"
                    variant="ghost"
                    class="h-9 font-semibold text-slate-600 hover:text-red-500"
                    :disabled="form.processing"
                    @click="showDashboard"
                >
                    Cancel
                </Button>
                <Button
                    type="button"
                    class="h-9 rounded-lg bg-[#007882] px-4 text-xs font-bold text-white shadow-md hover:bg-[#006773]"
                    :disabled="form.processing"
                    @click="submitTransfer"
                >
                    Save Transfer
                </Button>
            </div>
        </template>

        <main
            class="h-[calc(100dvh-4rem)] w-full [scrollbar-gutter:stable] overflow-y-scroll bg-[#f8fafc] p-4 text-slate-800 md:h-[calc(100dvh-5rem)] md:p-6 xl:p-8 2xl:p-10"
        >
            <section class="w-full">
                <div class="grid gap-5 xl:grid-cols-[22rem_minmax(0,1fr)]">
                    <div class="space-y-5 xl:order-2">
                        <div
                            class="rounded-lg border border-slate-100 bg-white shadow-sm"
                        >
                            <div
                                class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-start sm:justify-between"
                            >
                                <div>
                                    <h2 class="font-bold text-[#2a4858]">
                                        Transfer Lines
                                    </h2>
                                    <p class="mt-1 text-sm text-slate-500">
                                        Choose the source item, warehouse, and
                                        location on each row, then choose where
                                        it should move.
                                    </p>
                                </div>
                                <Button
                                    type="button"
                                    class="h-8 rounded bg-[#23aa8f] px-3 text-xs font-bold text-white hover:bg-[#1e917a]"
                                    @click="addLine"
                                >
                                    <Plus class="size-3.5" />
                                    Add Line
                                </Button>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full min-w-[1060px] text-left">
                                    <thead
                                        class="bg-slate-50 text-xs font-bold text-slate-500 uppercase"
                                    >
                                        <tr>
                                            <th class="w-[52%] px-4 py-3">
                                                Item
                                            </th>
                                            <th class="w-[16rem] px-4 py-3">
                                                Qty
                                            </th>
                                            <th class="w-[32%] px-4 py-3">
                                                Destination
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
                                                <div class="space-y-3">
                                                    <SearchDropdown
                                                        v-model="line.item_id"
                                                        :options="itemOptions"
                                                        placeholder="Select item"
                                                        search-placeholder="Search item..."
                                                        empty-text="No stocked item found."
                                                        input-class="border-transparent font-medium"
                                                        @select="
                                                            selectItem(line)
                                                        "
                                                    />
                                                    <div
                                                        class="grid gap-3 md:grid-cols-2"
                                                    >
                                                        <SearchDropdown
                                                            v-model="
                                                                line.from_warehouse_id
                                                            "
                                                            :options="
                                                                sourceWarehouseOptions
                                                            "
                                                            placeholder="From warehouse"
                                                            search-placeholder="Search warehouse..."
                                                            empty-text="No warehouse found."
                                                            input-class="border-transparent font-medium"
                                                            @select="
                                                                selectSourceWarehouse(
                                                                    line,
                                                                )
                                                            "
                                                        />
                                                        <SearchDropdown
                                                            v-model="
                                                                line.stock_balance_id
                                                            "
                                                            :options="
                                                                sourceLocationOptionsFor(
                                                                    line,
                                                                )
                                                            "
                                                            :disabled="
                                                                !line.item_id ||
                                                                !line.from_warehouse_id ||
                                                                !itemHasStock(
                                                                    line,
                                                                )
                                                            "
                                                            placeholder="From location"
                                                            search-placeholder="Search location..."
                                                            empty-text="No source stock found."
                                                            input-class="border-transparent font-medium"
                                                            @select="
                                                                selectSourceLocation(
                                                                    line,
                                                                )
                                                            "
                                                        />
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-4">
                                                <div class="space-y-2">
                                                    <Input
                                                        v-model.number="
                                                            line.quantity
                                                        "
                                                        type="number"
                                                        min="0.0001"
                                                        step="0.0001"
                                                        :max="
                                                            availableFor(line)
                                                        "
                                                        class="h-10 w-full rounded border-slate-200 text-center font-semibold"
                                                    />
                                                    <p
                                                        class="text-xs font-semibold text-slate-500"
                                                    >
                                                        Available:
                                                        <span
                                                            class="text-[#2a4858]"
                                                        >
                                                            {{
                                                                numberValue(
                                                                    availableFor(
                                                                        line,
                                                                    ),
                                                                )
                                                            }}
                                                            {{
                                                                selectedBalance(
                                                                    line.stock_balance_id,
                                                                )?.unit ?? ''
                                                            }}
                                                        </span>
                                                    </p>
                                                </div>
                                            </td>
                                            <td class="px-4 py-4">
                                                <div class="space-y-3">
                                                    <SearchDropdown
                                                        v-model="
                                                            line.to_warehouse_id
                                                        "
                                                        :options="
                                                            warehouseOptions
                                                        "
                                                        placeholder="Warehouse selection"
                                                        search-placeholder="Search warehouse..."
                                                        empty-text="No warehouse found."
                                                        input-class="border-transparent font-medium"
                                                        @select="
                                                            selectDestinationWarehouse(
                                                                line,
                                                            )
                                                        "
                                                    />
                                                    <SearchDropdown
                                                        v-if="
                                                            Number(
                                                                line.from_warehouse_id,
                                                            ) ===
                                                            Number(
                                                                line.to_warehouse_id,
                                                            )
                                                        "
                                                        v-model="
                                                            line.to_location_id
                                                        "
                                                        :options="
                                                            destinationLocationOptionsFor(
                                                                line,
                                                            )
                                                        "
                                                        :disabled="
                                                            !line.to_warehouse_id
                                                        "
                                                        placeholder="Location selection"
                                                        search-placeholder="Search location..."
                                                        empty-text="No target location found."
                                                        input-class="border-transparent font-medium"
                                                    />
                                                    <p
                                                        v-else
                                                        class="rounded-md bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700"
                                                    >
                                                        Location will be chosen
                                                        by the destination
                                                        warehouse during
                                                        putaway.
                                                    </p>
                                                </div>
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
                            <div class="border-t border-slate-100 px-4 py-4">
                                <InputError :message="firstLineError" />
                            </div>
                        </div>

                        <AlertError
                            v-if="validationErrors.length"
                            title="Internal transfer cannot be saved."
                            :errors="validationErrors"
                        />
                    </div>

                    <aside class="space-y-5 xl:order-1">
                        <div
                            class="rounded-lg border border-slate-100 bg-white p-5 shadow-sm"
                        >
                            <h2 class="font-bold text-[#2a4858]">
                                Transfer Header
                            </h2>
                            <div class="mt-4 space-y-4">
                                <label class="block">
                                    <span
                                        class="text-xs font-bold text-slate-500 uppercase"
                                    >
                                        Document Number
                                    </span>
                                    <Input
                                        :model-value="nextTransferNo"
                                        readonly
                                        class="mt-1 h-10 rounded-lg border-slate-200 bg-slate-50 font-mono text-slate-500"
                                    />
                                </label>
                                <label class="block">
                                    <span
                                        class="text-xs font-bold text-slate-500 uppercase"
                                    >
                                        Transfer Date
                                    </span>
                                    <Input
                                        v-model="form.transfer_date"
                                        type="date"
                                        class="mt-1 h-10 rounded-lg border-slate-200"
                                    />
                                    <InputError
                                        class="mt-1"
                                        :message="form.errors.transfer_date"
                                    />
                                </label>
                                <label class="block">
                                    <span
                                        class="text-xs font-bold text-slate-500 uppercase"
                                    >
                                        Note
                                    </span>
                                    <textarea
                                        v-model="form.note"
                                        rows="4"
                                        class="mt-1 w-full resize-none rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-[#007882] focus:ring-2 focus:ring-[#007882]/20"
                                        placeholder="Optional transfer note"
                                    ></textarea>
                                    <InputError
                                        class="mt-1"
                                        :message="form.errors.note"
                                    />
                                </label>
                            </div>
                        </div>

                        <div
                            class="rounded-lg bg-[#2a4858] p-6 text-white shadow-lg"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex size-11 items-center justify-center rounded-lg bg-white/10"
                                >
                                    <Boxes class="size-5 text-[#fafa6e]" />
                                </div>
                                <div>
                                    <p class="text-sm text-white/60">
                                        Transfer Value
                                    </p>
                                    <p
                                        class="text-2xl font-bold text-[#fafa6e]"
                                    >
                                        {{ money(draftTotal) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </aside>
                </div>
            </section>
        </main>
    </AppLayout>
</template>
