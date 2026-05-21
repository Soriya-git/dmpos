<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowRight,
    ClipboardCheck,
    PackageCheck,
    PlusCircle,
    Search,
    Truck,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import ApprovalActionMenu from '@/components/master-data/ApprovalActionMenu.vue';
import TablePagination from '@/components/TablePagination.vue';
import { usePagination } from '@/composables/usePagination';
import AppLayout from '@/layouts/AppLayout.vue';
import GRModal from './GRModal.vue';

type GoodsReceipt = {
    id: number;
    receipt_no: string;
    purchase_order_no?: string | null;
    status: string;
    created_at?: string | null;
    updated_at?: string | null;
    received_at?: string | null;
    staging_area?: string | null;
    operator?: string | null;
    cancelled_by?: string | null;
    line_count: number;
    lines: {
        id: number;
        item_name?: string | null;
        item_code?: string | null;
        unit_code?: string | null;
        staging_area?: string | null;
        quantity_received: number;
    }[];
};

const props = defineProps<{
    receipts: GoodsReceipt[];
    stats: {
        waitingPurchaseOrders: number;
        awaitingStaging: number;
        readyForPutaway: number;
    };
}>();

const search = ref('');
const detailReceipt = ref<GoodsReceipt | null>(null);

const filteredReceipts = computed(() => {
    const term = search.value.trim().toLowerCase();

    if (!term) {
        return props.receipts;
    }

    return props.receipts.filter((receipt) => {
        return [receipt.receipt_no, receipt.purchase_order_no, receipt.status]
            .filter(Boolean)
            .some((value) => String(value).toLowerCase().includes(term));
    });
});

const {
    currentPage: receiptsPage,
    totalRows: receiptsTotalRows,
    totalPages: receiptsTotalPages,
    pageStart: receiptsPageStart,
    pageEnd: receiptsPageEnd,
    paginatedRows: paginatedReceipts,
    goToPage: goToReceiptsPage,
    pageSize: receiptsPageSize,
    setRowsPerPage: setReceiptsRowsPerPage,
} = usePagination(filteredReceipts, 10);

function statusClass(status: string) {
    const classes: Record<string, string> = {
        draft: 'bg-slate-100 text-slate-700',
        approved: 'bg-green-100 text-green-700',
        rejected: 'bg-red-100 text-red-700',
        in_progress: 'bg-amber-100 text-amber-700',
        partially_received: 'bg-blue-100 text-blue-700',
        received: 'bg-emerald-100 text-emerald-700',
        cancelled: 'bg-rose-100 text-rose-700',
    };

    return classes[status] ?? 'bg-slate-100 text-slate-700';
}

function statusLabel(status: string) {
    return status
        .split('_')
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
}

function openDetail(receipt: GoodsReceipt) {
    detailReceipt.value = receipt;
}

function closeDetail() {
    detailReceipt.value = null;
}

function updateReceiptStatus(
    receipt: GoodsReceipt,
    action: 'approve' | 'reject' | 'cancel',
) {
    router.patch(
        `/goods-receipts/${receipt.id}/${action}`,
        {},
        {
            preserveScroll: true,
            preserveState: false,
        },
    );
}
</script>

<template>
    <Head title="Goods Receipt" />

    <AppLayout>
        <main class="w-full bg-slate-100 p-4 text-slate-800 md:p-8">
            <header
                class="mb-8 flex flex-col items-start justify-between gap-4 md:flex-row md:items-center"
            >
                <div>
                    <h1
                        class="flex items-center text-2xl font-bold text-[#2a4858]"
                    >
                        <PackageCheck class="mr-3 h-7 w-7 text-[#007882]" />
                        Goods Receipt Management
                    </h1>
                    <p class="text-slate-500">
                        Manage inbound deliveries and staging area arrivals
                    </p>
                </div>
                <Link
                    href="/goods-receipts/create"
                    class="flex items-center rounded-lg bg-[#007882] px-6 py-3 font-bold text-white shadow-lg transition hover:bg-[#006873]"
                >
                    <PlusCircle class="mr-2 h-5 w-5" />
                    Create Goods Receipt
                </Link>
            </header>

            <div class="mb-8 grid grid-cols-1 gap-6 md:grid-cols-3">
                <Link
                    href="/goods-receipts/approved-purchase-orders"
                    class="rounded-xl border-t-4 border-[#007882] bg-white p-6 shadow-sm transition hover:-translate-y-0.5"
                >
                    <p
                        class="text-xs font-bold tracking-wider text-slate-400 uppercase"
                    >
                        Waiting POs
                    </p>
                    <div class="mt-2 flex items-end justify-between gap-4">
                        <span class="text-3xl font-black">
                            {{ stats.waitingPurchaseOrders }}
                        </span>
                        <span
                            class="flex items-center text-sm font-bold text-[#007882]"
                        >
                            View Waiting POs
                            <ArrowRight class="ml-1 h-4 w-4" />
                        </span>
                    </div>
                </Link>

                <div
                    class="rounded-xl border-t-4 border-amber-400 bg-white p-6 shadow-sm"
                >
                    <p
                        class="text-xs font-bold tracking-wider text-slate-400 uppercase"
                    >
                        Awaiting Staging
                    </p>
                    <div class="mt-2 flex items-end justify-between gap-4">
                        <span class="text-3xl font-black">
                            {{ stats.awaitingStaging }}
                        </span>
                        <span class="flex items-center text-sm text-slate-400">
                            <Truck class="mr-1 h-4 w-4" />
                            Trucks arriving
                        </span>
                    </div>
                </div>

                <div
                    class="rounded-xl border-t-4 border-[#23aa8f] bg-white p-6 shadow-sm"
                >
                    <p
                        class="text-xs font-bold tracking-wider text-slate-400 uppercase"
                    >
                        Ready for Putaway
                    </p>
                    <div class="mt-2 flex items-end justify-between gap-4">
                        <span class="text-3xl font-black">
                            {{ stats.readyForPutaway }}
                        </span>
                        <span
                            class="flex items-center text-sm font-bold text-[#23aa8f]"
                        >
                            <PackageCheck class="mr-1 h-4 w-4" />
                            Staging Zone
                        </span>
                    </div>
                </div>
            </div>

            <div
                class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
            >
                <div
                    class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50 p-4 md:flex-row md:items-center md:justify-between"
                >
                    <h2
                        class="text-sm font-bold tracking-tight text-slate-700 uppercase"
                    >
                        Recent Receipts
                    </h2>
                    <div class="relative w-full md:w-72">
                        <Search
                            class="absolute top-2.5 left-3 h-4 w-4 text-slate-400"
                        />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search GR or PO"
                            class="w-full rounded-lg border border-slate-200 py-2 pr-4 pl-9 text-sm outline-none focus:ring-2 focus:ring-[#007882]/20"
                        />
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-left">
                        <thead
                            class="bg-slate-50 text-xs font-bold tracking-widest text-slate-500 uppercase"
                        >
                            <tr>
                                <th class="px-6 py-4">GR Number</th>
                                <th class="px-6 py-4">Source PO</th>
                                <th class="px-6 py-4">Staging Area</th>
                                <th class="px-6 py-4">Saved Date</th>
                                <th class="px-6 py-4">Items</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-slate-100 text-sm font-medium"
                        >
                            <tr
                                v-for="receipt in paginatedReceipts"
                                :key="receipt.id"
                                class="transition hover:bg-slate-50"
                            >
                                <td class="px-6 py-4 font-bold text-[#007882]">
                                    {{ receipt.receipt_no }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ receipt.purchase_order_no ?? 'Direct' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="rounded bg-slate-100 px-2 py-1"
                                    >
                                        {{ receipt.staging_area ?? 'INBOUND' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-500">
                                    {{
                                        receipt.received_at ??
                                        receipt.created_at ??
                                        '-'
                                    }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ receipt.line_count }} SKUs
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="rounded-full px-3 py-1 text-[0.7rem] font-extrabold uppercase"
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
                            <tr v-if="filteredReceipts.length === 0">
                                <td
                                    colspan="7"
                                    class="px-6 py-12 text-center text-sm text-slate-500"
                                >
                                    <ClipboardCheck
                                        class="mx-auto mb-3 h-8 w-8 text-slate-300"
                                    />
                                    No goods receipts found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <TablePagination
                    :current-page="receiptsPage"
                    :total-pages="receiptsTotalPages"
                    :total-rows="receiptsTotalRows"
                    :page-start="receiptsPageStart"
                    :page-end="receiptsPageEnd"
                    :rows-per-page="receiptsPageSize"
                    @go-to-page="goToReceiptsPage"
                    @update-rows-per-page="setReceiptsRowsPerPage"
                />
            </div>

            <GRModal
                v-if="detailReceipt"
                :receipt="detailReceipt"
                @close="closeDetail"
            />
        </main>
    </AppLayout>
</template>
