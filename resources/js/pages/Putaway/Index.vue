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
import PTDetail from './PTDetail.vue';
import PTEdit from './PTEdit.vue';

type ReceiptLine = {
    item_id: number;
    unit_id: number;
    item_name?: string | null;
    item_code?: string | null;
    unit_code?: string | null;
    quantity_received: number;
    quantity_putaway: number;
    quantity_remaining: number;
};

type Receipt = {
    id: number;
    receipt_no: string;
    purchase_order_no?: string | null;
    staging_area?: string | null;
    total_remaining: number;
    lines: ReceiptLine[];
};

type PutawayLine = {
    id: number;
    item_id: number;
    unit_id: number;
    item_name?: string | null;
    item_code?: string | null;
    unit_code?: string | null;
    to_location?: string | null;
    to_location_id?: number | null;
    quantity: number;
    unit_cost: number;
    total_cost: number;
};

type Putaway = {
    id: number;
    transfer_no: string;
    goods_receipt_id?: number | null;
    goods_receipt_no?: string | null;
    priority: string;
    assigned_to_id?: number | null;
    note?: string | null;
    created_at?: string | null;
    updated_at?: string | null;
    approved_at?: string | null;
    cancelled_at?: string | null;
    assigned_staff: string;
    created_by?: string | null;
    approved_by?: string | null;
    cancelled_by?: string | null;
    item_count: number;
    total_quantity: number;
    status: string;
    receipt?: Receipt | null;
    lines: PutawayLine[];
};

type StorageLocation = {
    id: number;
    name: string;
    code?: string | null;
    warehouse_name?: string | null;
};

type Staff = {
    id: number;
    name: string;
};

const props = defineProps<{
    putaways: Putaway[];
    storageLocations: StorageLocation[];
    staff: Staff[];
    stats: {
        pendingReceipts: number;
        activeTasks: number;
        todayGoal: number;
        averageMinutes: number;
    };
}>();

const view = ref<'list' | 'detail' | 'edit'>('list');
const detailPutaway = ref<Putaway | null>(null);
const editPTRef = ref<InstanceType<typeof PTEdit> | null>(null);
const search = ref('');
const statusFilter = ref('');

const breadcrumbs = computed<BreadcrumbItem[]>(() => {
    if ((view.value === 'detail' || view.value === 'edit') && detailPutaway.value) {
        const crumbs: BreadcrumbItem[] = [
            { title: 'Stock Operations' },
            { title: 'Putaway', href: '/putaway' },
            { title: detailPutaway.value.transfer_no },
        ];
        if (view.value === 'edit') crumbs.push({ title: 'Edit' });
        return crumbs;
    }
    return [
        { title: 'Stock Operations' },
        { title: 'Putaway', href: '/putaway' },
    ];
});

const filteredPutaways = computed(() => {
    const term = search.value.trim().toLowerCase();
    return props.putaways.filter((p) => {
        const matchesSearch =
            !term ||
            [p.transfer_no, p.goods_receipt_no, p.assigned_staff, p.status]
                .filter(Boolean)
                .some((v) => String(v).toLowerCase().includes(term));
        const matchesStatus = !statusFilter.value || p.status === statusFilter.value;
        return matchesSearch && matchesStatus;
    });
});

const {
    currentPage,
    totalRows,
    totalPages,
    pageStart,
    pageEnd,
    paginatedRows: paginatedPutaways,
    goToPage,
    pageSize,
    setRowsPerPage,
} = usePagination(() => filteredPutaways.value, 10);

function statusLabel(status: string) {
    const labels: Record<string, string> = {
        draft: 'Draft',
        submitted: 'Submitted',
        approved: 'Approved',
        in_transit: 'In Transit',
        received: 'Received',
        cancelled: 'Cancelled',
        rejected: 'Rejected',
    };
    return labels[status] ?? status.split('_').map((w) => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
}

function statusClass(status: string) {
    const classes: Record<string, string> = {
        draft: 'bg-slate-100 text-slate-700',
        submitted: 'bg-amber-100 text-amber-700',
        approved: 'bg-green-100 text-green-700',
        in_transit: 'bg-blue-100 text-blue-700',
        received: 'bg-emerald-100 text-emerald-700',
        cancelled: 'bg-rose-100 text-rose-700',
        rejected: 'bg-red-100 text-red-700',
    };
    return classes[status] ?? 'bg-slate-100 text-slate-700';
}

function openDetail(putaway: Putaway) {
    detailPutaway.value = putaway;
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

function updatePutawayStatus(putaway: Putaway, action: 'approve' | 'reject' | 'cancel') {
    router.patch(`/putaway/${putaway.id}/${action}`, {}, { preserveScroll: true, preserveState: false });
}
</script>

<template>
    <Head title="Putaway Movements" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #actions>
            <!-- List -->
            <template v-if="view === 'list'">
                <Button
                    type="button"
                    class="h-9 rounded-lg bg-[#007882] px-4 text-xs font-bold text-white shadow-md hover:bg-[#006773]"
                    @click="router.visit('/putaway/completed-goods-receipts')"
                >
                    <Plus class="size-4" />
                    New Putaway Task
                </Button>
            </template>

            <!-- Detail -->
            <template v-else-if="view === 'detail'">
                <div class="flex gap-2">
                    <Button
                        v-if="detailPutaway?.status === 'draft'"
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

            <!-- Edit -->
            <template v-else-if="view === 'edit'">
                <div class="flex gap-2">
                    <Button
                        type="button"
                        variant="ghost"
                        class="h-9 font-semibold text-slate-600 hover:text-red-500"
                        @click="view = 'detail'; window.scrollTo({ top: 0, behavior: 'smooth' })"
                    >
                        Cancel
                    </Button>
                    <Button
                        type="button"
                        class="h-9 rounded-lg bg-[#23aa8f] px-4 text-xs font-bold text-white shadow-md hover:bg-[#1e917a]"
                        :disabled="!editPTRef?.canSubmit"
                        @click="editPTRef?.submit()"
                    >
                        {{ editPTRef?.isProcessing ? 'Saving...' : 'Save Changes' }}
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
                <div class="mb-8 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4 2xl:gap-6">
                    <div
                        class="cursor-pointer rounded-lg border-l-4 border-[#007882] bg-white p-5 shadow-sm transition hover:shadow-md"
                        @click="router.visit('/putaway/completed-goods-receipts')"
                    >
                        <p class="text-xs font-bold text-slate-500 uppercase">Pending in Staging</p>
                        <h3 class="mt-1 text-2xl font-bold text-[#007882]">{{ stats.pendingReceipts }}</h3>
                        <p class="mt-1.5 text-xs font-semibold text-[#007882]/70">View completed GRs →</p>
                    </div>
                    <div class="rounded-lg border-l-4 border-amber-400 bg-white p-5 shadow-sm">
                        <p class="text-xs font-bold text-slate-500 uppercase">Active Tasks</p>
                        <h3 class="mt-1 text-2xl font-bold text-amber-600">{{ stats.activeTasks }}</h3>
                        <p class="mt-1.5 text-xs text-slate-400">Draft + In Progress</p>
                    </div>
                    <div class="rounded-lg border-l-4 border-[#23aa8f] bg-white p-5 shadow-sm">
                        <p class="text-xs font-bold text-slate-500 uppercase">Today's Goal</p>
                        <h3 class="mt-1 text-2xl font-bold text-[#23aa8f]">{{ stats.todayGoal }}%</h3>
                        <p class="mt-1.5 text-xs text-slate-400">Target completion rate</p>
                    </div>
                    <div class="rounded-lg border-l-4 border-slate-300 bg-white p-5 shadow-sm">
                        <p class="text-xs font-bold text-slate-500 uppercase">Avg. Time / Task</p>
                        <h3 class="mt-1 text-2xl font-bold text-slate-500">{{ stats.averageMinutes }}m</h3>
                        <p class="mt-1.5 text-xs text-slate-400">Minutes per putaway task</p>
                    </div>
                </div>

                <!-- Filters -->
                <div class="mb-6">
                    <div class="grid w-full grid-cols-1 gap-2 md:grid-cols-[minmax(20rem,32rem)_14rem_2.5rem]">
                        <div class="relative w-full">
                            <Search class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400" />
                            <Input
                                v-model="search"
                                type="text"
                                placeholder="Search PTW #, GR #, staff..."
                                class="h-10 rounded-lg border-slate-200 bg-white pl-10 focus-visible:ring-[#007882]/30"
                            />
                        </div>
                        <select
                            v-model="statusFilter"
                            class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-600 outline-none focus:border-[#007882] focus:ring-2 focus:ring-[#007882]/20"
                        >
                            <option value="">All Status</option>
                            <option value="draft">Draft</option>
                            <option value="submitted">Submitted</option>
                            <option value="approved">Approved</option>
                            <option value="in_transit">In Transit</option>
                            <option value="received">Received</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="rejected">Rejected</option>
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
                <div class="min-h-[56vh] overflow-hidden rounded-lg border border-slate-100 bg-white shadow-sm">
                    <div v-if="filteredPutaways.length > 0" class="overflow-x-auto">
                        <table class="w-full border-collapse text-left">
                            <thead class="bg-slate-50 text-xs font-bold text-slate-600 uppercase">
                                <tr>
                                    <th class="px-6 py-4">Transfer #</th>
                                    <th class="px-6 py-4">Created</th>
                                    <th class="px-6 py-4">Source GR</th>
                                    <th class="px-6 py-4">Assigned Staff</th>
                                    <th class="px-6 py-4 text-center">Items</th>
                                    <th class="px-6 py-4">Status</th>
                                    <th class="px-6 py-4 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                                <tr
                                    v-for="putaway in paginatedPutaways"
                                    :key="putaway.id"
                                    class="transition hover:bg-slate-50/50"
                                >
                                    <td class="px-6 py-4 font-mono font-bold text-[#007882]">
                                        {{ putaway.transfer_no }}
                                    </td>
                                    <td class="px-6 py-4 text-slate-500">
                                        {{ putaway.created_at ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-slate-700">
                                        {{ putaway.goods_receipt_no ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-slate-600">
                                        {{ putaway.assigned_staff }}
                                    </td>
                                    <td class="px-6 py-4 text-center font-bold text-slate-700">
                                        {{ putaway.item_count }}
                                        <span class="text-xs font-normal text-slate-400">SKU</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="rounded-full px-2.5 py-1 text-xs font-bold uppercase"
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
                                            @view="openDetail(putaway)"
                                            @approve="updatePutawayStatus(putaway, 'approve')"
                                            @reject="updatePutawayStatus(putaway, 'reject')"
                                            @cancel="updatePutawayStatus(putaway, 'cancel')"
                                        />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <TablePagination
                        v-if="filteredPutaways.length > 0"
                        :current-page="currentPage"
                        :total-pages="totalPages"
                        :total-rows="totalRows"
                        :page-start="pageStart"
                        :page-end="pageEnd"
                        :rows-per-page="pageSize"
                        @go-to-page="goToPage"
                        @update-rows-per-page="setRowsPerPage"
                    />

                    <div v-if="filteredPutaways.length === 0" class="p-16 text-center">
                        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-slate-50">
                            <Filter class="size-6 text-slate-300" />
                        </div>
                        <h3 class="font-bold text-[#2a4858]">No putaway movements found</h3>
                        <p class="mt-1 text-sm text-slate-500">
                            Create a new putaway task or adjust your filters.
                        </p>
                    </div>
                </div>
            </section>

            <PTDetail
                v-else-if="view === 'detail' && detailPutaway"
                :putaway="detailPutaway"
            />

            <PTEdit
                v-else-if="view === 'edit' && detailPutaway"
                ref="editPTRef"
                :putaway="detailPutaway"
                :storage-locations="storageLocations"
                :staff="staff"
            />
        </main>
    </AppLayout>
</template>
