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
import SAModal from './SAModal.vue';

type AdjustmentStatus = 'draft' | 'approved' | 'cancelled';
type AdjustmentType = 'adjustment_in' | 'adjustment_out';

type StockedLocation = {
    id: number;
    warehouseId: number;
    code: string;
    name: string;
    warehouse: string;
    branch: string;
};

type AdjustmentLine = {
    id: number;
    itemCode: string;
    itemName: string;
    unit: string;
    location?: string | null;
    locationCode?: string | null;
    systemQuantity: number;
    adjustedQuantity: number;
    differenceQuantity: number;
    unitCost: number;
    totalCost: number;
    reason?: string | null;
    note?: string | null;
};

type AdjustmentRecord = {
    id: number;
    code: string;
    type: AdjustmentType;
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
    status: AdjustmentStatus;
    lines: AdjustmentLine[];
};

const props = defineProps<{
    adjustments: AdjustmentRecord[];
    locations: StockedLocation[];
    filters: {
        search?: string | null;
        status?: string | null;
        type?: string | null;
        location_id?: number | string | null;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Stock Operations' },
    { title: 'Stock Movements' },
    {
        title: 'Stock Adjustments',
        href: '/stock-movements/stock-adjustments',
    },
];

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const adjustmentType = ref(props.filters.type ?? '');
const locationId = ref<number | string | ''>(props.filters.location_id ?? '');
const detailAdjustment = ref<AdjustmentRecord | null>(null);
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
} = usePagination(() => props.adjustments, 10);

const totalAdjustments = computed(() => props.adjustments.length);
const draftAdjustments = computed(
    () =>
        props.adjustments.filter((adjustment) => adjustment.status === 'draft')
            .length,
);
const adjustInCount = computed(
    () =>
        props.adjustments.filter(
            (adjustment) => adjustment.type === 'adjustment_in',
        ).length,
);
const adjustOutCount = computed(
    () =>
        props.adjustments.filter(
            (adjustment) => adjustment.type === 'adjustment_out',
        ).length,
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

function typeLabel(value: string) {
    const labels: Record<string, string> = {
        adjustment_in: 'Adjust In',
        adjustment_out: 'Adjust Out',
    };

    return labels[value] ?? value.replaceAll('_', ' ');
}

function typeClass(value: string) {
    const classes: Record<string, string> = {
        adjustment_in: 'bg-green-100 text-green-700',
        adjustment_out: 'bg-red-100 text-red-700',
    };

    return classes[value] ?? 'bg-slate-100 text-slate-600';
}

function statusLabel(value: string) {
    const labels: Record<string, string> = {
        draft: 'Draft',
        approved: 'Approved',
        cancelled: 'Cancelled',
    };

    return labels[value] ?? value.replaceAll('_', ' ');
}

function statusClass(value: string) {
    const classes: Record<string, string> = {
        draft: 'bg-amber-100 text-amber-700',
        approved: 'bg-green-100 text-green-700',
        cancelled: 'bg-slate-100 text-slate-600',
    };

    return classes[value] ?? 'bg-slate-100 text-slate-600';
}

function applyFilters() {
    router.get(
        '/stock-movements/stock-adjustments',
        {
            search: search.value || undefined,
            status: status.value || undefined,
            type: adjustmentType.value || undefined,
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
    adjustmentType.value = '';
    locationId.value = '';

    router.get(
        '/stock-movements/stock-adjustments',
        {},
        { preserveScroll: true, replace: true },
    );
}

function showCreate() {
    router.visit('/stock-movements/stock-adjustments/create');
}

function openDetail(adjustment: AdjustmentRecord) {
    detailAdjustment.value = adjustment;
}

function closeDetail() {
    detailAdjustment.value = null;
}

function updateAdjustmentStatus(
    adjustment: AdjustmentRecord,
    action: 'approve' | 'reject' | 'cancel',
) {
    router.patch(
        `/stock-movements/stock-adjustments/${adjustment.id}/${action}`,
        {},
        {
            preserveScroll: true,
            preserveState: false,
        },
    );
}
</script>

<template>
    <Head title="Stock Adjustments" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #actions>
            <Button
                type="button"
                class="h-9 rounded-lg bg-[#007882] px-4 text-xs font-bold text-white shadow-md hover:bg-[#006773]"
                @click="showCreate"
            >
                <Plus class="size-4" />
                New Stock Adjustment
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
                    title="Stock adjustment action failed."
                    :errors="pageErrors"
                />

                <div
                    class="mb-8 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4 2xl:gap-6"
                >
                    <div
                        class="rounded-lg border-l-4 border-[#007882] bg-white p-5 shadow-sm"
                    >
                        <p class="text-xs font-bold text-slate-500 uppercase">
                            Total Adjustments
                        </p>
                        <h3 class="mt-1 text-2xl font-bold">
                            {{ totalAdjustments }}
                        </h3>
                    </div>
                    <div
                        class="rounded-lg border-l-4 border-[#23aa8f] bg-white p-5 shadow-sm"
                    >
                        <p class="text-xs font-bold text-slate-500 uppercase">
                            Draft Approval
                        </p>
                        <h3 class="mt-1 text-2xl font-bold text-[#23aa8f]">
                            {{ draftAdjustments }}
                        </h3>
                    </div>
                    <div
                        class="rounded-lg border-l-4 border-green-400 bg-white p-5 shadow-sm"
                    >
                        <p class="text-xs font-bold text-slate-500 uppercase">
                            Adjust In
                        </p>
                        <h3 class="mt-1 text-2xl font-bold text-green-600">
                            {{ adjustInCount }}
                        </h3>
                    </div>
                    <div
                        class="rounded-lg border-l-4 border-red-400 bg-white p-5 shadow-sm"
                    >
                        <p class="text-xs font-bold text-slate-500 uppercase">
                            Adjust Out
                        </p>
                        <h3 class="mt-1 text-2xl font-bold text-red-500">
                            {{ adjustOutCount }}
                        </h3>
                    </div>
                </div>

                <div
                    class="mb-6 flex flex-col items-stretch justify-between gap-4 xl:flex-row xl:items-center"
                >
                    <div
                        class="grid w-full grid-cols-1 gap-2 md:grid-cols-[minmax(18rem,28rem)_12rem_12rem_16rem_2.5rem] xl:w-auto"
                    >
                        <div class="relative w-full">
                            <Search
                                class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"
                            />
                            <Input
                                v-model="search"
                                type="text"
                                placeholder="Search adjustment #, item, or note..."
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
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <select
                            v-model="adjustmentType"
                            class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-600 outline-none focus:border-[#007882] focus:ring-2 focus:ring-[#007882]/20"
                            @change="applyFilters"
                        >
                            <option value="">All Types</option>
                            <option value="adjustment_in">Adjust In</option>
                            <option value="adjustment_out">Adjust Out</option>
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
                    <div v-if="adjustments.length > 0" class="overflow-x-auto">
                        <table class="w-full border-collapse text-left">
                            <thead
                                class="bg-slate-50 text-xs font-bold text-slate-600 uppercase"
                            >
                                <tr>
                                    <th class="px-6 py-4">Adjustment No</th>
                                    <th class="px-6 py-4">Type</th>
                                    <th class="px-6 py-4">Warehouse</th>
                                    <th class="px-6 py-4">Date</th>
                                    <th class="px-6 py-4 text-right">Qty</th>
                                    <th class="px-6 py-4 text-right">Value</th>
                                    <th class="px-6 py-4">Status</th>
                                    <th class="px-6 py-4 text-center">
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                                <tr
                                    v-for="adjustment in paginatedRows"
                                    :key="adjustment.id"
                                    class="transition hover:bg-slate-50/50"
                                >
                                    <td
                                        class="px-6 py-4 font-mono font-bold text-[#007882]"
                                    >
                                        {{ adjustment.code }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="rounded-full px-2.5 py-1 text-xs font-bold uppercase"
                                            :class="typeClass(adjustment.type)"
                                        >
                                            {{ typeLabel(adjustment.type) }}
                                        </span>
                                    </td>
                                    <td
                                        class="px-6 py-4 font-semibold text-slate-800"
                                    >
                                        {{ adjustment.warehouse }}
                                        <p
                                            class="mt-1 text-xs font-medium text-slate-400"
                                        >
                                            {{ adjustment.locationCode }} -
                                            {{ adjustment.location }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600">
                                        {{ adjustment.displayDate ?? '-' }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-right font-bold text-[#2a4858]"
                                    >
                                        {{
                                            numberValue(
                                                adjustment.totalQuantity,
                                            )
                                        }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-right font-mono font-bold"
                                    >
                                        {{ money(adjustment.totalCost) }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="rounded-full px-2.5 py-1 text-xs font-bold uppercase"
                                            :class="
                                                statusClass(adjustment.status)
                                            "
                                        >
                                            {{ statusLabel(adjustment.status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <ApprovalActionMenu
                                            :status="adjustment.status"
                                            view-label="View"
                                            :actionable-statuses="['draft']"
                                            @view="openDetail(adjustment)"
                                            @approve="
                                                updateAdjustmentStatus(
                                                    adjustment,
                                                    'approve',
                                                )
                                            "
                                            @reject="
                                                updateAdjustmentStatus(
                                                    adjustment,
                                                    'reject',
                                                )
                                            "
                                            @cancel="
                                                updateAdjustmentStatus(
                                                    adjustment,
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
                        class="flex min-h-[56vh] flex-col items-center justify-center px-4 text-center"
                    >
                        <div
                            class="mb-4 flex size-14 items-center justify-center rounded-full bg-slate-50"
                        >
                            <Filter class="size-6 text-slate-300" />
                        </div>
                        <h3 class="text-lg font-bold text-[#2a4858]">
                            No stock adjustments found
                        </h3>
                        <p class="mt-2 max-w-md text-sm text-slate-500">
                            Create a draft adjustment when counted inventory is
                            higher or lower than system stock.
                        </p>
                    </div>
                </div>

                <TablePagination
                    v-if="adjustments.length > 0"
                    class="mt-4"
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

            <SAModal
                v-if="detailAdjustment"
                :adjustment="detailAdjustment"
                @close="closeDetail"
            />
        </main>
    </AppLayout>
</template>
