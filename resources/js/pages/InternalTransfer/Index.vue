<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { ArrowRightLeft, Plus, RotateCcw, Search } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import AlertError from '@/components/AlertError.vue';
import ApprovalActionMenu from '@/components/master-data/ApprovalActionMenu.vue';
import TablePagination from '@/components/TablePagination.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { usePagination } from '@/composables/usePagination';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import ITModel from './ITModel.vue';

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
    approvedAt?: string | null;
    cancelledAt?: string | null;
    fromWarehouse: string;
    toWarehouse: string;
    fromLocation: string;
    toLocation: string;
    itemCount: number;
    totalQuantity: number;
    totalCost: number;
    note?: string | null;
    createdBy: string;
    approvedBy?: string | null;
    cancelledBy?: string | null;
    actionStatus?: string | null;
    status: string;
    lines: TransferLine[];
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
];

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

const pageErrors = computed(() =>
    Object.values((page.props.errors ?? {}) as Record<string, string>).filter(
        Boolean,
    ),
);

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
    router.visit('/stock-movements/internal-transfer/create');
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

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #actions>
            <Button
                type="button"
                class="h-9 rounded-lg bg-[#007882] px-4 text-xs font-bold text-white shadow-md hover:bg-[#006773]"
                @click="showCreate"
            >
                <Plus class="size-4" />
                New Internal Transfer
            </Button>
        </template>

        <main
            class="h-[calc(100dvh-4rem)] w-full [scrollbar-gutter:stable] overflow-y-scroll bg-[#f8fafc] p-4 text-slate-800 md:h-[calc(100dvh-5rem)] md:p-6 xl:p-8 2xl:p-10"
        >
            <section class="w-full">
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

            <ITModel
                v-if="detailTransfer"
                :transfer="detailTransfer"
                @close="closeDetail"
            />
        </main>
    </AppLayout>
</template>
