<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ChevronDown,
    History,
    Maximize2,
    Minimize2,
    MonitorCog,
    Pencil,
    Power,
    Printer,
    ReceiptText,
    RotateCcw,
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
    bill_group?: string | null;
    has_invoice?: boolean;
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
        status: string;
    }[];
};

type PrintLine = {
    id: number;
    name: string;
    quantity: number;
    note?: string | null;
    status: string;
    canCancel: boolean;
    canReturn: boolean;
};

type PrintPrinterGroup = {
    jobId: number;
    jobNo: string;
    printerName: string;
    printerRole: string;
    status: string;
    isReprint: boolean;
    reprintOf?: string | null;
    printedAt?: string | null;
    lines: PrintLine[];
};

type PrintOrderData = {
    orderId: number;
    orderNo: string;
    printedAt?: string | null;
    printers: PrintPrinterGroup[];
};

type PaymentMethodOption = {
    id: number;
    code?: string | null;
    label: string;
    type: 'cash' | 'bank' | 'card';
    currency: 'USD' | 'KHR';
};

type CustomerOption = {
    id: number;
    name: string;
    phone: string;
    cardCount: number;
};

type MembershipCardOption = {
    id: number;
    customerId: number;
    cardNo: string;
    cardName?: string | null;
    balances: {
        currency: string;
        balance: number;
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
        customer_id?: number | null;
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
    exchangeRate: number;
    paymentMethods: PaymentMethodOption[];
    membershipCards?: MembershipCardOption[];
    customers?: CustomerOption[];
    historyOrders: OrderData[];
    printOrders: PrintOrderData[];
    invoices: InvoiceData[];
    filters: {
        category_id?: string | number | null;
        search?: string | null;
    };
}>();

const activeTab = ref<'cart' | 'print' | 'orders' | 'invoice'>('cart');
const search = ref(props.filters.search ?? '');
const categoryId = ref<string | number | null>(props.filters.category_id ?? '');
const pageRoot = ref<HTMLElement | null>(null);
const rightPanelWidth = ref(440);
const isResizingPanel = ref(false);
const isFullscreen = ref(false);
const instructionNotes = ref<Record<number, string>>({});
const discountMode = ref<'percent' | 'amount'>('percent');
const discountValue = ref(0);
const paymentOpen = ref(false);
const selectedBillGroup = ref<string | null>(null);
const editCustomerOpen = ref(false);
const collapsedPrintOrders = ref<Record<number, boolean>>({});
const collapsedPrintGroups = ref<Record<string, boolean>>({});
const reprintProcessingId = ref<number | null>(null);
const cancelPrintLineProcessingId = ref<number | null>(null);
const returnPrintLineProcessingId = ref<number | null>(null);
const { imageUrl, money } = usePosFormatting();

type PaymentPayload = {
    changeKhrAmount: number;
    changeUsdAmount: number;
    method: string;
    paymentMethodId?: number | null;
    membershipCardId?: number | null;
    currency: 'USD' | 'KHR';
    receivedAmount: number;
    operationStatus: 'invoice' | 'invoice_receipt_done';
};

const customerForm = useForm({
    customer_id: props.diningSession.customer_id ?? null,
    customer_phone: props.diningSession.customer_phone ?? '',
    customer_name: props.diningSession.customer_name ?? '',
});
const customerSearch = ref('');

const localLineId = ref(-1);
const draftLines = ref<CartLine[]>(cloneCartLines(props.cart.lines));
const sendKitchenProcessing = ref(false);

const cart = computed<OrderData>(() => {
    const subtotal = draftLines.value.reduce((sum, line) => {
        return sum + Number(line.subtotal ?? 0);
    }, 0);
    const taxAmount = draftLines.value.reduce((sum, line) => {
        return sum + Number(line.tax_amount ?? 0);
    }, 0);
    const totalAmount = draftLines.value.reduce((sum, line) => {
        return sum + Number(line.total_amount ?? 0);
    }, 0);

    return {
        ...props.cart,
        lines: draftLines.value,
        subtotal,
        tax_amount: taxAmount,
        total_amount: totalAmount,
    };
});

const filteredCustomerOptions = computed(() => {
    const term = customerSearch.value.trim().toLowerCase();
    const options = props.customers ?? [];

    if (!term) {
        return options.slice(0, 12);
    }

    return options
        .filter((customer) =>
            `${customer.phone} ${customer.name}`.toLowerCase().includes(term),
        )
        .slice(0, 12);
});

const cartLineCount = computed(() => cart.value.lines.length);
const posIsOpen = computed(() => Boolean(props.posSession));
const hasInvoice = computed(() => Boolean(props.diningSession.invoice_no));
const billableOrders = computed(() => {
    return props.historyOrders.filter((order) => {
        return (
            !order.invoice_no &&
            order.status !== 'draft' &&
            order.lines.some((line) => !line.has_invoice)
        );
    });
});
const hasBillableOrders = computed(() => billableOrders.value.length > 0);
const billableBillGroups = computed(() => {
    const groups = new Map<
        string,
        {
            name: string;
            orders: OrderData[];
            lines: CartLine[];
            subtotal: number;
            taxAmount: number;
            totalAmount: number;
        }
    >();

    billableOrders.value.forEach((order) => {
        order.lines
            .filter((line) => !line.has_invoice)
            .forEach((line) => {
                const name = line.bill_group || 'Bill A';

                if (!groups.has(name)) {
                    groups.set(name, {
                        name,
                        orders: [],
                        lines: [],
                        subtotal: 0,
                        taxAmount: 0,
                        totalAmount: 0,
                    });
                }

                const group = groups.get(name);

                if (!group) return;

                if (!group.orders.some((item) => item.id === order.id)) {
                    group.orders.push(order);
                }

                group.lines.push({ ...line, menu_name: line.menu_name });
                group.subtotal += Number(line.subtotal ?? 0);
                group.taxAmount += Number(line.tax_amount ?? 0);
                group.totalAmount += Number(line.total_amount ?? 0);
            });
    });

    return [...groups.values()].sort((a, b) => a.name.localeCompare(b.name));
});
const selectedBill = computed(() => {
    return (
        billableBillGroups.value.find(
            (group) => group.name === selectedBillGroup.value,
        ) ??
        billableBillGroups.value[0] ??
        null
    );
});
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
    return new Map(cart.value.lines.map((line) => [line.menu_id, line]));
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
        count: props.printOrders.reduce(
            (total, order) => total + order.printers.length,
            0,
        ),
        icon: Printer,
        label: 'Print',
        value: 'print',
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
    return selectedBill.value?.subtotal ?? 0;
});

const allOrdersTax = computed(() => {
    return selectedBill.value?.taxAmount ?? 0;
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

const rightPanelStyle = computed(() => ({
    '--right-panel-width': `${rightPanelWidth.value}px`,
}));

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

function cloneCartLines(lines: CartLine[]) {
    return lines.map((line) => ({ ...line }));
}

function calculateDraftLine(line: CartLine) {
    line.subtotal = Number(line.qty) * Number(line.unit_price);
    line.tax_amount = line.subtotal * 0.1;
    line.total_amount = line.subtotal + line.tax_amount;

    return line;
}

function makeDraftLine(menu: MenuItem, note: string | null): CartLine {
    return calculateDraftLine({
        id: localLineId.value--,
        menu_id: menu.id,
        menu_name: menu.name,
        note,
        qty: 1,
        status: 'ordered',
        subtotal: 0,
        tax_amount: 0,
        total_amount: 0,
        unit_price: Number(menu.unit_price ?? 0),
    });
}

function replaceDraftLine(line: CartLine, changes: Partial<CartLine>) {
    draftLines.value = draftLines.value.map((draftLine) => {
        if (draftLine.id !== line.id) {
            return draftLine;
        }

        return calculateDraftLine({
            ...draftLine,
            ...changes,
        });
    });
}

function addToDish(menu: MenuItem) {
    const note = (instructionNotes.value[menu.id] || '').trim() || null;
    const line = cartLineForMenu(menu);

    if (line) {
        replaceDraftLine(line, {
            note: note ?? line.note,
            qty: line.qty + 1,
        });

        return;
    }

    draftLines.value = [...draftLines.value, makeDraftLine(menu, note)];
}

function increaseQty(line: CartLine) {
    updateQty(line, line.qty + 1);
}

function decreaseQty(line: CartLine) {
    if (line.qty <= 1) return;
    updateQty(line, line.qty - 1);
}

function updateQty(line: CartLine, qty: number) {
    replaceDraftLine(line, {
        qty: Math.max(1, Number(qty || 1)),
    });
}

function removeLine(line: CartLine) {
    draftLines.value = draftLines.value.filter(
        (draftLine) => draftLine.id !== line.id,
    );
}

function clearCart() {
    if (cart.value.lines.length === 0) return;

    draftLines.value = [];
}

function sendToKitchen() {
    if (cart.value.lines.length === 0 || sendKitchenProcessing.value) return;

    sendKitchenProcessing.value = true;

    router.post(
        `/orders/${props.diningSession.id}/send-kitchen`,
        {
            lines: cart.value.lines.map((line) => ({
                menu_id: line.menu_id,
                note: line.note || null,
                qty: line.qty,
            })),
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                draftLines.value = [];
                instructionNotes.value = {};
                activeTab.value = 'print';
            },
            onFinish: () => {
                sendKitchenProcessing.value = false;
            },
        },
    );
}

function checkBill(billGroup?: string) {
    if (cartLineCount.value > 0 || !hasBillableOrders.value) return;

    selectedBillGroup.value = billGroup ?? selectedBill.value?.name ?? null;
    paymentOpen.value = true;
}

function manageOrders() {
    router.get(`/orders/${props.diningSession.id}/manage`);
}

function openPosSessionPage() {
    router.visit('/pos-sessions');
}

function confirmPayment(payload: PaymentPayload) {
    router.post(
        `/orders/${props.diningSession.id}/settle`,
        {
            method: payload.method,
            payment_method_id: payload.paymentMethodId,
            membership_card_id: payload.membershipCardId,
            currency: payload.currency,
            received_amount: payload.receivedAmount,
            operation_status: payload.operationStatus,
            discount_amount: discountAmount.value,
            change_usd_amount: payload.changeUsdAmount,
            change_khr_amount: payload.changeKhrAmount,
            bill_group: selectedBill.value?.name ?? selectedBillGroup.value,
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

function openPrintPreview(url: string) {
    window.open(url, '_blank', 'noopener,noreferrer');
}

function previewCurrentInvoice() {
    const params = new URLSearchParams({
        discount_amount: String(discountAmount.value),
    });
    const billGroup = selectedBill.value?.name ?? selectedBillGroup.value;

    if (billGroup) {
        params.set('bill_group', billGroup);
    }

    openPrintPreview(
        `/orders/${props.diningSession.id}/print/current-invoice?${params}`,
    );
}

function previewPrintJob(job: PrintPrinterGroup) {
    openPrintPreview(
        `/orders/${props.diningSession.id}/print-jobs/${job.jobId}/preview`,
    );
}

function previewInvoiceDocument(
    invoice: InvoiceData,
    documentType: 'invoice' | 'receipt',
) {
    openPrintPreview(
        `/orders/${props.diningSession.id}/invoices/${invoice.id}/print/${documentType}`,
    );
}

function openEditCustomer() {
    customerForm.customer_id = props.diningSession.customer_id ?? null;
    customerForm.customer_phone = props.diningSession.customer_phone ?? '';
    customerForm.customer_name = props.diningSession.customer_name ?? '';
    customerSearch.value = '';
    editCustomerOpen.value = true;
}

function selectCustomerOption(customer: CustomerOption) {
    customerForm.customer_id = customer.id;
    customerForm.customer_phone = customer.phone;
    customerForm.customer_name = customer.name;
    customerSearch.value = `${customer.phone} ${customer.name}`;
}

function useTypedCustomerPhone() {
    const exact = (props.customers ?? []).find(
        (customer) => customer.phone === customerForm.customer_phone,
    );

    if (exact) {
        selectCustomerOption(exact);
        return;
    }

    customerForm.customer_id = null;
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

function isPrintOrderCollapsed(order: PrintOrderData) {
    return collapsedPrintOrders.value[order.orderId] ?? false;
}

function togglePrintOrder(order: PrintOrderData) {
    collapsedPrintOrders.value = {
        ...collapsedPrintOrders.value,
        [order.orderId]: !isPrintOrderCollapsed(order),
    };
}

function printGroupKey(order: PrintOrderData, printer: PrintPrinterGroup) {
    return `${order.orderId}-${printer.jobId}`;
}

function isPrintGroupCollapsed(
    order: PrintOrderData,
    printer: PrintPrinterGroup,
) {
    return collapsedPrintGroups.value[printGroupKey(order, printer)] ?? false;
}

function togglePrintGroup(order: PrintOrderData, printer: PrintPrinterGroup) {
    const key = printGroupKey(order, printer);
    collapsedPrintGroups.value = {
        ...collapsedPrintGroups.value,
        [key]: !isPrintGroupCollapsed(order, printer),
    };
}

function reprintJob(job: PrintPrinterGroup) {
    if (reprintProcessingId.value) {
        return;
    }

    reprintProcessingId.value = job.jobId;

    router.post(
        `/orders/${props.diningSession.id}/print-jobs/${job.jobId}/reprint`,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                reprintProcessingId.value = null;
            },
        },
    );
}

function cancelPrintedLine(line: PrintLine) {
    if (
        cancelPrintLineProcessingId.value ||
        !line.canCancel ||
        hasInvoice.value
    ) {
        return;
    }

    cancelPrintLineProcessingId.value = line.id;

    router.patch(
        `/orders/${props.diningSession.id}/print-lines/${line.id}/cancel`,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                cancelPrintLineProcessingId.value = null;
            },
        },
    );
}

function returnPrintedLine(line: PrintLine) {
    if (
        returnPrintLineProcessingId.value ||
        !line.canReturn ||
        hasInvoice.value
    ) {
        return;
    }

    const rawQuantity = window.prompt(
        `Return quantity for ${line.name}`,
        String(line.quantity),
    );

    if (rawQuantity === null) {
        return;
    }

    const quantity = Math.min(
        line.quantity,
        Number(rawQuantity || line.quantity),
    );

    if (!Number.isFinite(quantity) || quantity <= 0) {
        return;
    }

    returnPrintLineProcessingId.value = line.id;

    router.patch(
        `/orders/${props.diningSession.id}/print-lines/${line.id}/return`,
        { quantity },
        {
            preserveScroll: true,
            onFinish: () => {
                returnPrintLineProcessingId.value = null;
            },
        },
    );
}

function printLineStatusLabel(status: string) {
    const labels: Record<string, string> = {
        cancelled: 'Cancelled',
        returned: 'Returned',
    };

    return labels[status] ?? status.replaceAll('_', ' ').toUpperCase();
}

function isNoChargeStatus(status: string) {
    return ['cancelled', 'returned'].includes(status);
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

function startPanelResize(event: MouseEvent) {
    isResizingPanel.value = true;
    document.body.style.cursor = 'col-resize';
    document.body.style.userSelect = 'none';
    resizePanel(event);
    window.addEventListener('mousemove', resizePanel);
    window.addEventListener('mouseup', stopPanelResize);
}

function resizePanel(event: MouseEvent) {
    if (!isResizingPanel.value && event.type !== 'mousedown') {
        return;
    }

    const viewportWidth = window.innerWidth;
    const minWidth = 400;
    const maxWidth = Math.min(620, Math.max(minWidth, viewportWidth - 520));
    const nextWidth = viewportWidth - event.clientX;
    rightPanelWidth.value = Math.min(maxWidth, Math.max(minWidth, nextWidth));
}

function stopPanelResize() {
    isResizingPanel.value = false;
    document.body.style.cursor = '';
    document.body.style.userSelect = '';
    window.removeEventListener('mousemove', resizePanel);
    window.removeEventListener('mouseup', stopPanelResize);
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

    replaceDraftLine(line, {
        note: cleanNote || null,
    });
}

function editLineInstruction(line: CartLine) {
    const note = window.prompt('Additional instruction', line.note ?? '');

    if (note === null) return;

    const cleanNote = note.trim();
    instructionNotes.value = {
        ...instructionNotes.value,
        [line.menu_id]: cleanNote,
    };

    replaceDraftLine(line, {
        note: cleanNote || null,
    });
}

onMounted(() => {
    document.addEventListener('fullscreenchange', syncFullscreenState);
});

onBeforeUnmount(() => {
    document.removeEventListener('fullscreenchange', syncFullscreenState);
    stopPanelResize();
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

                        <button
                            type="button"
                            class="hidden h-9 shrink-0 items-center gap-2 rounded-xl px-3 text-xs font-black text-white shadow-lg transition sm:inline-flex"
                            :class="
                                posIsOpen
                                    ? 'bg-red-600 shadow-red-600/20 hover:bg-red-700'
                                    : 'bg-[#23AA8F] shadow-[#23AA8F]/25 hover:bg-[#007882]'
                            "
                            @click="openPosSessionPage"
                        >
                            <Power v-if="posIsOpen" class="h-4 w-4" />
                            <MonitorCog v-else class="h-4 w-4" />
                            {{ posIsOpen ? 'CLOSE NOW' : 'OPEN NOW' }}
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
                    class="min-h-0 flex-1 [scrollbar-width:thin] [scrollbar-color:#cbd5e1_transparent] overflow-y-auto bg-gray-50/30 p-4"
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
                            :processing="sendKitchenProcessing"
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
                class="relative flex h-[48vh] min-h-0 w-full flex-col bg-white lg:h-auto lg:w-[var(--right-panel-width)] lg:max-w-[620px] lg:min-w-[400px]"
                :style="rightPanelStyle"
            >
                <button
                    type="button"
                    class="absolute top-0 bottom-0 left-0 z-10 hidden w-2 -translate-x-1/2 cursor-col-resize items-center justify-center lg:flex"
                    title="Resize order panel"
                    @mousedown="startPanelResize"
                >
                    <span
                        class="h-16 w-1 rounded-full bg-gray-200 transition hover:bg-[#23AA8F]"
                        :class="isResizingPanel ? 'bg-[#23AA8F]' : ''"
                    />
                </button>

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
                    class="min-h-0 flex-1 [scrollbar-width:thin] [scrollbar-color:#cbd5e1_transparent] overflow-y-auto px-4"
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

                    <div v-if="activeTab === 'print'" class="space-y-3 py-2">
                        <article
                            v-for="order in printOrders"
                            :key="order.orderId"
                            class="rounded-xl border border-gray-100 bg-white p-3 shadow-sm"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <button
                                    type="button"
                                    class="flex min-w-0 flex-1 items-start gap-2 text-left"
                                    @click="togglePrintOrder(order)"
                                >
                                    <ChevronDown
                                        class="mt-0.5 h-3.5 w-3.5 shrink-0 text-gray-400 transition"
                                        :class="
                                            isPrintOrderCollapsed(order)
                                                ? '-rotate-90'
                                                : ''
                                        "
                                    />
                                    <div class="min-w-0">
                                        <h3
                                            class="truncate text-xs font-black text-[#2A4858]"
                                        >
                                            {{ order.orderNo }}
                                        </h3>
                                        <p
                                            class="mt-0.5 text-[10px] text-gray-400"
                                        >
                                            {{ order.printedAt }}
                                        </p>
                                    </div>
                                </button>

                                <span
                                    class="rounded-full bg-[#23AA8F]/10 px-2 py-1 text-[9px] font-bold text-[#007882] uppercase"
                                >
                                    {{ order.printers.length }} Printer(s)
                                </span>
                            </div>

                            <div
                                v-if="!isPrintOrderCollapsed(order)"
                                class="mt-3 space-y-2"
                            >
                                <section
                                    v-for="printer in order.printers"
                                    :key="printer.jobId"
                                    class="rounded-xl border border-gray-100 bg-gray-50/70 p-2"
                                >
                                    <div
                                        class="flex items-center justify-between gap-2"
                                    >
                                        <button
                                            type="button"
                                            class="flex min-w-0 flex-1 items-center gap-2 text-left"
                                            @click="
                                                togglePrintGroup(order, printer)
                                            "
                                        >
                                            <ChevronDown
                                                class="h-3.5 w-3.5 shrink-0 text-gray-400 transition"
                                                :class="
                                                    isPrintGroupCollapsed(
                                                        order,
                                                        printer,
                                                    )
                                                        ? '-rotate-90'
                                                        : ''
                                                "
                                            />
                                            <span
                                                class="min-w-0 truncate text-xs font-black text-[#2A4858]"
                                            >
                                                {{ printer.printerName }}
                                            </span>
                                        </button>

                                        <div class="flex shrink-0 gap-1">
                                            <button
                                                type="button"
                                                class="h-7 rounded-lg border border-[#23AA8F]/30 bg-white px-2 text-[9px] font-black text-[#007882] transition hover:bg-[#23AA8F]/10"
                                                @click="
                                                    previewPrintJob(printer)
                                                "
                                            >
                                                Preview
                                            </button>
                                            <button
                                                type="button"
                                                class="h-7 rounded-lg bg-[#2A4858] px-2 text-[9px] font-black text-white transition hover:bg-[#203946] disabled:opacity-60"
                                                :disabled="
                                                    reprintProcessingId ===
                                                    printer.jobId
                                                "
                                                @click="reprintJob(printer)"
                                            >
                                                Re-print
                                            </button>
                                        </div>
                                    </div>

                                    <div
                                        class="mt-1 flex flex-wrap items-center gap-1 pl-5"
                                    >
                                        <span
                                            class="rounded-full bg-white px-2 py-0.5 text-[9px] font-bold text-gray-400 uppercase"
                                        >
                                            {{ printer.printerRole }}
                                        </span>
                                        <span
                                            v-if="printer.isReprint"
                                            class="rounded-full bg-orange-50 px-2 py-0.5 text-[9px] font-black text-orange-500 uppercase"
                                        >
                                            Re-print
                                            {{
                                                printer.reprintOf
                                                    ? `of ${printer.reprintOf}`
                                                    : ''
                                            }}
                                        </span>
                                        <span
                                            class="rounded-full bg-white px-2 py-0.5 text-[9px] font-bold text-gray-400 uppercase"
                                        >
                                            {{ printer.status }}
                                        </span>
                                    </div>

                                    <div
                                        v-if="
                                            !isPrintGroupCollapsed(
                                                order,
                                                printer,
                                            )
                                        "
                                        class="mt-2 space-y-1.5 border-t border-dashed border-gray-200 pt-2"
                                    >
                                        <div
                                            v-for="line in printer.lines"
                                            :key="`${printer.jobId}-${line.id}-${line.name}`"
                                            class="space-y-0.5"
                                        >
                                            <div
                                                class="flex items-center justify-between gap-3 text-[11px]"
                                            >
                                                <span
                                                    class="min-w-0 truncate text-gray-500"
                                                    :class="
                                                        isNoChargeStatus(
                                                            line.status,
                                                        )
                                                            ? 'text-gray-400 line-through decoration-red-400 decoration-2'
                                                            : ''
                                                    "
                                                >
                                                    {{ line.name }} x
                                                    {{ line.quantity }}
                                                </span>

                                                <div
                                                    class="flex shrink-0 items-center gap-1"
                                                >
                                                    <span
                                                        v-if="
                                                            isNoChargeStatus(
                                                                line.status,
                                                            )
                                                        "
                                                        class="rounded-full px-1.5 py-0.5 text-[8px] font-black uppercase"
                                                        :class="
                                                            line.status ===
                                                            'returned'
                                                                ? 'bg-orange-50 text-orange-500'
                                                                : 'bg-red-50 text-red-500'
                                                        "
                                                    >
                                                        {{
                                                            printLineStatusLabel(
                                                                line.status,
                                                            )
                                                        }}
                                                    </span>
                                                    <button
                                                        type="button"
                                                        class="flex h-6 w-6 items-center justify-center rounded-md text-red-400 transition hover:bg-red-50 hover:text-red-600 disabled:cursor-not-allowed disabled:opacity-30"
                                                        title="Cancel printed item"
                                                        :disabled="
                                                            !line.canCancel ||
                                                            hasInvoice ||
                                                            cancelPrintLineProcessingId ===
                                                                line.id
                                                        "
                                                        @click="
                                                            cancelPrintedLine(
                                                                line,
                                                            )
                                                        "
                                                    >
                                                        <X
                                                            class="h-3.5 w-3.5"
                                                        />
                                                    </button>
                                                    <button
                                                        type="button"
                                                        class="flex h-6 w-6 items-center justify-center rounded-md text-orange-400 transition hover:bg-orange-50 hover:text-orange-600 disabled:cursor-not-allowed disabled:opacity-30"
                                                        title="Return printed item"
                                                        :disabled="
                                                            !line.canReturn ||
                                                            hasInvoice ||
                                                            returnPrintLineProcessingId ===
                                                                line.id
                                                        "
                                                        @click="
                                                            returnPrintedLine(
                                                                line,
                                                            )
                                                        "
                                                    >
                                                        <RotateCcw
                                                            class="h-3.5 w-3.5"
                                                        />
                                                    </button>
                                                </div>
                                            </div>
                                            <p
                                                v-if="line.note"
                                                class="truncate pl-2 text-[10px] font-medium text-[#007882]"
                                                :class="
                                                    isNoChargeStatus(
                                                        line.status,
                                                    )
                                                        ? 'text-gray-400 line-through decoration-red-400'
                                                        : ''
                                                "
                                            >
                                                {{ line.note }}
                                            </p>
                                        </div>
                                    </div>
                                </section>
                            </div>
                        </article>

                        <div
                            v-if="printOrders.length === 0"
                            class="rounded-xl border border-dashed border-gray-200 p-8 text-center text-sm text-gray-400"
                        >
                            No print jobs for this seat yet.
                        </div>
                    </div>

                    <div v-if="activeTab === 'orders'" class="space-y-3 py-2">
                        <article
                            v-for="bill in billableBillGroups"
                            :key="bill.name"
                            class="rounded-xl border border-gray-100 bg-white p-3 shadow-sm"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3
                                        class="truncate text-xs font-black text-[#2A4858]"
                                    >
                                        {{ bill.name }}
                                    </h3>
                                    <p class="mt-0.5 text-[10px] text-gray-400">
                                        {{
                                            bill.orders
                                                .map((order) => order.order_no)
                                                .join(', ')
                                        }}
                                    </p>
                                </div>

                                <span
                                    class="rounded-full bg-[#23AA8F]/10 px-2 py-1 text-[9px] font-bold text-[#007882] uppercase"
                                >
                                    Open Bill
                                </span>
                            </div>

                            <div class="mt-3 space-y-1.5">
                                <div
                                    v-for="line in bill.lines"
                                    :key="line.id"
                                    class="space-y-0.5"
                                >
                                    <div
                                        class="flex items-center justify-between gap-3 text-[11px]"
                                    >
                                        <div
                                            class="flex min-w-0 items-center gap-1.5"
                                        >
                                            <span
                                                class="min-w-0 truncate text-gray-500"
                                                :class="
                                                    isNoChargeStatus(
                                                        line.status,
                                                    )
                                                        ? 'text-gray-400 line-through decoration-red-400'
                                                        : ''
                                                "
                                            >
                                                {{ line.menu_name }} x
                                                {{ line.qty }}
                                            </span>
                                            <span
                                                v-if="
                                                    isNoChargeStatus(
                                                        line.status,
                                                    )
                                                "
                                                class="rounded-full px-1.5 py-0.5 text-[8px] font-black uppercase"
                                                :class="
                                                    line.status === 'returned'
                                                        ? 'bg-orange-50 text-orange-500'
                                                        : 'bg-red-50 text-red-500'
                                                "
                                            >
                                                {{
                                                    printLineStatusLabel(
                                                        line.status,
                                                    )
                                                }}
                                            </span>
                                        </div>
                                        <span class="font-bold text-[#2A4858]">
                                            {{ money(line.total_amount) }}
                                        </span>
                                    </div>
                                    <p
                                        v-if="line.note"
                                        class="truncate pl-2 text-[10px] font-medium text-[#007882]"
                                        :class="
                                            isNoChargeStatus(line.status)
                                                ? 'text-gray-400 line-through decoration-red-400'
                                                : ''
                                        "
                                    >
                                        {{ line.note }}
                                    </p>
                                </div>
                            </div>

                            <div
                                class="mt-3 flex items-center justify-between border-t border-dashed pt-2"
                            >
                                <span class="text-xs font-black text-[#007882]">
                                    {{ money(bill.totalAmount) }}
                                </span>
                                <button
                                    type="button"
                                    class="rounded-lg bg-[#23AA8F] px-3 py-2 text-[10px] font-black text-white transition hover:bg-[#007882] disabled:cursor-not-allowed disabled:bg-gray-300"
                                    :disabled="cart.lines.length > 0"
                                    @click="checkBill(bill.name)"
                                >
                                    PAY THIS BILL
                                </button>
                            </div>
                        </article>

                        <details
                            v-if="historyOrders.length > 0"
                            class="rounded-xl border border-gray-100 bg-white p-3 shadow-sm"
                        >
                            <summary
                                class="cursor-pointer text-xs font-black text-[#2A4858]"
                            >
                                Order History
                            </summary>

                            <div class="mt-3 space-y-2">
                                <article
                                    v-for="order in historyOrders"
                                    :key="order.id"
                                    class="rounded-lg bg-gray-50 p-2"
                                >
                                    <div
                                        class="flex items-center justify-between gap-3"
                                    >
                                        <span
                                            class="truncate text-[11px] font-black text-[#2A4858]"
                                        >
                                            {{ order.order_no }}
                                        </span>
                                        <span
                                            class="rounded-full bg-white px-2 py-1 text-[8px] font-black text-gray-500 uppercase"
                                        >
                                            {{ orderStatusLabel(order) }}
                                        </span>
                                    </div>
                                </article>
                            </div>
                        </details>

                        <div
                            v-if="historyOrders.length === 0"
                            class="rounded-xl border border-dashed border-gray-200 p-8 text-center text-sm text-gray-400"
                        >
                            No previous order history for this seat.
                        </div>
                        <div
                            v-else-if="billableBillGroups.length === 0"
                            class="rounded-xl border border-dashed border-gray-200 p-8 text-center text-sm text-gray-400"
                        >
                            All bills for this seat have been checked.
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
                                            :class="
                                                isNoChargeStatus(line.status)
                                                    ? 'text-gray-400 line-through decoration-red-400'
                                                    : ''
                                            "
                                        >
                                            {{ line.menu_name }} x
                                            {{ line.qty }}
                                        </span>
                                        <span
                                            v-if="isNoChargeStatus(line.status)"
                                            class="shrink-0 rounded-full px-1.5 py-0.5 text-[8px] font-black uppercase"
                                            :class="
                                                line.status === 'returned'
                                                    ? 'bg-orange-50 text-orange-500'
                                                    : 'bg-red-50 text-red-500'
                                            "
                                        >
                                            {{
                                                printLineStatusLabel(
                                                    line.status,
                                                )
                                            }}
                                        </span>
                                        <span class="font-bold text-[#2A4858]">
                                            {{ money(line.total_amount) }}
                                        </span>
                                    </div>
                                    <p
                                        v-if="line.note"
                                        class="truncate pl-2 text-[10px] font-medium text-[#007882]"
                                        :class="
                                            isNoChargeStatus(line.status)
                                                ? 'text-gray-400 line-through decoration-red-400'
                                                : ''
                                        "
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

                            <div class="mt-3 grid grid-cols-2 gap-2">
                                <button
                                    type="button"
                                    class="rounded-xl border border-[#23AA8F]/30 bg-white py-2 text-[10px] font-black text-[#007882] transition hover:bg-[#23AA8F]/10"
                                    @click="
                                        previewInvoiceDocument(
                                            invoice,
                                            'invoice',
                                        )
                                    "
                                >
                                    Print Invoice
                                </button>
                                <button
                                    v-if="invoice.status === 'paid'"
                                    type="button"
                                    class="rounded-xl bg-[#2A4858] py-2 text-[10px] font-black text-white transition hover:bg-[#203946]"
                                    @click="
                                        previewInvoiceDocument(
                                            invoice,
                                            'receipt',
                                        )
                                    "
                                >
                                    Print Receipt
                                </button>
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

                <template v-if="activeTab === 'cart'">
                    <PosTotalsPanel
                        :subtotal="money(cart.subtotal)"
                        action-label="CONFIRM ORDER"
                        :show-action="true"
                        :processing="sendKitchenProcessing"
                        :disabled="cart.lines.length === 0"
                        variant="cart"
                        @action="sendToKitchen"
                    />
                </template>

                <div
                    v-if="activeTab === 'orders'"
                    class="border-t border-gray-100 bg-gray-50/50 p-4"
                >
                    <div
                        class="mb-3 space-y-2 rounded-2xl border border-gray-100 bg-white p-3 shadow-sm"
                    >
                        <div class="flex justify-between text-[10px] font-bold">
                            <span class="text-gray-400">
                                Sub Total
                                <template v-if="selectedBill">
                                    ({{ selectedBill.name }})
                                </template>
                            </span>
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

                    <div class="grid grid-cols-2 gap-2">
                        <button
                            type="button"
                            class="rounded-xl bg-[#2A4858] py-3 text-xs font-black text-white shadow-lg shadow-[#2A4858]/15 transition hover:bg-[#203946]"
                            @click="manageOrders"
                        >
                            MANAGE ORDERS
                        </button>

                        <button
                            type="button"
                            class="rounded-xl bg-[#23AA8F] py-3 text-xs font-black text-white shadow-lg shadow-[#23AA8F]/20 transition hover:bg-[#007882] disabled:cursor-not-allowed disabled:bg-gray-300 disabled:shadow-none"
                            :disabled="cart.lines.length > 0 || !selectedBill"
                            @click="checkBill(selectedBill?.name)"
                        >
                            CHECK BILL
                        </button>
                    </div>

                    <button
                        v-if="hasInvoice && canCloseOrder"
                        type="button"
                        class="mt-2 w-full rounded-xl bg-[#2A4858] py-3 text-xs font-black text-white shadow-lg shadow-[#2A4858]/15 transition hover:bg-[#203946]"
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
                :bill-name="selectedBill?.name ?? selectedBillGroup"
                :exchange-rate="exchangeRate"
                :allow-pay-later="true"
                :payment-methods="paymentMethods"
                :membership-cards="membershipCards ?? []"
                @close="paymentOpen = false"
                @confirm="confirmPayment"
                @print-invoice="previewCurrentInvoice"
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
                                Search Customer Phone
                            </label>
                            <input
                                v-model="customerSearch"
                                type="search"
                                class="h-10 w-full rounded-xl border border-gray-100 bg-gray-50 px-3 text-sm font-bold text-[#2A4858] outline-none focus:border-[#23AA8F]/60"
                                placeholder="Search by phone or name"
                            />
                            <div
                                class="mt-2 max-h-44 overflow-y-auto rounded-xl border border-gray-100 bg-white shadow-sm"
                            >
                                <button
                                    v-for="customer in filteredCustomerOptions"
                                    :key="customer.id"
                                    type="button"
                                    class="flex w-full items-center justify-between gap-3 border-b border-gray-50 px-3 py-2 text-left last:border-b-0 hover:bg-[#23AA8F]/10"
                                    @click="selectCustomerOption(customer)"
                                >
                                    <span class="min-w-0">
                                        <span
                                            class="block truncate text-xs font-black text-[#2A4858]"
                                        >
                                            {{ customer.phone }}
                                        </span>
                                        <span
                                            class="block truncate text-[11px] text-gray-500"
                                        >
                                            {{ customer.name }}
                                        </span>
                                    </span>
                                    <span
                                        v-if="customer.cardCount > 0"
                                        class="shrink-0 rounded bg-[#23AA8F]/10 px-2 py-0.5 text-[10px] font-black text-[#007882]"
                                    >
                                        {{ customer.cardCount }} card
                                    </span>
                                </button>
                                <p
                                    v-if="filteredCustomerOptions.length === 0"
                                    class="px-3 py-4 text-center text-xs text-gray-400"
                                >
                                    No matching customer. Enter phone and name
                                    below to create one.
                                </p>
                            </div>
                        </div>

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
                                @input="useTypedCustomerPhone"
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
