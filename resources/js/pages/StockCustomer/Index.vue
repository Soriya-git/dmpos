<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowRight,
    Boxes,
    Filter,
    PackagePlus,
    Plus,
    Search,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import ApprovalActionMenu from '@/components/master-data/ApprovalActionMenu.vue';
import TablePagination from '@/components/TablePagination.vue';
import { usePagination } from '@/composables/usePagination';
import AppLayout from '@/layouts/AppLayout.vue';
import SCModal from './SCModal.vue';

type Keep = {
    id: number;
    transfer_no: string;
    created_at?: string | null;
    approved_at?: string | null;
    invoice_no?: string | null;
    customer_name: string;
    customer_phone?: string | null;
    assigned_staff: string;
    created_by?: string | null;
    approved_by?: string | null;
    cancelled_by?: string | null;
    item_count: number;
    total_quantity: number;
    status: string;
    lines: {
        id: number;
        item_name?: string | null;
        item_code?: string | null;
        unit_code?: string | null;
        to_location?: string | null;
        quantity: number;
    }[];
};

const props = defineProps<{
    keeps: Keep[];
    stats: {
        pendingInvoices: number;
        activeKeeps: number;
        receivedKeeps: number;
        totalQuantity: number;
    };
}>();

const search = ref('');
const detailKeep = ref<Keep | null>(null);

const filteredKeeps = computed(() => {
    const term = search.value.trim().toLowerCase();

    if (!term) return props.keeps;

    return props.keeps.filter((keep) =>
        [
            keep.transfer_no,
            keep.invoice_no,
            keep.customer_name,
            keep.customer_phone,
            keep.status,
        ]
            .filter(Boolean)
            .some((value) => String(value).toLowerCase().includes(term)),
    );
});

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
} = usePagination(filteredKeeps, 10);

function statusLabel(status: string) {
    return status
        .split('_')
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
}

function statusClass(status: string) {
    const classes: Record<string, string> = {
        draft: 'bg-slate-100 text-slate-500',
        received: 'bg-emerald-100 text-emerald-700',
        rejected: 'bg-red-100 text-red-700',
        cancelled: 'bg-rose-100 text-rose-700',
    };

    return classes[status] ?? 'bg-slate-100 text-slate-600';
}

function viewKeep(keep: Keep) {
    if (keep.status === 'draft') {
        router.visit(`/stock-customer/create?stock_transfer_id=${keep.id}`);
        return;
    }

    detailKeep.value = keep;
}

function updateStatus(keep: Keep, action: 'approve' | 'reject' | 'cancel') {
    router.patch(
        `/stock-customer/${keep.id}/${action}`,
        {},
        { preserveScroll: true, preserveState: false },
    );
}
</script>

<template>
    <Head title="Customer Keep Stock" />

    <AppLayout>
        <main class="w-full bg-slate-100 p-4 text-slate-800 md:p-8">
            <header
                class="mb-6 flex flex-col items-start justify-between gap-4 md:flex-row md:items-center"
            >
                <div>
                    <h1
                        class="text-2xl font-black tracking-tight text-[#2a4858]"
                    >
                        Customer Keep Stock
                    </h1>
                    <p class="text-sm font-medium text-slate-500">
                        Customer-owned leftover items kept in restaurant
                        locations
                    </p>
                </div>
                <Link
                    href="/stock-customer/create"
                    class="flex items-center rounded-lg bg-[#007882] px-6 py-2.5 font-bold text-white shadow-lg transition hover:brightness-110"
                >
                    <Plus class="mr-2 h-4 w-4" />
                    New Customer Stock
                </Link>
            </header>

            <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-4">
                <Link
                    href="/stock-customer/invoices"
                    class="group rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-[#007882] hover:bg-teal-50"
                >
                    <div class="flex items-start justify-between">
                        <span
                            class="text-[10px] font-black tracking-widest text-slate-400 uppercase group-hover:text-[#007882]"
                        >
                            Select Invoice
                        </span>
                        <ArrowRight
                            class="h-3 w-3 text-slate-300 group-hover:text-[#007882]"
                        />
                    </div>
                    <div class="mt-1 text-2xl font-black text-[#2a4858]">
                        {{ stats.pendingInvoices }}
                        <span class="text-sm font-medium text-slate-400"
                            >invoices</span
                        >
                    </div>
                </Link>
                <div
                    class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
                >
                    <span
                        class="text-[10px] font-black tracking-widest text-slate-400 uppercase"
                        >Draft Keeps</span
                    >
                    <div class="mt-1 text-2xl font-black text-[#007882]">
                        {{ stats.activeKeeps }}
                    </div>
                </div>
                <div
                    class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
                >
                    <span
                        class="text-[10px] font-black tracking-widest text-slate-400 uppercase"
                        >Received Keeps</span
                    >
                    <div class="mt-1 text-2xl font-black text-[#23aa8f]">
                        {{ stats.receivedKeeps }}
                    </div>
                </div>
                <div
                    class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
                >
                    <span
                        class="text-[10px] font-black tracking-widest text-slate-400 uppercase"
                        >Customer Qty</span
                    >
                    <div class="mt-1 text-2xl font-black text-slate-500">
                        {{ stats.totalQuantity }}
                    </div>
                </div>
            </div>

            <div
                class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
            >
                <div
                    class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50/30 p-4 md:flex-row md:items-center md:justify-between"
                >
                    <div class="relative w-full md:w-80">
                        <Search
                            class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400"
                        />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search keep stock, invoice or customer..."
                            class="w-full rounded-lg border border-slate-200 bg-white py-2 pr-4 pl-9 text-sm"
                        />
                    </div>
                    <div class="flex gap-2">
                        <button
                            type="button"
                            class="inline-flex items-center rounded-lg border bg-white px-3 py-2 text-xs font-bold text-slate-600"
                        >
                            <Filter class="mr-1 h-3.5 w-3.5" />
                            Filter
                        </button>
                        <button
                            type="button"
                            class="inline-flex items-center rounded-lg border bg-white px-3 py-2 text-xs font-bold text-slate-600"
                        >
                            <Boxes class="mr-1 h-3.5 w-3.5" />
                            Export
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[920px] text-left">
                        <thead
                            class="border-b border-slate-100 bg-slate-50 text-[10px] font-black tracking-widest text-slate-500 uppercase"
                        >
                            <tr>
                                <th class="px-6 py-4">Document ID</th>
                                <th class="px-6 py-4">Created Date</th>
                                <th class="px-6 py-4">Invoice</th>
                                <th class="px-6 py-4">Customer</th>
                                <th class="px-6 py-4">Items</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <tr
                                v-for="keep in paginatedRows"
                                :key="keep.id"
                                class="transition hover:bg-slate-50"
                            >
                                <td class="px-6 py-4 font-bold text-[#007882]">
                                    {{ keep.transfer_no }}
                                </td>
                                <td
                                    class="px-6 py-4 font-medium text-slate-500"
                                >
                                    {{ keep.created_at ?? '-' }}
                                </td>
                                <td class="px-6 py-4 font-bold">
                                    {{ keep.invoice_no ?? '-' }}
                                </td>
                                <td
                                    class="px-6 py-4 font-medium text-slate-600"
                                >
                                    {{ keep.customer_name }}
                                    <div class="text-[10px] text-slate-400">
                                        {{ keep.customer_phone ?? '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-bold">
                                    {{ keep.item_count }} items /
                                    {{ keep.total_quantity }} qty
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="rounded px-2.5 py-1 text-[0.65rem] font-extrabold uppercase"
                                        :class="statusClass(keep.status)"
                                    >
                                        {{ statusLabel(keep.status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <ApprovalActionMenu
                                        :status="keep.status"
                                        view-label="View"
                                        :actionable-statuses="['draft']"
                                        @view="viewKeep(keep)"
                                        @approve="updateStatus(keep, 'approve')"
                                        @reject="updateStatus(keep, 'reject')"
                                        @cancel="updateStatus(keep, 'cancel')"
                                    />
                                </td>
                            </tr>
                            <tr v-if="filteredKeeps.length === 0">
                                <td
                                    colspan="7"
                                    class="px-6 py-12 text-center text-sm text-slate-500"
                                >
                                    No customer keep stock documents found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <TablePagination
                    :current-page="currentPage"
                    :total-pages="totalPages"
                    :total-rows="totalRows"
                    :page-start="pageStart"
                    :page-end="pageEnd"
                    :rows-per-page="pageSize"
                    @go-to-page="goToPage"
                    @update-rows-per-page="setRowsPerPage"
                />
            </div>

            <SCModal
                v-if="detailKeep"
                :keep="detailKeep"
                @close="detailKeep = null"
            />
        </main>
    </AppLayout>
</template>
