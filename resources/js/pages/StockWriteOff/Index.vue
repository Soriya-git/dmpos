<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { Filter, Plus, RotateCcw, Search } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import AlertError from '@/components/AlertError.vue';
import ApprovalActionMenu from '@/components/master-data/ApprovalActionMenu.vue';
import TablePagination from '@/components/TablePagination.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { usePagination } from '@/composables/usePagination';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import SWModal from './SWModal.vue';

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
    confirmedBy?: string | null;
    cancelledBy?: string | null;
    confirmedAt?: string | null;
    cancelledAt?: string | null;
    actionStatus?: string | null;
    status: WriteOffStatus;
    lines: WriteOffLine[];
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

const pageErrors = computed(() =>
    Object.values((page.props.errors ?? {}) as Record<string, string>).filter(
        Boolean,
    ),
);

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

function showCreate() {
    router.visit('/stock-movements/write-off/create');
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
    <Head title="Stock Write-off" />

    <AppLayout :breadcrumbs="breadcrumbs">
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
                    title="Stock write-off action failed."
                    :errors="pageErrors"
                />

                <div
                    class="mb-8 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4 2xl:gap-6"
                >
                    <div
                        class="rounded-lg border-l-4 border-[#007882] bg-white p-5 shadow-sm"
                    >
                        <p class="text-xs font-bold text-slate-500 uppercase">
                            Total Write-offs
                        </p>
                        <h3 class="mt-1 text-2xl font-bold">
                            {{ totalWriteOffs }}
                        </h3>
                    </div>
                    <div
                        class="rounded-lg border-l-4 border-[#23aa8f] bg-white p-5 shadow-sm"
                    >
                        <p class="text-xs font-bold text-slate-500 uppercase">
                            Draft Approval
                        </p>
                        <h3 class="mt-1 text-2xl font-bold text-[#23aa8f]">
                            {{ draftWriteOffs }}
                        </h3>
                    </div>
                    <div
                        class="rounded-lg border-l-4 border-amber-400 bg-white p-5 shadow-sm"
                    >
                        <p class="text-xs font-bold text-slate-500 uppercase">
                            Approved Docs
                        </p>
                        <h3 class="mt-1 text-2xl font-bold text-amber-600">
                            {{ approvedWriteOffs }}
                        </h3>
                    </div>
                    <div
                        class="rounded-lg border-l-4 border-red-400 bg-white p-5 shadow-sm"
                    >
                        <p class="text-xs font-bold text-slate-500 uppercase">
                            Approved Cost
                        </p>
                        <h3 class="mt-1 text-2xl font-bold text-red-500">
                            {{ money(approvedCost) }}
                        </h3>
                    </div>
                </div>

                <div
                    class="mb-6 flex flex-col items-stretch justify-between gap-4 xl:flex-row xl:items-center"
                >
                    <div
                        class="grid w-full grid-cols-1 gap-2 md:grid-cols-[minmax(18rem,28rem)_12rem_16rem_2.5rem] xl:w-auto"
                    >
                        <div class="relative w-full">
                            <Search
                                class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"
                            />
                            <Input
                                v-model="search"
                                type="text"
                                placeholder="Search write-off #, item, or note..."
                                class="h-10 rounded-lg border-slate-200 bg-white pl-10 focus-visible:ring-[#007882]/30"
                                @keyup.enter="applyFilters"
                            />
                        </div>
                        <select
                            v-model="status"
                            class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-600 outline-none focus:border-[#007882] focus:ring-2 focus:ring-[#007882]/20"
                            @change="applyFilters"
                        >
                            <option value="">All Status</option>
                            <option value="draft">Draft</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                            <option value="cancelled">Cancelled</option>
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
                        New Stock Write-off
                    </Button>
                </div>

                <div
                    class="min-h-[56vh] overflow-hidden rounded-lg border border-slate-100 bg-white shadow-sm"
                >
                    <div v-if="writeOffs.length > 0" class="overflow-x-auto">
                        <table class="w-full border-collapse text-left">
                            <thead
                                class="bg-slate-50 text-xs font-bold text-slate-600 uppercase"
                            >
                                <tr>
                                    <th class="px-6 py-4">Write-off No</th>
                                    <th class="px-6 py-4">Warehouse</th>
                                    <th class="px-6 py-4">Date</th>
                                    <th class="px-6 py-4 text-right">Qty</th>
                                    <th class="px-6 py-4 text-right">Cost</th>
                                    <th class="px-6 py-4">Status</th>
                                    <th class="px-6 py-4 text-center">
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                                <tr
                                    v-for="writeOff in paginatedRows"
                                    :key="writeOff.id"
                                    class="transition hover:bg-slate-50/50"
                                >
                                    <td
                                        class="px-6 py-4 font-mono font-bold text-[#007882]"
                                    >
                                        {{ writeOff.code }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="font-semibold text-slate-800">
                                            {{ writeOff.warehouse }}
                                        </p>
                                        <p
                                            class="mt-1 text-xs font-medium text-slate-400"
                                        >
                                            {{ writeOff.branch }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600">
                                        {{ writeOff.displayDate ?? '-' }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-right font-bold text-red-600"
                                    >
                                        {{
                                            numberValue(writeOff.totalQuantity)
                                        }}
                                    </td>
                                    <td class="px-6 py-4 text-right font-bold">
                                        {{ money(writeOff.totalCost) }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="rounded-full px-2.5 py-1 text-xs font-bold uppercase"
                                            :class="
                                                statusClass(writeOff.status)
                                            "
                                        >
                                            {{ statusLabel(writeOff.status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <ApprovalActionMenu
                                            :status="writeOff.status"
                                            view-label="View"
                                            :actionable-statuses="['draft']"
                                            @view="openDetail(writeOff)"
                                            @approve="
                                                updateWriteOffStatus(
                                                    writeOff,
                                                    'approve',
                                                )
                                            "
                                            @reject="
                                                updateWriteOffStatus(
                                                    writeOff,
                                                    'reject',
                                                )
                                            "
                                            @cancel="
                                                updateWriteOffStatus(
                                                    writeOff,
                                                    'cancel',
                                                )
                                            "
                                        />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <TablePagination
                        v-if="writeOffs.length > 0"
                        :current-page="currentPage"
                        :total-pages="totalPages"
                        :total-rows="totalRows"
                        :page-start="pageStart"
                        :page-end="pageEnd"
                        :rows-per-page="pageSize"
                        @go-to-page="goToPage"
                        @update-rows-per-page="setRowsPerPage"
                    />

                    <div v-else class="p-16 text-center">
                        <div
                            class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-slate-50"
                        >
                            <Filter class="size-6 text-slate-300" />
                        </div>
                        <h3 class="text-lg font-bold text-slate-700">
                            No stock write-offs found
                        </h3>
                        <p class="mt-1 text-sm text-slate-400">
                            Create a draft write-off when warehouse stock has a
                            discrepancy.
                        </p>
                    </div>
                </div>
            </section>

            <SWModal
                v-if="detailWriteOff"
                :write-off="detailWriteOff"
                @close="closeDetail"
            />
        </main>
    </AppLayout>
</template>
