<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    History,
    Maximize2,
    Minimize2,
    Pencil,
    ReceiptText,
    Search,
    ShoppingCart,
    Trash2,
    Utensils,
    X,
} from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import PosCategoryPills from '@/components/pos/PosCategoryPills.vue';
import PosMenuCard from '@/components/pos/PosMenuCard.vue';
import PosOrderLineItem from '@/components/pos/PosOrderLineItem.vue';
import PosSegmentedTabs from '@/components/pos/PosSegmentedTabs.vue';
import PosTotalsPanel from '@/components/pos/PosTotalsPanel.vue';
import { usePosFormatting } from '@/composables/usePosFormatting';
import AppLayout from '@/layouts/AppLayout.vue';
import Payment from '@/pages/Seats/Payment.vue';

type MenuItem = {
    id: number;
    name: string;
    code?: string | null;
    image?: string | null;
    category_name?: string | null;
    unit_price: number;
};

type Category = {
    id: number;
    name: string;
};

type CartLine = {
    id: number;
    menu_id: number;
    menu_name: string;
    qty: number;
    unit_price: number;
    subtotal: number;
    tax_amount: number;
    total_amount: number;
    note?: string | null;
    status: string;
};

type OrderData = {
    id: number;
    order_no: string;
    status: string;
    invoice_no?: string | null;
    invoice_status?: string | null;
    subtotal: number;
    tax_amount: number;
    total_amount: number;
    created_at?: string | null;
    lines: CartLine[];
};

type InvoiceData = {
    id: number;
    invoice_no: string;
    status: string;
    subtotal: number;
    discount_amount: number;
    tax_amount: number;
    grand_total: number;
    paid_amount: number;
    balance_amount: number;
    created_at?: string | null;
    lines: {
        id: number;
        menu_name: string;
        qty: number;
        total_amount: number;
        note?: string | null;
    }[];
};

const props = defineProps<{
    posSession: {
        id: number;
        session_no: string;
    };
    diningSession: {
        id: number;
        session_no: string;
        status?: string | null;
        seat_name?: string | null;
        seat_type?: string | null;
        customer_name?: string | null;
        customer_phone?: string | null;
        invoice_no?: string | null;
        invoice_status?: string | null;
        invoice_total?: number | null;
    };
    menus: MenuItem[];
    categories: Category[];
    cart: OrderData;
    historyOrders: OrderData[];
    invoices: InvoiceData[];
    filters: {
        category_id?: string | number | null;
        search?: string | null;
    };
}>();

const activeTab = ref<'cart' | 'orders' | 'invoice'>('cart');
const search = ref(props.filters.search ?? '');
const categoryId = ref<string | number | null>(props.filters.category_id ?? '');
const pageRoot = ref<HTMLElement | null>(null);
const isFullscreen = ref(false);
const instructionNotes = ref<Record<number, string>>({});
const discountMode = ref<'percent' | 'amount'>('percent');
const discountValue = ref(0);
const paymentOpen = ref(false);
const editCustomerOpen = ref(false);
const { imageUrl, money } = usePosFormatting();

type PaymentPayload = {
    method: string;
    currency: 'USD' | 'KHR';
    receivedAmount: number;
    operationStatus: 'invoice' | 'invoice_receipt_done';
};

const addForm = useForm({
    menu_id: null as number | null,
    qty: 1,
    note: null as string | null,
});

const updateForm = useForm({
    qty: 1,
});

const customerForm = useForm({
    customer_phone: props.diningSession.customer_phone ?? '',
    customer_name: props.diningSession.customer_name ?? '',
});

const cartLineCount = computed(() => props.cart.lines.length);
const hasInvoice = computed(() => Boolean(props.diningSession.invoice_no));
const billableOrders = computed(() => {
    return props.historyOrders.filter((order) => {
        return (
            !order.invoice_no && !['draft', 'cancelled'].includes(order.status)
        );
    });
});
const hasBillableOrders = computed(() => billableOrders.value.length > 0);
const canCloseOrder = computed(() => {
    return (
        hasInvoice.value &&
        !hasBillableOrders.value &&
        ['invoiced', 'paid', 'pay_later'].includes(
            props.diningSession.status ?? '',
        )
    );
});

const invoiceHeaderStatus = computed(() => {
    return props.diningSession.invoice_status === 'paid' ? 'Paid' : 'Pending';
});

const cartLinesByMenuId = computed(() => {
    return new Map(props.cart.lines.map((line) => [line.menu_id, line]));
});

const menusById = computed(() => {
    return new Map(props.menus.map((menu) => [menu.id, menu]));
});

const tabs = computed(() => [
    {
        count: cartLineCount.value,
        icon: ShoppingCart,
        label: 'Cart',
        value: 'cart',
    },
    {
        icon: History,
        label: 'Orders',
        value: 'orders',
    },
    {
        count: props.invoices.length,
        icon: ReceiptText,
        label: 'Invoice',
        value: 'invoice',
    },
]);

const allOrdersSubtotal = computed(() => {
    return billableOrders.value.reduce((total, order) => {
        return total + Number(order.subtotal ?? 0);
    }, 0);
});

const allOrdersTax = computed(() => {
    return billableOrders.value.reduce((total, order) => {
        return total + Number(order.tax_amount ?? 0);
    }, 0);
});

const discountAmount = computed(() => {
    const value = Math.max(0, Number(discountValue.value || 0));

    if (discountMode.value === 'percent') {
        return Math.min(
            allOrdersSubtotal.value,
            allOrdersSubtotal.value * (value / 100),
        );
    }

    return Math.min(allOrdersSubtotal.value, value);
});

const allOrdersTotalAfterDiscount = computed(() => {
    return Math.max(0, allOrdersSubtotal.value - discountAmount.value);
});

const allOrdersFinalAmount = computed(() => {
    return allOrdersTotalAfterDiscount.value + allOrdersTax.value;
});

function applyFilters() {
    router.get(
        `/orders/${props.diningSession.id}/menu`,
        {
            category_id: categoryId.value || undefined,
            search: search.value || undefined,
        },
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        },
    );
}

function clearFilters() {
    search.value = '';
    categoryId.value = '';

    router.get(
        `/orders/${props.diningSession.id}/menu`,
        {},
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        },
    );
}

function addToDish(menu: MenuItem) {
    addForm.menu_id = menu.id;
    addForm.qty = 1;
    addForm.note = instructionNotes.value[menu.id] || null;

    addForm.post(`/orders/${props.diningSession.id}/items`, {
        preserveScroll: true,
    });
}

function increaseQty(line: CartLine) {
    updateQty(line, line.qty + 1);
}

function decreaseQty(line: CartLine) {
    if (line.qty <= 1) return;
    updateQty(line, line.qty - 1);
}

function updateQty(line: CartLine, qty: number) {
    updateForm.qty = qty;

    updateForm.patch(`/orders/${props.diningSession.id}/items/${line.id}`, {
        preserveScroll: true,
    });
}

function removeLine(line: CartLine) {
    router.delete(`/orders/${props.diningSession.id}/items/${line.id}`, {
        preserveScroll: true,
    });
}

function clearCart() {
    if (props.cart.lines.length === 0) return;

    router.delete(`/orders/${props.diningSession.id}/items`, {
        preserveScroll: true,
    });
}

function sendToKitchen() {
    router.post(
        `/orders/${props.diningSession.id}/send-kitchen`,
        {},
        {
            preserveScroll: true,
        },
    );
}

function checkBill() {
    if (cartLineCount.value > 0 || !hasBillableOrders.value) return;

    paymentOpen.value = true;
}

function confirmPayment(payload: PaymentPayload) {
    router.post(
        `/orders/${props.diningSession.id}/settle`,
        {
            method: payload.method,
            currency: payload.currency,
            received_amount: payload.receivedAmount,
            operation_status: payload.operationStatus,
            discount_amount: discountAmount.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                paymentOpen.value = false;
                activeTab.value = 'invoice';
            },
        },
    );
}

function openEditCustomer() {
    customerForm.customer_phone = props.diningSession.customer_phone ?? '';
    customerForm.customer_name = props.diningSession.customer_name ?? '';
    editCustomerOpen.value = true;
}

function saveCustomer() {
    customerForm.patch(`/orders/${props.diningSession.id}/customer`, {
        preserveScroll: true,
        onSuccess: () => {
            editCustomerOpen.value = false;
        },
    });
}

function closeOrder() {
    router.post(
        `/orders/${props.diningSession.id}/close`,
        {},
        {
            preserveScroll: true,
        },
    );
}

async function toggleFullscreen() {
    if (isFullscreen.value) {
        if (document.fullscreenElement) {
            await document.exitFullscreen();
        }

        isFullscreen.value = false;
        return;
    }

    if (pageRoot.value?.requestFullscreen) {
        await pageRoot.value.requestFullscreen();
    }

    isFullscreen.value = true;
}

function syncFullscreenState() {
    isFullscreen.value = document.fullscreenElement === pageRoot.value;
}

function goBack() {
    window.history.back();
}

function cartLineForMenu(menu: MenuItem) {
    return cartLinesByMenuId.value.get(menu.id) ?? null;
}

function menuImageForLine(line: CartLine) {
    return imageUrl(menusById.value.get(line.menu_id)?.image);
}

function instructionForMenu(menu: MenuItem) {
    if (Object.prototype.hasOwnProperty.call(instructionNotes.value, menu.id)) {
        return instructionNotes.value[menu.id] ?? '';
    }

    return cartLineForMenu(menu)?.note ?? '';
}

function updateInstructionNote(menu: MenuItem, note: string) {
    instructionNotes.value = {
        ...instructionNotes.value,
        [menu.id]: note,
    };
}

function orderStatusLabel(order: OrderData) {
    const status = order.invoice_status ?? order.status;

    const labels: Record<string, string> = {
        draft: 'Draft',
        sent_to_kitchen: 'Confirmed',
        preparing: 'Preparing',
        ready: 'Ready',
        served: 'Served',
        paid: 'Paid',
        pay_later: 'Invoiced',
        issued: 'Invoiced',
        partially_paid: 'Partially Paid',
        cancelled: 'Cancelled',
    };

    return labels[status] ?? status.replaceAll('_', ' ').toUpperCase();
}

function invoiceStatusLabel(status: string) {
    return status === 'paid' ? 'Paid' : 'Pending';
}

function commitInstructionNote(menu: MenuItem) {
    const line = cartLineForMenu(menu);
    const cleanNote = instructionForMenu(menu).trim();

    if (!line) return;

    router.patch(
        `/orders/${props.diningSession.id}/items/${line.id}`,
        {
            note: cleanNote || null,
            qty: line.qty,
        },
        {
            preserveScroll: true,
        },
    );
}

function editLineInstruction(line: CartLine) {
    const note = window.prompt('Additional instruction', line.note ?? '');

    if (note === null) return;

    const cleanNote = note.trim();
    instructionNotes.value = {
        ...instructionNotes.value,
        [line.menu_id]: cleanNote,
    };

    router.patch(
        `/orders/${props.diningSession.id}/items/${line.id}`,
        {
            note: cleanNote || null,
            qty: line.qty,
        },
        {
            preserveScroll: true,
        },
    );
}

onMounted(() => {
    document.addEventListener('fullscreenchange', syncFullscreenState);
});

onBeforeUnmount(() => {
    document.removeEventListener('fullscreenchange', syncFullscreenState);
});
</script>

<template>
    <Head title="Open Order" />

    <AppLayout>
        <div
            ref="pageRoot"
            class="flex h-[calc(100vh-80px)] flex-col overflow-hidden bg-gray-50 lg:flex-row"
            :class="isFullscreen ? 'fixed inset-0 z-50 h-screen' : ''"
        >
            <section
                class="flex min-h-0 flex-1 flex-col border-r border-gray-100 bg-white"
            >
                <div
                    class="space-y-3 border-b border-gray-100 px-4 py-4 sm:px-6"
                >
                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-gray-400 transition hover:bg-gray-100 hover:text-[#2A4858]"
                            @click="goBack"
                        >
                            <ArrowLeft class="h-4 w-4" />
                        </button>

                        <div class="relative w-full max-w-sm">
                            <Search
                                class="absolute top-1/2 left-4 h-3.5 w-3.5 -translate-y-1/2 text-gray-400"
                            />
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search products..."
                                class="w-full rounded-xl border border-transparent bg-gray-50 py-2.5 pr-24 pl-10 text-sm transition outline-none focus:border-[#007882] focus:bg-white"
                                @keyup.enter="applyFilters"
                            />
                            <button
                                type="button"
                                class="absolute top-1/2 right-2 -translate-y-1/2 rounded-lg px-3 py-1.5 text-[10px] font-black text-[#007882] hover:bg-[#23AA8F]/10"
                                @click="applyFilters"
                            >
                                SEARCH
                            </button>
                        </div>

                        <button
                            type="button"
                            class="hidden h-9 shrink-0 items-center gap-2 rounded-xl border border-gray-100 px-3 text-xs font-bold text-[#2A4858] transition hover:border-[#23AA8F]/40 hover:bg-gray-50 sm:inline-flex"
                            @click="toggleFullscreen"
                        >
                            <Minimize2 v-if="isFullscreen" class="h-4 w-4" />
                            <Maximize2 v-else class="h-4 w-4" />
                            {{
                                isFullscreen
                                    ? 'Exit Full Screen'
                                    : 'Full Screen'
                            }}
                        </button>
                    </div>

                    <div class="flex items-center gap-2">
                        <PosCategoryPills
                            v-model="categoryId"
                            class="min-w-0 flex-1"
                            :categories="categories"
                            @change="applyFilters"
                        />
                        <button
                            v-if="search || categoryId"
                            type="button"
                            class="shrink-0 rounded-xl border border-gray-100 px-3 py-2 text-[10px] font-black text-gray-400 hover:border-[#23AA8F]/40 hover:text-[#2A4858]"
                            @click="clearFilters"
                        >
                            CLEAR
                        </button>
                    </div>
                </div>

                <div
                    class="min-h-0 flex-1 overflow-y-auto bg-gray-50/30 p-4 [scrollbar-color:#cbd5e1_transparent] [scrollbar-width:thin]"
                >
                    <div
                        class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6"
                    >
                        <PosMenuCard
                            v-for="menu in menus"
                            :key="menu.id"
                            :menu="menu"
                            :image-src="imageUrl(menu.image)"
                            :price="money(menu.unit_price)"
                            :quantity="cartLineForMenu(menu)?.qty ?? 0"
                            :note="instructionForMenu(menu)"
                            :processing="
                                addForm.processing || updateForm.processing
                            "
                            @add="addToDish"
                            @commit-note="commitInstructionNote"
                            @update-note="updateInstructionNote(menu, $event)"
                        />
                    </div>

                    <div
                        v-if="menus.length === 0"
                        class="flex min-h-64 flex-col items-center justify-center rounded-xl border border-dashed border-gray-200 bg-white p-10 text-center text-gray-400"
                    >
                        <Utensils class="mb-3 h-10 w-10" />
                        <p class="text-sm font-bold text-[#2A4858]">
                            No menu found.
                        </p>
                        <p class="mt-1 text-xs">
                            Try another search or category.
                        </p>
                    </div>
                </div>
            </section>

            <aside
                class="flex h-[48vh] min-h-0 w-full flex-col bg-white lg:h-auto lg:w-[360px]"
            >
                <div class="border-b border-gray-100 bg-white p-4">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <h2
                                class="truncate text-lg font-black text-[#2A4858]"
                            >
                                {{ diningSession.seat_name ?? 'Open Order' }}
                            </h2>
                            <p
                                class="truncate text-[10px] font-bold text-gray-400 uppercase"
                            >
                                <template
                                    v-if="
                                        diningSession.customer_phone ||
                                        diningSession.customer_name
                                    "
                                >
                                    {{ diningSession.customer_phone ?? '-' }} /
                                    {{
                                        diningSession.customer_name ??
                                        'Walk-in Customer'
                                    }}
                                </template>
                                <template v-else>Walk-in Customer</template>
                            </p>
                            <p
                                v-if="diningSession.invoice_no"
                                class="mt-0.5 truncate text-[10px] font-black text-[#007882] uppercase"
                            >
                                Invoice {{ diningSession.invoice_no }}
                            </p>
                            <p
                                v-if="diningSession.invoice_no"
                                class="mt-0.5 truncate text-[10px] font-bold text-[#2A4858]"
                            >
                                {{
                                    money(
                                        diningSession.invoice_total ??
                                            allOrdersFinalAmount,
                                    )
                                }}
                                ({{ invoiceHeaderStatus }})
                            </p>
                        </div>

                        <button
                            type="button"
                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-gray-50 text-gray-400 hover:text-[#007882]"
                            title="Edit customer"
                            @click="openEditCustomer"
                        >
                            <Pencil class="h-3.5 w-3.5" />
                        </button>
                    </div>
                </div>

                <div class="p-3">
                    <PosSegmentedTabs v-model="activeTab" :tabs="tabs" />
                </div>

                <div
                    class="min-h-0 flex-1 overflow-y-auto px-4 [scrollbar-color:#cbd5e1_transparent] [scrollbar-width:thin]"
                >
                    <div v-if="activeTab === 'cart'" class="space-y-2 py-2">
                        <div
                            class="flex items-center justify-between gap-3 pb-1"
                        >
                            <p
                                class="text-[10px] font-black tracking-widest text-gray-400 uppercase"
                            >
                                {{ cartLineCount }} item(s)
                            </p>

                            <button
                                v-if="cart.lines.length > 0"
                                type="button"
                                class="inline-flex h-7 items-center gap-1 rounded-lg border border-red-100 px-2 text-[10px] font-bold text-red-500 transition hover:bg-red-50"
                                title="Remove all items"
                                @click="clearCart"
                            >
                                <Trash2 class="h-3.5 w-3.5" />
                                Remove All
                            </button>
                        </div>

                        <PosOrderLineItem
                            v-for="line in cart.lines"
                            :key="line.id"
                            editable
                            :line="line"
                            :unit-price="money(line.unit_price)"
                            :total-price="money(line.total_amount)"
                            :image-src="menuImageForLine(line)"
                            @decrease="decreaseQty(line)"
                            @increase="increaseQty(line)"
                            @note="editLineInstruction(line)"
                            @remove="removeLine(line)"
                        />

                        <div
                            v-if="cart.lines.length === 0"
                            class="rounded-xl border border-dashed border-gray-200 p-8 text-center text-sm text-gray-400"
                        >
                            No dish selected yet.
                        </div>
                    </div>

                    <div v-if="activeTab === 'orders'" class="space-y-3 py-2">
                        <article
                            v-for="order in historyOrders"
                            :key="order.id"
                            class="rounded-xl border border-gray-100 bg-white p-3 shadow-sm"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3
                                        class="truncate text-xs font-black text-[#2A4858]"
                                    >
                                        {{ order.order_no }}
                                    </h3>
                                    <p class="mt-0.5 text-[10px] text-gray-400">
                                        {{ order.created_at }}
                                    </p>
                                </div>

                                <span
                                    class="rounded-full bg-gray-100 px-2 py-1 text-[9px] font-bold text-gray-500 uppercase"
                                >
                                    {{ orderStatusLabel(order) }}
                                </span>
                            </div>

                            <div class="mt-3 space-y-1.5">
                                <div
                                    v-for="line in order.lines"
                                    :key="line.id"
                                    class="space-y-0.5"
                                >
                                    <div
                                        class="flex justify-between gap-3 text-[11px]"
                                    >
                                        <span
                                            class="min-w-0 truncate text-gray-500"
                                        >
                                            {{ line.menu_name }} x
                                            {{ line.qty }}
                                        </span>
                                        <span class="font-bold text-[#2A4858]">
                                            {{ money(line.total_amount) }}
                                        </span>
                                    </div>
                                    <p
                                        v-if="line.note"
                                        class="truncate pl-2 text-[10px] font-medium text-[#007882]"
                                    >
                                        {{ line.note }}
                                    </p>
                                </div>
                            </div>

                            <div
                                class="mt-3 border-t border-dashed pt-2 text-right text-xs font-black text-[#007882]"
                            >
                                {{ money(order.total_amount) }}
                            </div>
                        </article>

                        <div
                            v-if="historyOrders.length === 0"
                            class="rounded-xl border border-dashed border-gray-200 p-8 text-center text-sm text-gray-400"
                        >
                            No previous order history for this seat.
                        </div>
                    </div>

                    <div v-if="activeTab === 'invoice'" class="space-y-3 py-2">
                        <article
                            v-for="invoice in invoices"
                            :key="invoice.id"
                            class="rounded-xl border border-gray-100 bg-white p-3 shadow-sm"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3
                                        class="truncate text-xs font-black text-[#2A4858]"
                                    >
                                        {{ invoice.invoice_no }}
                                    </h3>
                                    <p class="mt-0.5 text-[10px] text-gray-400">
                                        {{ invoice.created_at }}
                                    </p>
                                </div>

                                <span
                                    class="rounded-full px-2 py-1 text-[9px] font-bold uppercase"
                                    :class="
                                        invoice.status === 'paid'
                                            ? 'bg-[#23AA8F]/10 text-[#007882]'
                                            : 'bg-orange-50 text-orange-500'
                                    "
                                >
                                    {{ invoiceStatusLabel(invoice.status) }}
                                </span>
                            </div>

                            <div class="mt-3 space-y-1.5">
                                <div
                                    v-for="line in invoice.lines"
                                    :key="line.id"
                                    class="space-y-0.5"
                                >
                                    <div
                                        class="flex justify-between gap-3 text-[11px]"
                                    >
                                        <span
                                            class="min-w-0 truncate text-gray-500"
                                        >
                                            {{ line.menu_name }} x
                                            {{ line.qty }}
                                        </span>
                                        <span class="font-bold text-[#2A4858]">
                                            {{ money(line.total_amount) }}
                                        </span>
                                    </div>
                                    <p
                                        v-if="line.note"
                                        class="truncate pl-2 text-[10px] font-medium text-[#007882]"
                                    >
                                        {{ line.note }}
                                    </p>
                                </div>
                            </div>

                            <div
                                class="mt-3 space-y-1 border-t border-dashed pt-2"
                            >
                                <div
                                    class="flex justify-between text-[10px] font-bold"
                                >
                                    <span class="text-gray-400">Discount</span>
                                    <span class="text-[#2A4858]">
                                        {{ money(invoice.discount_amount) }}
                                    </span>
                                </div>
                                <div
                                    class="flex justify-between text-xs font-black text-[#007882]"
                                >
                                    <span>Total</span>
                                    <span>{{
                                        money(invoice.grand_total)
                                    }}</span>
                                </div>
                            </div>
                        </article>

                        <div
                            v-if="invoices.length === 0"
                            class="rounded-xl border border-dashed border-gray-200 p-8 text-center text-sm text-gray-400"
                        >
                            No invoices for this seat yet.
                        </div>
                    </div>
                </div>

                <template v-if="activeTab === 'cart' && cart.lines.length > 0">
                    <PosTotalsPanel
                        :subtotal="money(cart.subtotal)"
                        action-label="SEND TO KITCHEN"
                        :show-action="cart.lines.length > 0"
                        variant="cart"
                        @action="sendToKitchen"
                    />
                </template>

                <div
                    v-else-if="
                        activeTab === 'cart' &&
                        (hasBillableOrders || canCloseOrder)
                    "
                    class="border-t border-gray-100 bg-gray-50/50 p-4"
                >
                    <button
                        v-if="activeTab === 'cart' && hasBillableOrders"
                        type="button"
                        class="w-full rounded-xl bg-[#23AA8F] py-3 text-xs font-black text-white shadow-lg shadow-[#23AA8F]/20 transition hover:bg-[#007882]"
                        @click="checkBill"
                    >
                        CHECK BILL
                    </button>

                    <button
                        v-else-if="activeTab === 'cart' && canCloseOrder"
                        type="button"
                        class="w-full rounded-xl bg-[#2A4858] py-3 text-xs font-black text-white shadow-lg shadow-[#2A4858]/15 transition hover:bg-[#203946]"
                        @click="closeOrder"
                    >
                        CLOSE ORDER
                    </button>
                </div>

                <div
                    v-if="activeTab === 'orders'"
                    class="border-t border-gray-100 bg-gray-50/50 p-4"
                >
                    <div
                        class="mb-3 space-y-2 rounded-2xl border border-gray-100 bg-white p-3 shadow-sm"
                    >
                        <div class="flex justify-between text-[10px] font-bold">
                            <span class="text-gray-400">Sub Total</span>
                            <span class="text-[#2A4858]">{{
                                money(allOrdersSubtotal)
                            }}</span>
                        </div>

                        <div class="grid grid-cols-[auto_1fr] gap-2">
                            <div
                                class="flex rounded-lg border border-gray-100 bg-gray-50 p-0.5"
                            >
                                <button
                                    type="button"
                                    class="h-7 rounded-md px-2 text-[10px] font-black"
                                    :class="
                                        discountMode === 'percent'
                                            ? 'bg-white text-[#2A4858] shadow-sm'
                                            : 'text-gray-400'
                                    "
                                    @click="discountMode = 'percent'"
                                >
                                    %
                                </button>
                                <button
                                    type="button"
                                    class="h-7 rounded-md px-2 text-[10px] font-black"
                                    :class="
                                        discountMode === 'amount'
                                            ? 'bg-white text-[#2A4858] shadow-sm'
                                            : 'text-gray-400'
                                    "
                                    @click="discountMode = 'amount'"
                                >
                                    $
                                </button>
                            </div>

                            <input
                                v-model.number="discountValue"
                                type="number"
                                min="0"
                                placeholder="Discount"
                                class="h-8 min-w-0 rounded-lg border border-gray-100 bg-gray-50 px-2 text-right text-xs font-bold text-[#2A4858] outline-none focus:border-[#23AA8F]/60"
                            />
                        </div>

                        <div class="flex justify-between text-[10px] font-bold">
                            <span class="text-gray-400">Discount</span>
                            <span class="text-[#2A4858]">{{
                                money(discountAmount)
                            }}</span>
                        </div>
                        <div class="flex justify-between text-[10px] font-bold">
                            <span class="text-gray-400">Total</span>
                            <span class="text-[#2A4858]">{{
                                money(allOrdersTotalAfterDiscount)
                            }}</span>
                        </div>
                        <div class="flex justify-between text-[10px] font-bold">
                            <span class="text-gray-400">Tax Amount</span>
                            <span class="text-[#2A4858]">{{
                                money(allOrdersTax)
                            }}</span>
                        </div>
                        <div
                            class="flex items-center justify-between border-t border-dashed pt-1.5"
                        >
                            <span class="text-xs font-black text-[#2A4858]">
                                Final Amount
                            </span>
                            <span class="text-base font-black text-[#007882]">
                                {{ money(allOrdersFinalAmount) }}
                            </span>
                        </div>
                    </div>

                    <button
                        v-if="cart.lines.length === 0 && hasBillableOrders"
                        type="button"
                        class="w-full rounded-xl bg-[#23AA8F] py-3 text-xs font-black text-white shadow-lg shadow-[#23AA8F]/20 transition hover:bg-[#007882]"
                        @click="checkBill"
                    >
                        CHECK BILL
                    </button>

                    <button
                        v-else-if="hasInvoice && canCloseOrder"
                        type="button"
                        class="w-full rounded-xl bg-[#2A4858] py-3 text-xs font-black text-white shadow-lg shadow-[#2A4858]/15 transition hover:bg-[#203946]"
                        @click="closeOrder"
                    >
                        CLOSE ORDER
                    </button>
                </div>

                <div
                    v-if="activeTab === 'invoice' && canCloseOrder"
                    class="border-t border-gray-100 bg-gray-50/50 p-4"
                >
                    <button
                        type="button"
                        class="w-full rounded-xl bg-[#2A4858] py-3 text-xs font-black text-white shadow-lg shadow-[#2A4858]/15 transition hover:bg-[#203946]"
                        @click="closeOrder"
                    >
                        CLOSE ORDER
                    </button>
                </div>
            </aside>

            <Payment
                :open="paymentOpen"
                :subtotal="allOrdersSubtotal"
                :discount-amount="discountAmount"
                :tax-amount="allOrdersTax"
                :final-amount="allOrdersFinalAmount"
                @close="paymentOpen = false"
                @confirm="confirmPayment"
            />

            <div
                v-if="editCustomerOpen"
                class="fixed inset-0 z-[75] flex items-center justify-center bg-[#2A4858]/20 p-4 backdrop-blur-sm"
            >
                <div
                    class="w-full max-w-sm rounded-2xl bg-white p-5 shadow-2xl"
                >
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-base font-black text-[#2A4858]">
                                Edit Customer
                            </h3>
                            <p class="mt-0.5 text-xs text-gray-400">
                                Enter an existing phone number or add a new one.
                            </p>
                        </div>

                        <button
                            type="button"
                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-gray-50 text-gray-400 hover:text-[#2A4858]"
                            @click="editCustomerOpen = false"
                        >
                            <X class="h-4 w-4" />
                        </button>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <label
                                class="mb-1 block text-[10px] font-black tracking-widest text-gray-400 uppercase"
                            >
                                Phone Number
                            </label>
                            <input
                                v-model="customerForm.customer_phone"
                                type="tel"
                                class="h-10 w-full rounded-xl border border-gray-100 bg-gray-50 px-3 text-sm font-bold text-[#2A4858] outline-none focus:border-[#23AA8F]/60"
                                placeholder="Customer phone"
                            />
                        </div>

                        <div>
                            <label
                                class="mb-1 block text-[10px] font-black tracking-widest text-gray-400 uppercase"
                            >
                                Customer Name
                            </label>
                            <input
                                v-model="customerForm.customer_name"
                                type="text"
                                class="h-10 w-full rounded-xl border border-gray-100 bg-gray-50 px-3 text-sm font-bold text-[#2A4858] outline-none focus:border-[#23AA8F]/60"
                                placeholder="Customer name"
                            />
                        </div>
                    </div>

                    <div class="mt-5 flex gap-2">
                        <button
                            type="button"
                            class="h-10 flex-1 rounded-xl border border-gray-100 text-xs font-black text-[#2A4858] hover:bg-gray-50"
                            @click="editCustomerOpen = false"
                        >
                            CANCEL
                        </button>
                        <button
                            type="button"
                            class="h-10 flex-1 rounded-xl bg-[#23AA8F] text-xs font-black text-white shadow-lg shadow-[#23AA8F]/20 hover:bg-[#007882] disabled:opacity-60"
                            :disabled="customerForm.processing"
                            @click="saveCustomer"
                        >
                            SAVE
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
