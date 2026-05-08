<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowRight, Download, Filter, Plus, Search, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import ApprovalActionMenu from '@/components/master-data/ApprovalActionMenu.vue';
import TablePagination from '@/components/TablePagination.vue';
import { Button } from '@/components/ui/button';
import { usePagination } from '@/composables/usePagination';
import AppLayout from '@/layouts/AppLayout.vue';

type Putaway = {
    id: number;
    transfer_no: string;
    created_at?: string | null;
    goods_receipt_no?: string | null;
    assigned_staff: string;
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
        unit_cost: number;
        total_cost: number;
    }[];
};

const props = defineProps<{
    putaways: Putaway[];
    stats: {
        pendingReceipts: number;
        activeTasks: number;
        todayGoal: number;
        averageMinutes: number;
    };
}>();

const search = ref('');
const detailPutaway = ref<Putaway | null>(null);

const filteredPutaways = computed(() => {
    const term = search.value.trim().toLowerCase();

    if (!term) {
        return props.putaways;
    }

    return props.putaways.filter((putaway) =>
        [
            putaway.transfer_no,
            putaway.goods_receipt_no,
            putaway.assigned_staff,
            putaway.status,
        ]
            .filter(Boolean)
            .some((value) => String(value).toLowerCase().includes(term)),
    );
});

const {
    currentPage: putawaysPage,
    totalRows: putawaysTotalRows,
    totalPages: putawaysTotalPages,
    pageStart: putawaysPageStart,
    pageEnd: putawaysPageEnd,
    paginatedRows: paginatedPutaways,
    goToPage: goToPutawaysPage,
    pageSize: putawaysPageSize,
    setRowsPerPage: setPutawaysRowsPerPage,
} = usePagination(filteredPutaways, 10);

function statusLabel(status: string) {
    return status
        .split('_')
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
}

function statusClass(status: string) {
    const classes: Record<string, string> = {
        draft: 'bg-slate-100 text-slate-500',
        submitted: 'bg-amber-100 text-amber-700',
        approved: 'bg-blue-100 text-blue-700',
        rejected: 'bg-red-100 text-red-700',
        in_transit: 'bg-blue-100 text-blue-700',
        received: 'bg-emerald-100 text-emerald-700',
        cancelled: 'bg-rose-100 text-rose-700',
    };

    return classes[status] ?? 'bg-slate-100 text-slate-600';
}

function isDraft(putaway: Putaway) {
    return putaway.status === 'draft';
}

function openDetail(putaway: Putaway) {
    detailPutaway.value = putaway;
}

function viewPutaway(putaway: Putaway) {
    if (isDraft(putaway)) {
        router.visit(`/putaway/create?stock_transfer_id=${putaway.id}`);
        return;
    }

    openDetail(putaway);
}

function closeDetail() {
    detailPutaway.value = null;
}

function updatePutawayStatus(
    putaway: Putaway,
    action: 'approve' | 'reject' | 'cancel',
) {
    router.patch(
        `/putaway/${putaway.id}/${action}`,
        {},
        {
            preserveScroll: true,
            preserveState: false,
        },
    );
}
</script>

<template>
    <Head title="Putaway Movements" />

    <AppLayout>
        <main class="w-full bg-slate-100 p-4 text-slate-800 md:p-8">
            <header
                class="mb-6 flex flex-col items-start justify-between gap-4 md:flex-row md:items-center"
            >
                <div>
                    <h1
                        class="text-2xl font-black tracking-tight text-[#2a4858]"
                    >
                        Putaway Movements
                    </h1>
                    <p class="text-sm font-medium text-slate-500">
                        Inbound staging area to storage location management
                    </p>
                </div>
                <Link
                    href="/putaway/create"
                    class="flex items-center rounded-lg bg-[#007882] px-6 py-2.5 font-bold text-white shadow-lg transition hover:brightness-110"
                >
                    <Plus class="mr-2 h-4 w-4" />
                    New Putaway Task
                </Link>
            </header>

            <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-4">
                <Link
                    href="/putaway/completed-goods-receipts"
                    class="group rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-[#007882] hover:bg-teal-50"
                >
                    <div class="flex items-start justify-between">
                        <span
                            class="text-[10px] font-black tracking-widest text-slate-400 uppercase group-hover:text-[#007882]"
                        >
                            Pending In Staging
                        </span>
                        <ArrowRight
                            class="h-3 w-3 text-slate-300 group-hover:text-[#007882]"
                        />
                    </div>
                    <div class="mt-1 text-2xl font-black text-[#2a4858]">
                        {{ stats.pendingReceipts }}
                        <span class="text-sm font-medium text-slate-400"
                            >GRs</span
                        >
                    </div>
                </Link>
                <div
                    class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
                >
                    <span
                        class="text-[10px] font-black tracking-widest text-slate-400 uppercase"
                        >Active Tasks</span
                    >
                    <div class="mt-1 text-2xl font-black text-[#007882]">
                        {{ stats.activeTasks }}
                    </div>
                </div>
                <div
                    class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
                >
                    <span
                        class="text-[10px] font-black tracking-widest text-slate-400 uppercase"
                        >Today's Goal</span
                    >
                    <div class="mt-1 text-2xl font-black text-[#23aa8f]">
                        {{ stats.todayGoal }}%
                    </div>
                </div>
                <div
                    class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
                >
                    <span
                        class="text-[10px] font-black tracking-widest text-slate-400 uppercase"
                        >Avg. Time / Task</span
                    >
                    <div class="mt-1 text-2xl font-black text-slate-500">
                        {{ stats.averageMinutes }}m
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
                            placeholder="Search Putaway ID or GR..."
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
                            <Download class="mr-1 h-3.5 w-3.5" />
                            Export
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[860px] text-left">
                        <thead
                            class="border-b border-slate-100 bg-slate-50 text-[10px] font-black tracking-widest text-slate-500 uppercase"
                        >
                            <tr>
                                <th class="px-6 py-4">Document ID</th>
                                <th class="px-6 py-4">Created Date</th>
                                <th class="px-6 py-4">Source GR</th>
                                <th class="px-6 py-4">Assigned Staff</th>
                                <th class="px-6 py-4">Items</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <tr
                                v-for="putaway in paginatedPutaways"
                                :key="putaway.id"
                                class="transition hover:bg-slate-50"
                            >
                                <td class="px-6 py-4 font-bold text-[#007882]">
                                    {{ putaway.transfer_no }}
                                </td>
                                <td
                                    class="px-6 py-4 font-medium text-slate-500"
                                >
                                    {{ putaway.created_at ?? '-' }}
                                </td>
                                <td class="px-6 py-4 font-bold">
                                    {{ putaway.goods_receipt_no ?? '-' }}
                                </td>
                                <td
                                    class="px-6 py-4 font-medium text-slate-600"
                                >
                                    {{ putaway.assigned_staff }}
                                </td>
                                <td class="px-6 py-4 font-bold">
                                    {{ putaway.item_count }} items
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="rounded px-2.5 py-1 text-[0.65rem] font-extrabold uppercase"
                                        :class="statusClass(putaway.status)"
                                    >
                                        {{ statusLabel(putaway.status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <ApprovalActionMenu
                                        :status="putaway.status"
                                        view-label="View"
                                        :actionable-statuses="['draft']"
                                        @view="viewPutaway(putaway)"
                                        @approve="
                                            updatePutawayStatus(
                                                putaway,
                                                'approve',
                                            )
                                        "
                                        @reject="
                                            updatePutawayStatus(
                                                putaway,
                                                'reject',
                                            )
                                        "
                                        @cancel="
                                            updatePutawayStatus(
                                                putaway,
                                                'cancel',
                                            )
                                        "
                                    />
                                </td>
                            </tr>
                            <tr v-if="filteredPutaways.length === 0">
                                <td
                                    colspan="7"
                                    class="px-6 py-12 text-center text-sm text-slate-500"
                                >
                                    No putaway movements found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <TablePagination
                    :current-page="putawaysPage"
                    :total-pages="putawaysTotalPages"
                    :total-rows="putawaysTotalRows"
                    :page-start="putawaysPageStart"
                    :page-end="putawaysPageEnd"
                    :rows-per-page="putawaysPageSize"
                    @go-to-page="goToPutawaysPage"
                    @update-rows-per-page="setPutawaysRowsPerPage"
                />
            </div>

            <div
                v-if="detailPutaway"
                class="fixed inset-0 z-[75] flex items-center justify-center bg-[#2a4858]/20 p-4 backdrop-blur-sm"
                @click.self="closeDetail"
            >
                <section
                    class="flex max-h-[90vh] w-full max-w-5xl flex-col overflow-hidden rounded-lg bg-white shadow-2xl"
                >
                    <header
                        class="flex items-start justify-between gap-4 border-b border-slate-100 p-5"
                    >
                        <div>
                            <p
                                class="text-xs font-bold tracking-widest text-slate-400 uppercase"
                            >
                                Putaway Movement Detail
                            </p>
                            <h2 class="mt-1 text-xl font-bold text-[#2a4858]">
                                {{ detailPutaway.transfer_no }}
                            </h2>
                            <p class="mt-1 text-sm text-slate-500">
                                Source GR:
                                {{ detailPutaway.goods_receipt_no ?? '-' }}
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
                                    Created
                                </p>
                                <p class="font-bold text-[#2a4858]">
                                    {{ detailPutaway.created_at ?? '-' }}
                                </p>
                            </div>
                            <div class="rounded-lg bg-slate-50 p-3">
                                <p
                                    class="text-xs font-bold text-slate-400 uppercase"
                                >
                                    Staff
                                </p>
                                <p class="font-bold text-[#2a4858]">
                                    {{ detailPutaway.assigned_staff }}
                                </p>
                            </div>
                            <div class="rounded-lg bg-slate-50 p-3">
                                <p
                                    class="text-xs font-bold text-slate-400 uppercase"
                                >
                                    Status
                                </p>
                                <span
                                    class="mt-1 inline-flex rounded px-2.5 py-1 text-xs font-bold uppercase"
                                    :class="statusClass(detailPutaway.status)"
                                >
                                    {{ statusLabel(detailPutaway.status) }}
                                </span>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead>
                                    <tr class="border-b border-slate-100">
                                        <th
                                            class="py-3 pr-4 text-xs font-bold text-slate-400 uppercase"
                                        >
                                            Item
                                        </th>
                                        <th
                                            class="px-4 py-3 text-right text-xs font-bold text-slate-400 uppercase"
                                        >
                                            Qty
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-bold text-slate-400 uppercase"
                                        >
                                            Destination
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="line in detailPutaway.lines"
                                        :key="line.id"
                                        class="border-b border-slate-50"
                                    >
                                        <td class="py-4 pr-4">
                                            <div
                                                class="font-bold text-[#2a4858]"
                                            >
                                                {{ line.item_name ?? 'Item' }}
                                            </div>
                                            <p
                                                class="mt-1 text-xs text-slate-400"
                                            >
                                                {{ line.item_code ?? '-' }} /
                                                {{ line.unit_code ?? '-' }}
                                            </p>
                                        </td>
                                        <td
                                            class="px-4 py-4 text-right font-bold text-[#2a4858]"
                                        >
                                            {{ line.quantity }}
                                        </td>
                                        <td
                                            class="px-4 py-4 font-bold text-[#007882]"
                                        >
                                            {{ line.to_location ?? '-' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </AppLayout>
</template>
