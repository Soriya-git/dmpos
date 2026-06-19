<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Filter, Plus, RotateCcw, Search } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import ApprovalActionMenu from '@/components/master-data/ApprovalActionMenu.vue';
import TablePagination from '@/components/TablePagination.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { usePagination } from '@/composables/usePagination';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import GRDetail from './GRDetail.vue';
import GREdit from './GREdit.vue';

type PoLine = {
    id: number;
    item_name?: string | null;
    item_code?: string | null;
    unit_code?: string | null;
    quantity_ordered: number;
    quantity_available: number;
};

type GoodsReceiptLine = {
    id: number;
    purchase_order_line_id: number;
    stock_location_id?: number | null;
    item_name?: string | null;
    item_code?: string | null;
    unit_code?: string | null;
    staging_area?: string | null;
    quantity_received: number;
};

type GoodsReceipt = {
    id: number;
    receipt_no: string;
    purchase_order_id?: number | null;
    purchase_order_no?: string | null;
    note?: string | null;
    status: string;
    created_at?: string | null;
    updated_at?: string | null;
    received_at?: string | null;
    staging_area?: string | null;
    operator?: string | null;
    cancelled_by?: string | null;
    line_count: number;
    po_lines?: PoLine[] | null;
    lines: GoodsReceiptLine[];
};

type StagingLocation = {
    id: number;
    name: string;
    code?: string | null;
    warehouse_name?: string | null;
};

const props = defineProps<{
    receipts: GoodsReceipt[];
    stagingLocations: StagingLocation[];
    stats: {
        waitingPurchaseOrders: number;
        awaitingStaging: number;
        readyForPutaway: number;
    };
}>();

const view = ref<'list' | 'detail' | 'edit'>('list');
const detailReceipt = ref<GoodsReceipt | null>(null);
const editGRRef = ref<InstanceType<typeof GREdit> | null>(null);
const search = ref('');
const statusFilter = ref('');

const breadcrumbs = computed<BreadcrumbItem[]>(() => {
    if (
        (view.value === 'detail' || view.value === 'edit') &&
        detailReceipt.value
    ) {
        const crumbs: BreadcrumbItem[] = [
            { title: 'Stock Operations' },
            { title: 'Goods Receipt', href: '/goods-receipts' },
            { title: detailReceipt.value.receipt_no },
        ];
        if (view.value === 'edit') crumbs.push({ title: 'Edit' });
        return crumbs;
    }
    return [
        { title: 'Stock Operations' },
        { title: 'Goods Receipt', href: '/goods-receipts' },
    ];
});

const totalReceipts = computed(() => props.receipts.length);

const awaitingPercent = computed(() =>
    totalReceipts.value
        ? Math.round((props.stats.awaitingStaging / totalReceipts.value) * 100)
        : 0,
);

const readyPercent = computed(() =>
    totalReceipts.value
        ? Math.round((props.stats.readyForPutaway / totalReceipts.value) * 100)
        : 0,
);

const filteredReceipts = computed(() => {
    const term = search.value.trim().toLowerCase();

    return props.receipts.filter((r) => {
        const matchesSearch =
            !term ||
            [r.receipt_no, r.purchase_order_no, r.operator, r.staging_area]
                .filter(Boolean)
                .some((v) => String(v).toLowerCase().includes(term));

        const matchesStatus =
            !statusFilter.value || r.status === statusFilter.value;

        return matchesSearch && matchesStatus;
    });
});

const {
    currentPage,
    totalRows,
    totalPages,
    pageStart,
    pageEnd,
    paginatedRows: paginatedReceipts,
    goToPage,
    pageSize,
    setRowsPerPage,
} = usePagination(() => filteredReceipts.value, 10);

function statusLabel(status: string) {
    const labels: Record<string, string> = {
        draft: 'Draft',
        in_progress: 'In Progress',
        approved: 'Approved',
        partially_received: 'Partially Received',
        received: 'Received',
        cancelled: 'Cancelled',
        rejected: 'Rejected',
    };
    return (
        labels[status] ??
        status
            .split('_')
            .map((w) => w.charAt(0).toUpperCase() + w.slice(1))
            .join(' ')
    );
}

function statusClass(status: string) {
    const classes: Record<string, string> = {
        draft: 'bg-slate-100 text-slate-700',
        in_progress: 'bg-amber-100 text-amber-700',
        approved: 'bg-green-100 text-green-700',
        partially_received: 'bg-blue-100 text-blue-700',
        received: 'bg-emerald-100 text-emerald-700',
        cancelled: 'bg-rose-100 text-rose-700',
        rejected: 'bg-red-100 text-red-700',
    };
    return classes[status] ?? 'bg-slate-100 text-slate-700';
}

function openDetail(receipt: GoodsReceipt) {
    detailReceipt.value = receipt;
    view.value = 'detail';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function showList() {
    view.value = 'list';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function showEdit() {
    view.value = 'edit';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function resetFilters() {
    search.value = '';
    statusFilter.value = '';
}

function updateReceiptStatus(
    receipt: GoodsReceipt,
    action: 'approve' | 'reject' | 'cancel',
) {
    router.patch(
        `/goods-receipts/${receipt.id}/${action}`,
        {},
        { preserveScroll: true, preserveState: false },
    );
}
</script>

<template>
    <Head title="Goods Receipt" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #actions>
            <!-- List view -->
            <template v-if="view === 'list'">
                <Button
                    type="button"
                    class="h-9 rounded-lg bg-[#007882] px-4 text-xs font-bold text-white shadow-md hover:bg-[#006773]"
                    @click="router.visit('/goods-receipts/create')"
                >
                    <Plus class="size-4" />
                    New Goods Receipt
                </Button>
            </template>

            <!-- Detail view -->
            <template v-else-if="view === 'detail'">
                <div class="flex gap-2">
                    <Button
                        v-if="detailReceipt?.status === 'draft'"
                        type="button"
                        class="h-9 rounded-lg bg-[#23aa8f] px-4 text-xs font-bold text-white shadow-md hover:bg-[#1e917a]"
                        @click="showEdit"
                    >
                        Edit
                    </Button>
                    <Button
                        type="button"
                        variant="ghost"
                        class="h-9 font-semibold text-slate-600"
                        @click="showList"
                    >
                        ← Back
                    </Button>
                </div>
            </template>

            <!-- Edit view -->
            <template v-else-if="view === 'edit'">
                <div class="flex gap-2">
                    <Button
                        type="button"
                        variant="ghost"
                        class="h-9 font-semibold text-slate-600 hover:text-red-500"
                        @click="
                            view = 'detail';
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                        "
                    >
                        Cancel
                    </Button>
                    <Button
                        type="button"
                        class="h-9 rounded-lg bg-[#23aa8f] px-4 text-xs font-bold text-white shadow-md hover:bg-[#1e917a]"
                        :disabled="!editGRRef?.canSubmit"
                        @click="editGRRef?.submit()"
                    >
                        {{
                            editGRRef?.isProcessing
                                ? 'Saving...'
                                : 'Save Changes'
                        }}
                    </Button>
                </div>
            </template>
        </template>

        <main
            class="h-[calc(100dvh-4rem)] w-full scrollbar-gutter-stable overflow-y-scroll bg-[#f8fafc] p-4 text-slate-800 md:h-[calc(100dvh-5rem)] md:p-6 xl:p-8 2xl:p-10"
        >
            <section v-if="view === 'list'" class="w-full">
                <div
                    v-if="($page.props.flash as any)?.success"
                    class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-[#2a4858]"
                >
                    {{ ($page.props.flash as any).success }}
                </div>

                <!-- Stat cards -->
                <div
                    class="mb-8 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4 2xl:gap-6"
                >
                    <div
                        class="rounded-lg border-l-4 border-[#007882] bg-white p-5 shadow-sm"
                    >
                        <p class="text-xs font-bold text-slate-500 uppercase">
                            Total Receipts
                        </p>
                        <h3 class="mt-1 text-2xl font-bold">
                            {{ totalReceipts }}
                        </h3>
                        <p class="mt-1.5 text-xs text-slate-400">
                            Loaded receipts
                        </p>
                    </div>
                    <div
                        class="rounded-lg border-l-4 border-amber-400 bg-white p-5 shadow-sm"
                    >
                        <p class="text-xs font-bold text-slate-500 uppercase">
                            Awaiting Staging
                        </p>
                        <h3 class="mt-1 text-2xl font-bold text-amber-600">
                            {{ stats.awaitingStaging }}
                        </h3>
                        <p class="mt-1.5 text-xs text-slate-400">
                            {{ awaitingPercent }}% of total receipts
                        </p>
                    </div>
                    <div
                        class="rounded-lg border-l-4 border-[#23aa8f] bg-white p-5 shadow-sm"
                    >
                        <p class="text-xs font-bold text-slate-500 uppercase">
                            Ready for Putaway
                        </p>
                        <h3 class="mt-1 text-2xl font-bold text-[#23aa8f]">
                            {{ stats.readyForPutaway }}
                        </h3>
                        <p class="mt-1.5 text-xs text-slate-400">
                            {{ readyPercent }}% of total receipts
                        </p>
                    </div>
                    <div
                        class="cursor-pointer rounded-lg border-l-4 border-sky-400 bg-white p-5 shadow-sm transition hover:shadow-md"
                        @click="
                            router.visit(
                                '/goods-receipts/approved-purchase-orders',
                            )
                        "
                    >
                        <p class="text-xs font-bold text-slate-500 uppercase">
                            Waiting POs
                        </p>
                        <h3 class="mt-1 text-2xl font-bold text-sky-500">
                            {{ stats.waitingPurchaseOrders }}
                        </h3>
                        <p class="mt-1.5 text-xs font-semibold text-sky-400">
                            View approved POs →
                        </p>
                    </div>
                </div>

                <!-- Filters -->
                <div class="mb-6">
                    <div
                        class="grid w-full grid-cols-1 gap-2 md:grid-cols-[minmax(20rem,32rem)_14rem_2.5rem]"
                    >
                        <div class="relative w-full">
                            <Search
                                class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"
                            />
                            <Input
                                v-model="search"
                                type="text"
                                placeholder="Search GR #, PO #, operator..."
                                class="h-10 rounded-lg border-slate-200 bg-white pl-10 focus-visible:ring-[#007882]/30"
                            />
                        </div>
                        <select
                            v-model="statusFilter"
                            class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-600 outline-none focus:border-[#007882] focus:ring-2 focus:ring-[#007882]/20"
                        >
                            <option value="">All Status</option>
                            <option value="draft">Draft</option>
                            <option value="in_progress">In Progress</option>
                            <option value="approved">Approved</option>
                            <option value="partially_received">
                                Partially Received
                            </option>
                            <option value="received">Received</option>
                            <option value="cancelled">Cancelled</option>
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

                <!-- Table -->
                <div
                    class="min-h-[56vh] overflow-hidden rounded-lg border border-slate-100 bg-white shadow-sm"
                >
                    <div
                        v-if="filteredReceipts.length > 0"
                        class="overflow-x-auto"
                    >
                        <table class="w-full border-collapse text-left">
                            <thead
                                class="bg-slate-50 text-xs font-bold text-slate-600 uppercase"
                            >
                                <tr>
                                    <th class="px-6 py-4">GR Number</th>
                                    <th class="px-6 py-4">Source PO</th>
                                    <th class="px-6 py-4">Staging Area</th>
                                    <th class="px-6 py-4">Operator</th>
                                    <th class="px-6 py-4">Received Date</th>
                                    <th class="px-6 py-4 text-center">Items</th>
                                    <th class="px-6 py-4">Status</th>
                                    <th class="px-6 py-4 text-center">
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                                <tr
                                    v-for="receipt in paginatedReceipts"
                                    :key="receipt.id"
                                    class="transition hover:bg-slate-50/50"
                                >
                                    <td
                                        class="px-6 py-4 font-mono font-bold text-[#007882]"
                                    >
                                        {{ receipt.receipt_no }}
                                    </td>
                                    <td
                                        class="px-6 py-4 font-semibold text-slate-700"
                                    >
                                        {{
                                            receipt.purchase_order_no ??
                                            'Direct'
                                        }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="rounded bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600"
                                        >
                                            {{
                                                receipt.staging_area ??
                                                'INBOUND'
                                            }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-slate-500">
                                        {{ receipt.operator ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-slate-500">
                                        {{
                                            receipt.received_at ??
                                            receipt.created_at ??
                                            '-'
                                        }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-center font-bold text-slate-700"
                                    >
                                        {{ receipt.line_count }}
                                        <span
                                            class="text-xs font-normal text-slate-400"
                                            >SKU</span
                                        >
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="rounded-full px-2.5 py-1 text-xs font-bold uppercase"
                                            :class="statusClass(receipt.status)"
                                        >
                                            {{ statusLabel(receipt.status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <ApprovalActionMenu
                                            :status="receipt.status"
                                            view-label="View"
                                            :actionable-statuses="['draft']"
                                            @view="openDetail(receipt)"
                                            @approve="
                                                updateReceiptStatus(
                                                    receipt,
                                                    'approve',
                                                )
                                            "
                                            @reject="
                                                updateReceiptStatus(
                                                    receipt,
                                                    'reject',
                                                )
                                            "
                                            @cancel="
                                                updateReceiptStatus(
                                                    receipt,
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
                        v-if="filteredReceipts.length > 0"
                        :current-page="currentPage"
                        :total-pages="totalPages"
                        :total-rows="totalRows"
                        :page-start="pageStart"
                        :page-end="pageEnd"
                        :rows-per-page="pageSize"
                        @go-to-page="goToPage"
                        @update-rows-per-page="setRowsPerPage"
                    />

                    <div
                        v-if="filteredReceipts.length === 0"
                        class="p-16 text-center"
                    >
                        <div
                            class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-slate-50"
                        >
                            <Filter class="size-6 text-slate-300" />
                        </div>
                        <h3 class="font-bold text-[#2a4858]">
                            No goods receipts found
                        </h3>
                        <p class="mt-1 text-sm text-slate-500">
                            Create a new goods receipt or adjust your filters.
                        </p>
                    </div>
                </div>
            </section>

            <GRDetail
                v-else-if="view === 'detail' && detailReceipt"
                :receipt="detailReceipt"
            />

            <GREdit
                v-else-if="view === 'edit' && detailReceipt"
                ref="editGRRef"
                :receipt="detailReceipt"
                :staging-locations="stagingLocations"
            />
        </main>
    </AppLayout>
</template>
