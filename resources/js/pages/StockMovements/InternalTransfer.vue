<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import {
    ArrowLeft,
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
    quantity: number;
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

const view = ref<'dashboard' | 'new'>('dashboard');
const search = ref(props.filters.search ?? '');
const warehouseId = ref<number | string | ''>(props.filters.warehouse_id ?? '');
const locationId = ref<number | string | ''>(props.filters.location_id ?? '');
const detailTransfer = ref<TransferRecord | null>(null);
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
    from_warehouse_id: props.warehouses[0]?.id ?? '',
    stock_balance_id: '',
    quantity: 1,
    to_warehouse_id: props.warehouses[0]?.id ?? '',
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
                line.to_location_id,
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

function showCreate() {
    form.clearErrors();
    view.value = 'new';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function showDashboard() {
    view.value = 'dashboard';
    window.scrollTo({ top: 0, behavior: 'smooth' });
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
                        !line.to_location_id),
            )
        ) {
            form.setError(
                'lines',
                'Select source warehouse, source location, destination warehouse, and destination location for every item.',
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
    detailTransfer.value = transfer;
}

function closeDetail() {
    detailTransfer.value = null;
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
    <Head title="Internal Transfer" />

    <AppLayout>
        <main
            class="h-[calc(100dvh-4rem)] w-full overflow-y-scroll bg-[#f8fafc] p-4 text-slate-800 [scrollbar-gutter:stable] md:h-[calc(100dvh-5rem)] md:p-6 xl:p-8 2xl:p-10"
        >
            <section v-if="view === 'dashboard'" class="w-full">
                <div
                    v-if="($page.props.flash as any)?.success"
                    class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-[#2a4858]"
                >
                    {{ ($page.props.flash as any).success }}
                </div>
                <AlertError
                    v-if="pageErrors.length"
                    class="mb-4"
                    title="Internal transfer action failed."
                    :errors="pageErrors"
                />

                <div
                    class="mb-8 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4 2xl:gap-6"
                >
                    <div
                        class="rounded-lg border-l-4 border-[#007882] bg-white p-5 shadow-sm"
                    >
                        <p class="text-xs font-bold text-slate-500 uppercase">
                            Total Transfers
                        </p>
                        <h3 class="mt-1 text-2xl font-bold">
                            {{ totalTransfers }}
                        </h3>
                    </div>
                    <div
                        class="rounded-lg border-l-4 border-[#23aa8f] bg-white p-5 shadow-sm"
                    >
                        <p class="text-xs font-bold text-slate-500 uppercase">
                            Units Moved
                        </p>
                        <h3 class="mt-1 text-2xl font-bold text-[#23aa8f]">
                            {{ numberValue(totalQuantity) }}
                        </h3>
                    </div>
                    <div
                        class="rounded-lg border-l-4 border-amber-400 bg-white p-5 shadow-sm"
                    >
                        <p class="text-xs font-bold text-slate-500 uppercase">
                            Cross Warehouse
                        </p>
                        <h3 class="mt-1 text-2xl font-bold text-amber-600">
                            {{ crossWarehouseTransfers }}
                        </h3>
                    </div>
                    <div
                        class="rounded-lg border-l-4 border-[#2a4858] bg-white p-5 shadow-sm"
                    >
                        <p class="text-xs font-bold text-slate-500 uppercase">
                            Transfer Value
                        </p>
                        <h3 class="mt-1 text-2xl font-bold text-[#2a4858]">
                            {{ money(totalCost) }}
                        </h3>
                    </div>
                </div>

                <div
                    class="mb-6 flex flex-col items-stretch justify-between gap-4 xl:flex-row xl:items-center"
                >
                    <div
                        class="grid w-full grid-cols-1 gap-2 md:grid-cols-[minmax(18rem,28rem)_16rem_16rem_2.5rem] xl:w-auto"
                    >
                        <div class="relative w-full">
                            <Search
                                class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"
                            />
                            <Input
                                v-model="search"
                                type="text"
                                placeholder="Search transfer #, item, or note..."
                                class="h-10 rounded-lg border-slate-200 bg-white pl-10 focus-visible:ring-[#007882]/30"
                                @keyup.enter="applyFilters"
                            />
                        </div>
                        <select
                            v-model="warehouseId"
                            class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-600 outline-none focus:border-[#007882] focus:ring-2 focus:ring-[#007882]/20"
                            @change="applyFilters"
                        >
                            <option value="">All Warehouses</option>
                            <option
                                v-for="warehouse in warehouses"
                                :key="warehouse.id"
                                :value="warehouse.id"
                            >
                                {{ warehouse.name }}
                            </option>
                        </select>
                        <select
                            v-model="locationId"
                            class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-600 outline-none focus:border-[#007882] focus:ring-2 focus:ring-[#007882]/20"
                            @change="applyFilters"
                        >
                            <option value="">All Locations</option>
                            <option
                                v-for="location in locations"
                                :key="location.id"
                                :value="location.id"
                            >
                                {{ location.code }} - {{ location.name }}
                            </option>
                        </select>
                        <Button
                            type="button"
                            variant="outline"
                            class="h-10 rounded-lg border-slate-200 bg-white px-3 text-slate-600"
                            title="Reset filters"
                            @click="resetFilters"
                        >
                            <RotateCcw class="size-4" />
                        </Button>
                    </div>

                    <Button
                        type="button"
                        class="h-11 rounded-lg bg-[#007882] px-6 font-bold text-white shadow-md hover:bg-[#006773]"
                        @click="showCreate"
                    >
                        <Plus class="size-4" />
                        New Internal Transfer
                    </Button>
                </div>

                <div
                    class="min-h-[56vh] overflow-hidden rounded-lg border border-slate-100 bg-white shadow-sm"
                >
                    <div v-if="transfers.length > 0" class="overflow-x-auto">
                        <table class="w-full border-collapse text-left">
                            <thead
                                class="bg-slate-50 text-xs font-bold text-slate-600 uppercase"
                            >
                                <tr>
                                    <th class="px-6 py-4">Transfer No</th>
                                    <th class="px-6 py-4">From</th>
                                    <th class="px-6 py-4">To</th>
                                    <th class="px-6 py-4">Date</th>
                                    <th class="px-6 py-4 text-right">Qty</th>
                                    <th class="px-6 py-4">Status</th>
                                    <th class="px-6 py-4 text-center">
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                                <tr
                                    v-for="transfer in paginatedRows"
                                    :key="transfer.id"
                                    class="transition hover:bg-slate-50/50"
                                >
                                    <td class="px-6 py-4">
                                        <p class="font-bold text-[#2a4858]">
                                            {{ transfer.code }}
                                        </p>
                                        <p class="mt-1 text-xs text-slate-400">
                                            {{ transfer.itemCount }} item line{{
                                                transfer.itemCount === 1
                                                    ? ''
                                                    : 's'
                                            }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="font-semibold">
                                            {{ transfer.fromWarehouse }}
                                        </p>
                                        <p class="mt-1 text-xs text-slate-400">
                                            {{ transfer.fromLocation }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="font-semibold">
                                            {{ transfer.toWarehouse }}
                                        </p>
                                        <p class="mt-1 text-xs text-slate-400">
                                            {{ transfer.toLocation }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-4 text-slate-500">
                                        {{ transfer.displayDate ?? '-' }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-right font-bold text-[#007882]"
                                    >
                                        {{
                                            numberValue(transfer.totalQuantity)
                                        }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold uppercase"
                                            :class="
                                                statusClass(transfer.status)
                                            "
                                        >
                                            {{ statusLabel(transfer.status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <ApprovalActionMenu
                                            :status="transfer.status"
                                            view-label="View"
                                            :actionable-statuses="['draft']"
                                            @view="openDetail(transfer)"
                                            @approve="
                                                updateTransferStatus(
                                                    transfer,
                                                    'approve',
                                                )
                                            "
                                            @reject="
                                                updateTransferStatus(
                                                    transfer,
                                                    'reject',
                                                )
                                            "
                                            @cancel="
                                                updateTransferStatus(
                                                    transfer,
                                                    'cancel',
                                                )
                                            "
                                        />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div
                        v-else
                        class="flex min-h-[56vh] flex-col items-center justify-center px-6 py-16 text-center"
                    >
                        <div
                            class="flex size-14 items-center justify-center rounded-lg bg-[#23aa8f]/10 text-[#007882]"
                        >
                            <ArrowRightLeft class="size-7" />
                        </div>
                        <h3 class="mt-4 text-lg font-bold text-[#2a4858]">
                            No internal transfers yet
                        </h3>
                        <p class="mt-2 max-w-md text-sm text-slate-500">
                            Move stock between putaway, damage, obsolete, and
                            scrap locations when items change storage status.
                        </p>
                    </div>
                </div>

                <TablePagination
                    v-if="transfers.length > 0"
                    :current-page="currentPage"
                    :total-pages="totalPages"
                    :page-start="pageStart"
                    :page-end="pageEnd"
                    :total-rows="totalRows"
                    :rows-per-page="pageSize"
                    @go-to-page="goToPage"
                    @update-rows-per-page="setRowsPerPage"
                />
            </section>

            <section v-else class="w-full">
                <div class="mb-6 flex items-center justify-between gap-4">
                    <div>
                        <button
                            type="button"
                            class="mb-3 inline-flex items-center gap-2 text-sm font-bold text-[#007882]"
                            @click="showDashboard"
                        >
                            <ArrowLeft class="size-4" />
                            Back to transfers
                        </button>
                        <p
                            class="text-xs font-bold tracking-widest text-slate-400 uppercase"
                        >
                            {{ nextTransferNo }}
                        </p>
                        <h1 class="mt-1 text-2xl font-bold text-[#2a4858]">
                            New Internal Transfer
                        </h1>
                    </div>
                    <div
                        class="hidden items-center gap-3 rounded-lg bg-white px-4 py-3 text-sm font-bold text-[#2a4858] shadow-sm md:flex"
                    >
                        <CalendarDays class="size-4 text-[#007882]" />
                        {{ form.transfer_date }}
                    </div>
                </div>

                <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_22rem]">
                    <div class="space-y-5">
                        <div
                            class="rounded-lg border border-slate-100 bg-white shadow-sm"
                        >
                            <div class="border-b border-slate-100 px-5 py-4">
                                <h2 class="font-bold text-[#2a4858]">
                                    Transfer Lines
                                </h2>
                                <p class="mt-1 text-sm text-slate-500">
                                    Choose the source item, warehouse, and
                                    location on each row, then choose where it
                                    should move.
                                </p>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full min-w-[1180px] text-left">
                                    <thead
                                        class="bg-slate-50 text-xs font-bold text-slate-500 uppercase"
                                    >
                                        <tr>
                                            <th class="px-4 py-3">Item</th>
                                            <th class="px-4 py-3">
                                                From Warehouse
                                            </th>
                                            <th class="px-4 py-3">
                                                From Location
                                            </th>
                                            <th class="px-4 py-3 text-right">
                                                Available
                                            </th>
                                            <th class="px-4 py-3 text-center">
                                                Qty
                                            </th>
                                            <th class="px-4 py-3">
                                                To Warehouse
                                            </th>
                                            <th class="px-4 py-3">
                                                To Location
                                            </th>
                                            <th class="px-4 py-3 text-right">
                                                Value
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
                                                    empty-text="No stocked item found."
                                                    input-class="border-transparent font-medium"
                                                    @select="selectItem(line)"
                                                />
                                            </td>
                                            <td class="px-4 py-4">
                                                <SearchDropdown
                                                    v-model="
                                                        line.from_warehouse_id
                                                    "
                                                    :options="warehouseOptions"
                                                    placeholder="Source warehouse"
                                                    search-placeholder="Search warehouse..."
                                                    empty-text="No warehouse found."
                                                    input-class="border-transparent font-medium"
                                                    @select="
                                                        selectSourceWarehouse(
                                                            line,
                                                        )
                                                    "
                                                />
                                            </td>
                                            <td class="px-4 py-4">
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
                                                        !itemHasStock(line)
                                                    "
                                                    placeholder="Source location"
                                                    search-placeholder="Search location..."
                                                    empty-text="No source stock found."
                                                    input-class="border-transparent font-medium"
                                                    @select="
                                                        selectSourceLocation(
                                                            line,
                                                        )
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
                                            <td class="px-4 py-4">
                                                <SearchDropdown
                                                    v-model="
                                                        line.to_warehouse_id
                                                    "
                                                    :options="warehouseOptions"
                                                    placeholder="Target warehouse"
                                                    search-placeholder="Search warehouse..."
                                                    empty-text="No warehouse found."
                                                    input-class="border-transparent font-medium"
                                                    @select="
                                                        selectDestinationWarehouse(
                                                            line,
                                                        )
                                                    "
                                                />
                                            </td>
                                            <td class="px-4 py-4">
                                                <SearchDropdown
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
                                                    placeholder="Target location"
                                                    search-placeholder="Search location..."
                                                    empty-text="No target location found."
                                                    input-class="border-transparent font-medium"
                                                />
                                            </td>
                                            <td
                                                class="px-4 py-4 text-right font-mono font-bold text-[#007882]"
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
                            title="Internal transfer cannot be saved."
                            :errors="validationErrors"
                        />
                    </div>

                    <aside class="space-y-5">
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
                            <p class="mt-4 text-sm text-white/60">
                                Stock will be moved immediately after saving.
                            </p>
                            <Button
                                type="button"
                                class="mt-5 h-11 w-full rounded-lg bg-[#23aa8f] font-bold text-white hover:bg-[#1e917a]"
                                :disabled="form.processing"
                                @click="submitTransfer"
                            >
                                <ArrowRightLeft class="size-4" />
                                Save Transfer
                            </Button>
                        </div>
                    </aside>
                </div>
            </section>

            <div
                v-if="detailTransfer"
                class="fixed inset-0 z-[75] flex items-center justify-center bg-[#2a4858]/20 p-4 backdrop-blur-sm"
                @click.self="closeDetail"
            >
                <section
                    class="flex max-h-[90vh] w-full max-w-6xl flex-col overflow-hidden rounded-lg bg-white shadow-2xl"
                >
                    <header
                        class="flex items-start justify-between gap-4 border-b border-slate-100 p-5"
                    >
                        <div>
                            <p
                                class="text-xs font-bold tracking-widest text-slate-400 uppercase"
                            >
                                Internal Transfer Detail
                            </p>
                            <h2 class="mt-1 text-xl font-bold text-[#2a4858]">
                                {{ detailTransfer.code }}
                            </h2>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ detailTransfer.fromWarehouse }} to
                                {{ detailTransfer.toWarehouse }}
                            </p>
                        </div>
                        <Button
                            type="button"
                            variant="outline"
                            class="h-9 w-9 rounded-lg border-slate-100 p-0 text-slate-400"
                            title="Close"
                            @click="closeDetail"
                        >
                            <X class="size-4" />
                        </Button>
                    </header>

                    <div class="min-h-0 flex-1 overflow-y-auto p-5">
                        <div class="mb-4 grid gap-3 text-sm sm:grid-cols-3">
                            <div class="rounded-lg bg-slate-50 p-3">
                                <p
                                    class="text-xs font-bold text-slate-400 uppercase"
                                >
                                    Transfer Date
                                </p>
                                <p class="font-bold text-[#2a4858]">
                                    {{ detailTransfer.displayDate ?? '-' }}
                                </p>
                            </div>
                            <div class="rounded-lg bg-[#23aa8f]/10 p-3">
                                <p
                                    class="text-xs font-bold text-[#007882] uppercase"
                                >
                                    Created By
                                </p>
                                <p class="font-bold text-[#2a4858]">
                                    {{ detailTransfer.createdBy }}
                                </p>
                            </div>
                            <div class="rounded-lg bg-slate-50 p-3">
                                <p
                                    class="text-xs font-bold text-slate-400 uppercase"
                                >
                                    Status
                                </p>
                                <span
                                    class="mt-1 inline-flex rounded-full px-2.5 py-1 text-xs font-bold uppercase"
                                    :class="statusClass(detailTransfer.status)"
                                >
                                    {{ statusLabel(detailTransfer.status) }}
                                </span>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table
                                class="w-full min-w-[920px] text-left text-sm"
                            >
                                <thead>
                                    <tr class="border-b border-slate-100">
                                        <th
                                            class="py-3 pr-4 text-xs font-bold text-slate-400 uppercase"
                                        >
                                            Item
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-bold text-slate-400 uppercase"
                                        >
                                            From
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-bold text-slate-400 uppercase"
                                        >
                                            To
                                        </th>
                                        <th
                                            class="px-4 py-3 text-right text-xs font-bold text-slate-400 uppercase"
                                        >
                                            Qty
                                        </th>
                                        <th
                                            class="px-4 py-3 text-right text-xs font-bold text-slate-400 uppercase"
                                        >
                                            Cost
                                        </th>
                                        <th
                                            class="py-3 pl-4 text-right text-xs font-bold text-slate-400 uppercase"
                                        >
                                            Total
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="line in detailTransfer.lines"
                                        :key="line.id"
                                        class="border-b border-slate-50"
                                    >
                                        <td class="py-4 pr-4">
                                            <div
                                                class="font-bold text-[#2a4858]"
                                            >
                                                {{ line.itemName }}
                                            </div>
                                            <p
                                                class="mt-1 text-xs text-slate-400"
                                            >
                                                {{ line.itemCode }} /
                                                {{ line.unit }}
                                            </p>
                                        </td>
                                        <td class="px-4 py-4">
                                            <p class="font-bold text-[#2a4858]">
                                                {{ line.fromWarehouse ?? '-' }}
                                            </p>
                                            <p
                                                class="mt-1 text-xs text-slate-400"
                                            >
                                                {{ line.fromLocationCode }} -
                                                {{ line.fromLocation }}
                                            </p>
                                            <span
                                                v-if="line.fromLocationType"
                                                class="mt-2 inline-flex rounded-full px-2 py-0.5 text-[11px] font-bold uppercase"
                                                :class="
                                                    locationTypeClass(
                                                        line.fromLocationType,
                                                    )
                                                "
                                            >
                                                {{
                                                    locationTypeLabel(
                                                        line.fromLocationType,
                                                    )
                                                }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4">
                                            <p class="font-bold text-[#2a4858]">
                                                {{ line.toWarehouse ?? '-' }}
                                            </p>
                                            <p
                                                class="mt-1 text-xs text-slate-400"
                                            >
                                                {{ line.toLocationCode }} -
                                                {{ line.toLocation }}
                                            </p>
                                            <span
                                                v-if="line.toLocationType"
                                                class="mt-2 inline-flex rounded-full px-2 py-0.5 text-[11px] font-bold uppercase"
                                                :class="
                                                    locationTypeClass(
                                                        line.toLocationType,
                                                    )
                                                "
                                            >
                                                {{
                                                    locationTypeLabel(
                                                        line.toLocationType,
                                                    )
                                                }}
                                            </span>
                                        </td>
                                        <td
                                            class="px-4 py-4 text-right font-bold text-[#007882]"
                                        >
                                            {{ numberValue(line.quantity) }}
                                        </td>
                                        <td
                                            class="px-4 py-4 text-right font-mono font-bold text-[#2a4858]"
                                        >
                                            {{ money(line.unitCost) }}
                                        </td>
                                        <td
                                            class="py-4 pl-4 text-right font-mono font-bold text-[#007882]"
                                        >
                                            {{ money(line.totalCost) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <footer
                        class="flex flex-col justify-between gap-3 border-t border-slate-100 bg-slate-50 p-5 sm:flex-row sm:items-center"
                    >
                        <div
                            class="flex items-center gap-2 text-sm text-slate-500"
                        >
                            <CalendarDays class="size-4 text-[#007882]" />
                            {{ detailTransfer.lines.length }} item line{{
                                detailTransfer.lines.length === 1 ? '' : 's'
                            }}
                        </div>
                        <div class="text-right">
                            <p
                                class="text-xs font-bold tracking-widest text-slate-400 uppercase"
                            >
                                Transfer Value
                            </p>
                            <p class="text-xl font-bold text-[#007882]">
                                {{ money(detailTransfer.totalCost) }}
                            </p>
                        </div>
                    </footer>
                </section>
            </div>
        </main>
    </AppLayout>
</template>
