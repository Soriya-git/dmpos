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
import CreatePO from './CreatePO.vue';
import EditPO from './EditPO.vue';
import PODetail from './PODetail.vue';

type PurchaseStatus =
    | 'created'
    | 'approved'
    | 'rejected'
    | 'cancelled'
    | 'in_progress_receipt'
    | 'partially_received'
    | 'received'
    | 'closed';

type PurchaseOrderLine = {
    id: number;
    item_id: number;
    unit_id: number;
    item_name?: string | null;
    item_code?: string | null;
    unit_code?: string | null;
    quantity_ordered: number;
    quantity_received: number;
    quantity_remaining: number;
    unit_cost: number;
    line_total: number;
    status: string;
    note?: string | null;
};

type PurchaseOrder = {
    id: number;
    po_no: string;
    supplier_name?: string | null;
    supplier_phone?: string | null;
    supplier_address?: string | null;
    status: PurchaseStatus;
    order_date?: string | null;
    display_order_date?: string | null;
    expected_date?: string | null;
    display_expected_date?: string | null;
    created_by?: string | null;
    created_by_email?: string | null;
    approved_by?: string | null;
    approved_by_email?: string | null;
    display_approved_at?: string | null;
    rejected_by?: string | null;
    rejected_by_email?: string | null;
    display_rejected_at?: string | null;
    cancelled_by?: string | null;
    cancelled_by_email?: string | null;
    display_cancelled_at?: string | null;
    grand_total: number;
    note?: string | null;
    lines: PurchaseOrderLine[];
};

type InventoryItem = {
    id: number;
    name: string;
    code?: string | null;
    unit_id: number;
    unit_code?: string | null;
    cost: number;
};

type Unit = {
    id: number;
    name: string;
    code: string;
};

type Supplier = {
    id: number;
    name: string;
    phone?: string | null;
    address?: string | null;
};

const props = defineProps<{
    orders: PurchaseOrder[];
    items: InventoryItem[];
    units: Unit[];
    suppliers: Supplier[];
    nextPoNo: string;
    filters: {
        search?: string | null;
        status?: string | null;
    };
}>();

const view = ref<'dashboard' | 'new' | 'detail' | 'edit'>('dashboard');
const createPORef = ref<InstanceType<typeof CreatePO> | null>(null);
const editPORef = ref<InstanceType<typeof EditPO> | null>(null);
const detailOrder = ref<PurchaseOrder | null>(null);
const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');

const breadcrumbs = computed<BreadcrumbItem[]>(() => {
    if (view.value === 'new') {
        return [
            { title: 'Stock Operations' },
            { title: 'Purchase', href: '/purchase' },
            { title: 'Create' },
        ];
    }
    if (
        (view.value === 'detail' || view.value === 'edit') &&
        detailOrder.value
    ) {
        const crumbs: BreadcrumbItem[] = [
            { title: 'Stock Operations' },
            { title: 'Purchase', href: '/purchase' },
            { title: detailOrder.value.po_no },
        ];
        if (view.value === 'edit') crumbs.push({ title: 'Edit' });
        return crumbs;
    }
    return [
        { title: 'Stock Operations' },
        { title: 'Purchase', href: '/purchase' },
    ];
});

const {
    currentPage: ordersPage,
    totalRows: ordersTotalRows,
    totalPages: ordersTotalPages,
    pageStart: ordersPageStart,
    pageEnd: ordersPageEnd,
    paginatedRows: paginatedOrders,
    goToPage: goToOrdersPage,
    pageSize: ordersPageSize,
    setRowsPerPage: setOrdersRowsPerPage,
} = usePagination(() => props.orders, 10);

const today = () => {
    const date = new Date();
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

const totalOrders = computed(() => props.orders.length);
const pendingApproval = computed(
    () => props.orders.filter((order) => order.status === 'created').length,
);
const awaitingDelivery = computed(
    () =>
        props.orders.filter((order) =>
            ['approved', 'in_progress_receipt', 'partially_received'].includes(
                order.status,
            ),
        ).length,
);
const overdueOrders = computed(() => {
    const todayDate = today();

    return props.orders.filter((order) => {
        return (
            order.expected_date &&
            order.expected_date < todayDate &&
            !['received', 'closed', 'cancelled'].includes(order.status)
        );
    }).length;
});

const totalValue = computed(() =>
    props.orders.reduce((sum, o) => sum + Number(o.grand_total || 0), 0),
);

const pendingPercent = computed(() =>
    totalOrders.value
        ? Math.round((pendingApproval.value / totalOrders.value) * 100)
        : 0,
);

const awaitingPercent = computed(() =>
    totalOrders.value
        ? Math.round((awaitingDelivery.value / totalOrders.value) * 100)
        : 0,
);

const overduePercent = computed(() =>
    totalOrders.value
        ? Math.round((overdueOrders.value / totalOrders.value) * 100)
        : 0,
);

const supplierOptions = computed(() =>
    props.suppliers.map((s) => ({
        value: s.name,
        label: s.name,
        description: s.phone,
        meta: s.address,
    })),
);

function money(value: number | string | null | undefined) {
    return Number(value ?? 0).toLocaleString(undefined, {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

function statusLabel(value: string) {
    const labels: Record<string, string> = {
        created: 'Pending',
        approved: 'Approved',
        rejected: 'Rejected',
        cancelled: 'Cancelled',
        in_progress_receipt: 'Receiving',
        partially_received: 'Partial',
        received: 'Received',
        closed: 'Closed',
        open: 'Open',
    };

    return labels[value] ?? value.replaceAll('_', ' ');
}

function statusClass(value: string) {
    const classes: Record<string, string> = {
        created: 'bg-amber-100 text-amber-700',
        approved: 'bg-green-100 text-green-700',
        rejected: 'bg-red-100 text-red-700',
        cancelled: 'bg-slate-100 text-slate-600',
        in_progress_receipt: 'bg-cyan-100 text-cyan-700',
        partially_received: 'bg-blue-100 text-blue-700',
        received: 'bg-blue-100 text-blue-700',
        closed: 'bg-slate-900 text-white',
        open: 'bg-amber-100 text-amber-700',
    };

    return classes[value] ?? 'bg-slate-100 text-slate-600';
}

function applyFilters() {
    router.get(
        '/purchase',
        {
            search: search.value || undefined,
            status: status.value || undefined,
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

    router.get('/purchase', {}, { preserveScroll: true, replace: true });
}

function showCreate() {
    view.value = 'new';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function showDashboard() {
    view.value = 'dashboard';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function openDetail(order: PurchaseOrder) {
    detailOrder.value = order;
    view.value = 'detail';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function showEdit() {
    view.value = 'edit';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function updateOrderStatus(
    order: PurchaseOrder,
    action: 'approve' | 'reject' | 'cancel',
) {
    router.patch(
        `/purchase/${order.id}/${action}`,
        {},
        {
            preserveScroll: true,
            preserveState: false,
        },
    );
}
</script>

<template>
    <Head title="Purchase Orders" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #actions>
            <template v-if="view === 'dashboard'">
                <Button
                    type="button"
                    class="h-9 rounded-lg bg-[#007882] px-4 text-xs font-bold text-white shadow-md hover:bg-[#006773]"
                    @click="showCreate"
                >
                    <Plus class="size-4" />
                    New Purchase Order
                </Button>
            </template>
            <template v-else-if="view === 'new'">
                <div class="flex gap-2">
                    <Button
                        type="button"
                        variant="ghost"
                        class="h-9 font-semibold text-slate-600 hover:text-red-500"
                        :disabled="createPORef?.isProcessing"
                        @click="showDashboard"
                    >
                        Cancel
                    </Button>
                    <Button
                        type="button"
                        class="h-9 rounded-lg bg-[#007882] px-4 text-xs font-bold text-white shadow-md hover:bg-[#006773]"
                        :disabled="createPORef?.isProcessing"
                        @click="createPORef?.submit()"
                    >
                        Save Order
                    </Button>
                </div>
            </template>
            <template v-else-if="view === 'detail'">
                <div class="flex gap-2">
                    <Button
                        v-if="detailOrder?.status === 'created'"
                        type="button"
                        class="h-9 rounded-lg bg-[#007882] px-4 text-xs font-bold text-white shadow-md hover:bg-[#006773]"
                        @click="showEdit"
                    >
                        Edit
                    </Button>
                    <Button
                        type="button"
                        variant="ghost"
                        class="h-9 font-semibold text-slate-600"
                        @click="showDashboard"
                    >
                        ← Back
                    </Button>
                </div>
            </template>
            <template v-else>
                <div class="flex gap-2">
                    <Button
                        type="button"
                        variant="ghost"
                        class="h-9 font-semibold text-slate-600 hover:text-red-500"
                        :disabled="editPORef?.isProcessing"
                        @click="view = 'detail'"
                    >
                        Cancel
                    </Button>
                    <Button
                        type="button"
                        class="h-9 rounded-lg bg-[#007882] px-4 text-xs font-bold text-white shadow-md hover:bg-[#006773]"
                        :disabled="editPORef?.isProcessing"
                        @click="editPORef?.submit()"
                    >
                        Save Changes
                    </Button>
                </div>
            </template>
        </template>

        <main
            class="h-[calc(100dvh-4rem)] w-full [scrollbar-gutter:stable] overflow-y-scroll bg-[#f8fafc] p-4 text-slate-800 md:h-[calc(100dvh-5rem)] md:p-6 xl:p-8 2xl:p-10"
        >
            <section v-if="view === 'dashboard'" class="w-full">
                <div
                    v-if="($page.props.flash as any)?.success"
                    class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-[#2a4858]"
                >
                    {{ ($page.props.flash as any).success }}
                </div>

                <div
                    class="mb-8 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4 2xl:gap-6"
                >
                    <div
                        class="rounded-lg border-l-4 border-[#007882] bg-white p-5 shadow-sm"
                    >
                        <p class="text-xs font-bold text-slate-500 uppercase">
                            Total POs
                        </p>
                        <h3 class="mt-1 text-2xl font-bold">
                            {{ totalOrders }}
                        </h3>
                        <p class="mt-1.5 text-xs text-slate-400">
                            {{ money(totalValue) }} total value
                        </p>
                    </div>
                    <div
                        class="rounded-lg border-l-4 border-[#23aa8f] bg-white p-5 shadow-sm"
                    >
                        <p class="text-xs font-bold text-slate-500 uppercase">
                            Pending Approval
                        </p>
                        <h3 class="mt-1 text-2xl font-bold text-[#23aa8f]">
                            {{ pendingApproval }}
                        </h3>
                        <p class="mt-1.5 text-xs text-slate-400">
                            {{ pendingPercent }}% of total orders
                        </p>
                    </div>
                    <div
                        class="rounded-lg border-l-4 border-amber-400 bg-white p-5 shadow-sm"
                    >
                        <p class="text-xs font-bold text-slate-500 uppercase">
                            Awaiting Delivery
                        </p>
                        <h3 class="mt-1 text-2xl font-bold text-amber-600">
                            {{ awaitingDelivery }}
                        </h3>
                        <p class="mt-1.5 text-xs text-slate-400">
                            {{ awaitingPercent }}% of total orders
                        </p>
                    </div>
                    <div
                        class="rounded-lg border-l-4 border-red-400 bg-white p-5 shadow-sm"
                    >
                        <p class="text-xs font-bold text-slate-500 uppercase">
                            Overdue
                        </p>
                        <h3 class="mt-1 text-2xl font-bold text-red-500">
                            {{ overdueOrders }}
                        </h3>
                        <p class="mt-1.5 text-xs text-slate-400">
                            {{ overduePercent }}% of total orders
                        </p>
                    </div>
                </div>

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
                                placeholder="Search PO # or supplier..."
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
                            <option value="created">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="in_progress_receipt">
                                Receiving
                            </option>
                            <option value="partially_received">Partial</option>
                            <option value="received">Received</option>
                            <option value="closed">Closed</option>
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
                    <div v-if="orders.length > 0" class="overflow-x-auto">
                        <table class="w-full border-collapse text-left">
                            <thead
                                class="bg-slate-50 text-xs font-bold text-slate-600 uppercase"
                            >
                                <tr>
                                    <th class="px-6 py-4">PO Number</th>
                                    <th class="px-6 py-4">Supplier</th>
                                    <th class="px-6 py-4">Order Date</th>
                                    <th class="px-6 py-4 text-right">
                                        Total Amount
                                    </th>
                                    <th class="px-6 py-4">Status</th>
                                    <th class="px-6 py-4 text-center">
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                                <tr
                                    v-for="order in paginatedOrders"
                                    :key="order.id"
                                    class="transition hover:bg-slate-50/50"
                                >
                                    <td
                                        class="px-6 py-4 font-mono font-bold text-[#007882]"
                                    >
                                        {{ order.po_no }}
                                    </td>
                                    <td
                                        class="px-6 py-4 font-semibold text-slate-800"
                                    >
                                        {{
                                            order.supplier_name ?? 'No supplier'
                                        }}
                                        <p
                                            v-if="order.supplier_phone"
                                            class="mt-1 text-xs font-medium text-slate-400"
                                        >
                                            {{ order.supplier_phone }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600">
                                        {{
                                            order.display_order_date ??
                                            order.order_date ??
                                            '-'
                                        }}
                                    </td>
                                    <td class="px-6 py-4 text-right font-bold">
                                        {{ money(order.grand_total) }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="rounded-full px-2.5 py-1 text-xs font-bold uppercase"
                                            :class="statusClass(order.status)"
                                        >
                                            {{ statusLabel(order.status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <ApprovalActionMenu
                                            :status="order.status"
                                            view-label="View"
                                            :actionable-statuses="['created']"
                                            @view="openDetail(order)"
                                            @approve="
                                                updateOrderStatus(
                                                    order,
                                                    'approve',
                                                )
                                            "
                                            @reject="
                                                updateOrderStatus(
                                                    order,
                                                    'reject',
                                                )
                                            "
                                            @cancel="
                                                updateOrderStatus(
                                                    order,
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
                        v-if="orders.length > 0"
                        :current-page="ordersPage"
                        :total-pages="ordersTotalPages"
                        :total-rows="ordersTotalRows"
                        :page-start="ordersPageStart"
                        :page-end="ordersPageEnd"
                        :rows-per-page="ordersPageSize"
                        @go-to-page="goToOrdersPage"
                        @update-rows-per-page="setOrdersRowsPerPage"
                    />

                    <div v-else class="p-16 text-center">
                        <div
                            class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-slate-50"
                        >
                            <Filter class="size-6 text-slate-300" />
                        </div>
                        <h3 class="font-bold text-[#2a4858]">
                            No purchase orders found
                        </h3>
                        <p class="mt-1 text-sm text-slate-500">
                            Create a new purchase order or adjust your filters.
                        </p>
                    </div>
                </div>
            </section>

            <CreatePO
                v-else-if="view === 'new'"
                ref="createPORef"
                :items="items"
                :units="units"
                :next-po-no="nextPoNo"
                :supplier-options="supplierOptions"
                @success="showDashboard"
            />

            <PODetail
                v-else-if="view === 'detail' && detailOrder"
                :order="detailOrder"
            />

            <EditPO
                v-else-if="view === 'edit' && detailOrder"
                ref="editPORef"
                :order="detailOrder"
                :items="items"
                :units="units"
                :supplier-options="supplierOptions"
                @success="showDashboard"
            />
        </main>
    </AppLayout>
</template>
