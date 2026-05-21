<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Filter,
    PackagePlus,
    Plus,
    RotateCcw,
    Search,
    Trash2,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import ApprovalActionMenu from '@/components/master-data/ApprovalActionMenu.vue';
import SearchDropdown from '@/components/SearchDropdown.vue';
import TablePagination from '@/components/TablePagination.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { usePagination } from '@/composables/usePagination';
import AppLayout from '@/layouts/AppLayout.vue';
import POModal from './POModal.vue';

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

type FormLine = {
    item_id: number | '';
    unit_id: number | '';
    quantity_ordered: number;
    unit_cost: number;
    note: string;
};

const props = defineProps<{
    orders: PurchaseOrder[];
    items: InventoryItem[];
    units: Unit[];
    nextPoNo: string;
    filters: {
        search?: string | null;
        status?: string | null;
    };
}>();

const view = ref<'dashboard' | 'new'>('dashboard');
const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const detailOrder = ref<PurchaseOrder | null>(null);

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

const makeLine = (): FormLine => ({
    item_id: '',
    unit_id: '',
    quantity_ordered: 1,
    unit_cost: 0,
    note: '',
});

const form = useForm({
    supplier_name: '',
    supplier_phone: '',
    supplier_address: '',
    order_date: today(),
    expected_date: '',
    note: '',
    lines: [makeLine()],
});

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

const grandTotal = computed(() => {
    return form.lines.reduce((total, line) => {
        return (
            total +
            Number(line.quantity_ordered || 0) * Number(line.unit_cost || 0)
        );
    }, 0);
});

const supplierOptions = computed(() => {
    const suppliers = new Map<string, PurchaseOrder>();

    props.orders.forEach((order) => {
        const supplier = order.supplier_name?.trim();

        if (supplier && !suppliers.has(supplier.toLowerCase())) {
            suppliers.set(supplier.toLowerCase(), order);
        }
    });

    return Array.from(suppliers.values()).map((order) => ({
        value: order.supplier_name ?? '',
        label: order.supplier_name ?? '',
        description: order.supplier_phone,
        meta: order.supplier_address,
    }));
});

const itemOptions = computed(() => {
    return props.items.map((item) => ({
        value: item.id,
        label: item.name,
        description: item.code,
        meta: `${item.unit_code ?? 'Unit'} / ${money(item.cost)}`,
    }));
});

const canSubmit = computed(() => {
    return (
        form.supplier_name.trim().length > 0 &&
        form.lines.some((line) => line.item_id && line.quantity_ordered > 0)
    );
});

const firstLineError = computed(() => {
    const errors = form.errors as Record<string, string>;

    return (
        errors.lines ||
        Object.entries(errors).find(([key]) => key.startsWith('lines.'))?.[1]
    );
});

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

function selectedItem(itemId: number | '') {
    return props.items.find((item) => item.id === itemId);
}

function selectItem(line: FormLine) {
    const item = selectedItem(line.item_id);

    if (!item) return;

    line.unit_id = item.unit_id;
    line.unit_cost = Number(item.cost || 0);
}

function selectSupplier(
    option: { description?: string | null; meta?: string | null } | null,
) {
    if (!option) return;

    form.supplier_phone = option.description ?? '';
    form.supplier_address = option.meta ?? '';
}

function lineTotal(line: FormLine) {
    return Number(line.quantity_ordered || 0) * Number(line.unit_cost || 0);
}

function submitOrder() {
    form.clearErrors();

    if (!canSubmit.value) {
        if (!form.supplier_name.trim()) {
            form.setError('supplier_name', 'Supplier is required.');
        }

        if (!form.lines.some((line) => line.item_id)) {
            form.setError('lines', 'Add at least one item.');
        }

        return;
    }

    form.post('/purchase', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            form.order_date = today();
            form.lines = [makeLine()];
            showDashboard();
        },
    });
}

function openDetail(order: PurchaseOrder) {
    detailOrder.value = order;
}

function closeDetail() {
    detailOrder.value = null;
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

    <AppLayout>
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
                    </div>
                </div>

                <div
                    class="mb-6 flex flex-col items-stretch justify-between gap-4 xl:flex-row xl:items-center"
                >
                    <div
                        class="grid w-full grid-cols-1 gap-2 md:grid-cols-[minmax(20rem,32rem)_14rem_2.5rem] xl:w-auto"
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

                    <Button
                        type="button"
                        class="h-11 rounded-lg bg-[#007882] px-6 font-bold text-white shadow-md hover:bg-[#006773]"
                        @click="showCreate"
                    >
                        <Plus class="size-4" />
                        New Purchase Order
                    </Button>
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

            <section v-else class="w-full">
                <div
                    class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-center"
                >
                    <div class="flex items-center gap-4">
                        <Button
                            type="button"
                            variant="outline"
                            class="h-10 w-10 rounded-full border-slate-200 bg-white p-0 text-slate-500 hover:text-[#007882]"
                            title="Back"
                            @click="showDashboard"
                        >
                            <ArrowLeft class="size-4" />
                        </Button>
                        <div>
                            <h2 class="text-2xl font-bold text-slate-800">
                                Create Purchase Order
                            </h2>
                            <p class="text-sm text-slate-500">
                                Fill in the details to issue a new inventory
                                request
                            </p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <Button
                            type="button"
                            variant="ghost"
                            class="font-semibold text-slate-600 hover:text-red-500"
                            :disabled="form.processing"
                            @click="showDashboard"
                        >
                            Cancel
                        </Button>
                        <Button
                            type="button"
                            class="rounded-lg bg-[#007882] px-6 font-bold text-white shadow-md hover:bg-[#006773]"
                            :disabled="form.processing"
                            @click="submitOrder"
                        >
                            Save Order
                        </Button>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 xl:grid-cols-4 2xl:gap-8">
                    <div class="space-y-6 xl:col-span-1">
                        <div
                            class="rounded-lg border border-slate-100 bg-white p-6 shadow-sm"
                        >
                            <h3
                                class="mb-4 flex items-center text-sm font-bold text-slate-700 uppercase"
                            >
                                <PackagePlus
                                    class="mr-2 size-4 text-[#007882]"
                                />
                                Info
                            </h3>
                            <div class="space-y-4">
                                <div>
                                    <label
                                        class="mb-1 block text-xs font-bold text-slate-500 uppercase"
                                    >
                                        PO Number
                                    </label>
                                    <Input
                                        :model-value="nextPoNo"
                                        readonly
                                        class="h-10 rounded-lg border-slate-200 bg-slate-50 font-mono text-slate-500"
                                    />
                                </div>
                                <div>
                                    <label
                                        class="mb-1 block text-xs font-bold text-slate-500 uppercase"
                                    >
                                        Supplier
                                    </label>
                                    <SearchDropdown
                                        v-model="form.supplier_name"
                                        :options="supplierOptions"
                                        allow-custom
                                        placeholder="Supplier name"
                                        search-placeholder="Search supplier..."
                                        empty-text="No supplier found. Type a new supplier name."
                                        @select="selectSupplier"
                                    />
                                    <InputError
                                        :message="form.errors.supplier_name"
                                    />
                                </div>
                                <div>
                                    <label
                                        class="mb-1 block text-xs font-bold text-slate-500 uppercase"
                                    >
                                        Supplier Phone
                                    </label>
                                    <Input
                                        v-model="form.supplier_phone"
                                        class="h-10 rounded-lg border-slate-200"
                                        placeholder="Optional phone"
                                    />
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label
                                            class="mb-1 block text-xs font-bold text-amber-600 uppercase"
                                        >
                                            Expected Date
                                        </label>
                                        <Input
                                            v-model="form.expected_date"
                                            type="date"
                                            class="h-10 rounded-lg border-amber-200 bg-amber-50 text-xs"
                                        />
                                    </div>
                                    <div>
                                        <label
                                            class="mb-1 block text-xs font-bold text-slate-500 uppercase"
                                        >
                                            Order Date
                                        </label>
                                        <Input
                                            v-model="form.order_date"
                                            type="date"
                                            class="h-10 rounded-lg border-slate-200 text-xs"
                                        />
                                    </div>
                                </div>
                                <InputError :message="form.errors.order_date" />
                                <div>
                                    <label
                                        class="mb-1 block text-xs font-bold text-slate-500 uppercase"
                                    >
                                        Note
                                    </label>
                                    <textarea
                                        v-model="form.note"
                                        class="min-h-20 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#007882] focus:ring-2 focus:ring-[#007882]/20"
                                        placeholder="Optional note"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6 xl:col-span-3">
                        <div
                            class="overflow-hidden rounded-lg border border-slate-100 bg-white shadow-sm"
                        >
                            <div
                                class="flex items-center justify-between border-b border-slate-100 bg-slate-50 p-4"
                            >
                                <h3
                                    class="text-xs font-bold tracking-wider text-slate-700 uppercase"
                                >
                                    Order Items
                                </h3>
                                <Button
                                    type="button"
                                    class="h-8 rounded bg-[#23aa8f] px-3 text-xs font-bold text-white hover:bg-[#1e917a]"
                                    @click="addLine"
                                >
                                    <Plus class="size-3.5" />
                                    Add Item
                                </Button>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead
                                        class="border-b border-slate-100 text-slate-500"
                                    >
                                        <tr>
                                            <th
                                                class="min-w-64 px-4 py-3 text-left font-semibold"
                                            >
                                                Item Details
                                            </th>
                                            <th
                                                class="px-4 py-3 text-center font-semibold"
                                            >
                                                Qty
                                            </th>
                                            <th
                                                class="px-4 py-3 text-left font-semibold"
                                            >
                                                Unit
                                            </th>
                                            <th
                                                class="px-4 py-3 text-right font-semibold"
                                            >
                                                Cost
                                            </th>
                                            <th
                                                class="px-4 py-3 text-right font-semibold"
                                            >
                                                Total
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
                                                    empty-text="No item found."
                                                    input-class="border-transparent font-medium"
                                                    @select="selectItem(line)"
                                                />
                                            </td>
                                            <td class="px-4 py-4">
                                                <Input
                                                    v-model.number="
                                                        line.quantity_ordered
                                                    "
                                                    type="number"
                                                    min="0.0001"
                                                    step="0.0001"
                                                    class="h-9 w-24 rounded border-slate-200 text-center"
                                                />
                                            </td>
                                            <td class="px-4 py-4">
                                                <select
                                                    v-model="line.unit_id"
                                                    class="h-9 w-24 rounded border border-slate-200 bg-white px-2 text-xs outline-none focus:border-[#007882] focus:ring-2 focus:ring-[#007882]/20"
                                                >
                                                    <option value="">
                                                        Unit
                                                    </option>
                                                    <option
                                                        v-for="unit in units"
                                                        :key="unit.id"
                                                        :value="unit.id"
                                                    >
                                                        {{ unit.code }}
                                                    </option>
                                                </select>
                                            </td>
                                            <td class="px-4 py-4">
                                                <Input
                                                    v-model.number="
                                                        line.unit_cost
                                                    "
                                                    type="number"
                                                    min="0"
                                                    step="0.0001"
                                                    class="h-9 w-28 rounded border-slate-200 text-right font-mono"
                                                />
                                            </td>
                                            <td
                                                class="px-4 py-4 text-right font-mono font-bold text-slate-700"
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
                            <InputError
                                class="px-4 pb-4"
                                :message="firstLineError"
                            />
                        </div>

                        <div
                            class="rounded-lg bg-[#2a4858] p-6 text-white shadow-lg"
                        >
                            <div class="flex items-center justify-between">
                                <span class="text-lg font-bold">
                                    Estimated Grand Total
                                </span>
                                <span class="text-2xl font-bold text-[#fafa6e]">
                                    {{ money(grandTotal) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <POModal
                v-if="detailOrder"
                :order="detailOrder"
                @close="closeDetail"
            />
        </main>
    </AppLayout>
</template>
