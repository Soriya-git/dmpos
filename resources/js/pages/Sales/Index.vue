<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Calendar,
    ChevronDown,
    CreditCard,
    Eye,
    Filter,
    MoreVertical,
    Printer,
    ReceiptText,
    RotateCcw,
    Search,
    ShieldAlert,
    X,
    XCircle,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import TablePagination from '@/components/TablePagination.vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { usePagination } from '@/composables/usePagination';
import { usePosFormatting } from '@/composables/usePosFormatting';
import AppLayout from '@/layouts/AppLayout.vue';
import Payment from '@/pages/Seats/Payment.vue';
import InvoiceView from '@/pages/Sales/view.vue';

type InvoiceStatus =
    | 'draft'
    | 'issued'
    | 'partially_paid'
    | 'paid'
    | 'pay_later';

type SaleInvoice = {
    id: number;
    customer_id?: number | null;
    dining_session_id: number;
    invoice_no: string;
    status: InvoiceStatus;
    date?: string | null;
    display_date?: string | null;
    pos_name?: string | null;
    customer_name?: string | null;
    customer_phone?: string | null;
    seat_name?: string | null;
    subtotal: number;
    discount_amount: number;
    tax_amount: number;
    grand_total: number;
    paid_amount: number;
    balance_amount: number;
    exchange_rate?: number | null;
    order_created_by: string[];
    invoice_created_by?: string | null;
    receipt_created_by?: string | null;
    payments: InvoicePayment[];
    lines: SaleInvoiceLine[];
};

type InvoicePayment = {
    id: number;
    payment_no: string;
    status: 'draft' | 'paid' | 'partial' | 'pay_later' | 'cancelled';
    method?: string | null;
    currency: 'USD' | 'KHR';
    amount_paid: number;
    received_amount: number;
    amount_usd_equivalent: number;
    change_usd_amount: number;
    change_khr_amount: number;
    paid_at?: string | null;
    received_by?: string | null;
};

type SaleInvoiceLine = {
    id: number;
    menu_name: string;
    quantity: number;
    unit_price: number;
    discount_amount: number;
    tax_amount: number;
    line_subtotal: number;
    line_total: number;
    note?: string | null;
};

type PaymentMethodOption = {
    id: number;
    code?: string | null;
    label: string;
    type: 'cash' | 'bank' | 'card';
    currency: 'USD' | 'KHR';
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

type PaymentSummary = {
    sales_usd: number;
    sales_khr: number;
    cash_usd: number;
    cash_khr: number;
    ebanking_usd: number;
    ebanking_khr: number;
    pay_later_usd: number;
    pay_later_khr: number;
    expected_cash_usd: number;
    expected_cash_khr: number;
};

const props = defineProps<{
    posSession?: {
        id: number;
        session_no: string;
        opening_cash_usd: number;
        opening_cash_khr: number;
    };
    invoices: SaleInvoice[];
    paymentMethods: PaymentMethodOption[];
    membershipCards?: Record<number, MembershipCardOption[]>;
    paymentSummary: PaymentSummary;
    filters: {
        start_date?: string | null;
        end_date?: string | null;
        search?: string | null;
    };
}>();

const startDate = ref(props.filters.start_date ?? '');
const endDate = ref(props.filters.end_date ?? '');
const search = ref(props.filters.search ?? '');
const collapsedGroups = ref<Record<'unpaid' | 'settled', boolean>>({
    unpaid: false,
    settled: false,
});
const paymentOpen = ref(false);
const selectedInvoice = ref<SaleInvoice | null>(null);
const detailInvoice = ref<SaleInvoice | null>(null);
const receiptToCancel = ref<InvoicePayment | null>(null);
const invoiceReceiptsToCancel = ref<SaleInvoice | null>(null);
const { money } = usePosFormatting();

const {
    currentPage: invoicesPage,
    totalRows: invoicesTotalRows,
    totalPages: invoicesTotalPages,
    pageStart: invoicesPageStart,
    pageEnd: invoicesPageEnd,
    paginatedRows: paginatedInvoices,
    goToPage: goToInvoicesPage,
    pageSize: invoicesPageSize,
    setRowsPerPage: setInvoicesRowsPerPage,
} = usePagination(() => props.invoices, 10);

const unpaidInvoices = computed(() => {
    return paginatedInvoices.value.filter(
        (invoice) => invoice.status !== 'paid',
    );
});

const settledInvoices = computed(() => {
    return paginatedInvoices.value.filter(
        (invoice) => invoice.status === 'paid',
    );
});

const groupedInvoices = computed(() => [
    {
        key: 'unpaid' as const,
        label: 'Unpaid',
        invoices: unpaidInvoices.value,
        badgeClass: 'bg-orange-100 text-orange-600',
        rowClass: 'border-orange-100/50 bg-orange-50/40',
        totalClass: 'text-orange-600',
        iconClass: 'text-orange-500',
    },
    {
        key: 'settled' as const,
        label: 'Settled',
        invoices: settledInvoices.value,
        badgeClass: 'bg-green-100 text-green-600',
        rowClass: 'border-green-100/50 bg-green-50/40',
        totalClass: 'text-green-600',
        iconClass: 'text-green-500',
    },
]);

const hasInvoices = computed(() => props.invoices.length > 0);

function groupTotal(invoices: SaleInvoice[]) {
    return invoices.reduce((total, invoice) => {
        return (
            total + Number(invoice.balance_amount || invoice.grand_total || 0)
        );
    }, 0);
}

function applyFilters() {
    router.get(
        '/sales',
        {
            start_date: startDate.value || undefined,
            end_date: endDate.value || undefined,
            search: search.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

function resetFilters() {
    startDate.value = '';
    endDate.value = '';
    search.value = '';

    router.get(
        '/sales',
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

function goBack() {
    window.history.back();
}

function toggleGroup(key: 'unpaid' | 'settled') {
    collapsedGroups.value = {
        ...collapsedGroups.value,
        [key]: !collapsedGroups.value[key],
    };
}

function openPayment(invoice: SaleInvoice) {
    selectedInvoice.value = invoice;
    paymentOpen.value = true;
}

function closePayment() {
    paymentOpen.value = false;
    selectedInvoice.value = null;
}

function showInvoiceDetail(invoice: SaleInvoice) {
    detailInvoice.value = invoice;
}

function closeInvoiceDetail() {
    detailInvoice.value = null;
}

function printInvoice(_invoice: SaleInvoice) {
    // Printing will be wired in a later pass.
}

function printReceipt(invoice: SaleInvoice) {
    if (!invoice.payments.some((payment) => payment.status !== 'cancelled')) {
        return;
    }

    window.open(
        `/orders/${invoice.dining_session_id}/invoices/${invoice.id}/print/receipt`,
        '_blank',
    );
}

function latestActivePayment(invoice: SaleInvoice) {
    return invoice.payments.find((payment) => payment.status !== 'cancelled');
}

function cancelInvoice(invoice: SaleInvoice) {
    if (!window.confirm(`Cancel invoice ${invoice.invoice_no}?`)) return;

    router.patch(
        `/sales/invoices/${invoice.id}/cancel`,
        {},
        { preserveScroll: true },
    );
}

function cancelReceipt(payment: InvoicePayment) {
    receiptToCancel.value = payment;
}

function cancelInvoiceReceipts(invoice: SaleInvoice) {
    invoiceReceiptsToCancel.value = invoice;
}

function closeCancelReceiptConfirm() {
    receiptToCancel.value = null;
    invoiceReceiptsToCancel.value = null;
}

function confirmCancelReceipt() {
    if (invoiceReceiptsToCancel.value) {
        router.patch(
            `/sales/invoices/${invoiceReceiptsToCancel.value.id}/receipts/cancel`,
            {},
            {
                preserveScroll: true,
                onSuccess: closeCancelReceiptConfirm,
            },
        );

        return;
    }

    if (!receiptToCancel.value) return;

    router.patch(
        `/sales/payments/${receiptToCancel.value.id}/cancel`,
        {},
        {
            preserveScroll: true,
            onSuccess: closeCancelReceiptConfirm,
        },
    );
}

function activeReceiptCount(invoice: SaleInvoice) {
    return invoice.payments.filter((payment) => payment.status !== 'cancelled')
        .length;
}

function confirmPayment(payload: PaymentPayload) {
    if (!selectedInvoice.value) return;

    router.post(
        `/sales/invoices/${selectedInvoice.value.id}/receive`,
        {
            method: payload.method,
            payment_method_id: payload.paymentMethodId,
            membership_card_id: payload.membershipCardId,
            currency: payload.currency,
            received_amount: payload.receivedAmount,
            operation_status: payload.operationStatus,
            change_usd_amount: payload.changeUsdAmount,
            change_khr_amount: payload.changeKhrAmount,
        },
        {
            preserveScroll: true,
            onSuccess: closePayment,
        },
    );
}

function statusLabel(status: InvoiceStatus) {
    const labels: Record<InvoiceStatus, string> = {
        draft: 'Draft',
        issued: 'Issued',
        partially_paid: 'Partially Paid',
        paid: 'Paid',
        pay_later: 'Pay Later',
    };

    return labels[status] ?? status;
}
</script>

<template>
    <Head title="Sales" />

    <AppLayout>
        <main class="min-h-screen bg-[#f8fafc] p-4 md:p-8">
            <div class="mx-auto max-w-7xl">
                <header
                    class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-center"
                >
                    <div>
                        <h1 class="text-3xl font-black text-[#2A4858]">
                            Invoice Management
                        </h1>
                        <p class="text-sm font-medium text-gray-500">
                            Filter activity by date range and terminal
                        </p>
                        <p
                            v-if="posSession"
                            class="mt-2 text-xs font-bold text-[#007882]"
                        >
                            Session: {{ posSession.session_no }}
                        </p>
                    </div>

                    <div class="flex gap-3">
                        <Button
                            type="button"
                            variant="outline"
                            class="h-11 rounded-xl border-2 border-gray-200 bg-white px-5 text-sm font-bold text-[#2A4858] hover:bg-gray-50"
                            @click="goBack"
                        >
                            <ArrowLeft class="h-4 w-4" />
                            Back
                        </Button>
                        <Button
                            type="button"
                            class="h-11 rounded-xl bg-[#007882] px-5 text-sm font-bold text-white shadow-lg shadow-[#007882]/20 hover:bg-[#006773]"
                            @click="resetFilters"
                        >
                            <RotateCcw class="h-4 w-4" />
                            Reset
                        </Button>
                    </div>
                </header>

                <section
                    class="mb-4 rounded-2xl border border-gray-100 bg-white p-3 shadow-sm"
                >
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                        <div>
                            <label
                                class="mb-1 ml-1 block text-[10px] font-black tracking-widest text-gray-400 uppercase"
                            >
                                Start Date
                            </label>
                            <div class="relative">
                                <Calendar
                                    class="absolute top-1/2 left-4 h-4 w-4 -translate-y-1/2 text-gray-400"
                                />
                                <Input
                                    v-model="startDate"
                                    type="date"
                                    class="h-10 rounded-xl border-none bg-gray-50 pr-4 pl-11 text-sm font-semibold text-[#2A4858] focus-visible:ring-[#23AA8F]/40"
                                    @change="applyFilters"
                                />
                            </div>
                        </div>

                        <div>
                            <label
                                class="mb-1 ml-1 block text-[10px] font-black tracking-widest text-gray-400 uppercase"
                            >
                                End Date
                            </label>
                            <div class="relative">
                                <Calendar
                                    class="absolute top-1/2 left-4 h-4 w-4 -translate-y-1/2 text-gray-400"
                                />
                                <Input
                                    v-model="endDate"
                                    type="date"
                                    class="h-10 rounded-xl border-none bg-gray-50 pr-4 pl-11 text-sm font-semibold text-[#2A4858] focus-visible:ring-[#23AA8F]/40"
                                    @change="applyFilters"
                                />
                            </div>
                        </div>

                        <div>
                            <label
                                class="mb-1 ml-1 block text-[10px] font-black tracking-widest text-gray-400 uppercase"
                            >
                                Keywords
                            </label>
                            <div class="relative">
                                <Search
                                    class="absolute top-1/2 left-4 h-4 w-4 -translate-y-1/2 text-gray-400"
                                />
                                <Input
                                    v-model="search"
                                    type="text"
                                    placeholder="Search #INV, phone, seat..."
                                    class="h-10 rounded-xl border-none bg-gray-50 pr-4 pl-11 text-sm focus-visible:ring-[#23AA8F]/40"
                                    @keyup.enter="applyFilters"
                                />
                            </div>
                        </div>
                    </div>
                </section>

                <section class="mb-6 grid gap-4 lg:grid-cols-[1.35fr_1fr]">
                    <div
                        class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm"
                    >
                        <div class="mb-3 flex items-center justify-between">
                            <h2
                                class="text-xs font-black tracking-wide text-[#2A4858] uppercase"
                            >
                                Amount by Payment Term
                            </h2>
                            <span class="text-xs font-semibold text-gray-400">
                                Sales {{ money(paymentSummary.sales_usd) }}
                            </span>
                        </div>

                        <div class="grid gap-3 md:grid-cols-3">
                            <div class="rounded-xl bg-gray-50 p-3">
                                <p
                                    class="text-[10px] font-black text-gray-400 uppercase"
                                >
                                    Cash
                                </p>
                                <p
                                    class="mt-1 text-sm font-black text-[#2A4858]"
                                >
                                    {{ money(paymentSummary.cash_usd) }}
                                </p>
                                <p class="text-xs font-bold text-gray-500">
                                    ៛{{
                                        money(paymentSummary.cash_khr).replace(
                                            '$',
                                            '',
                                        )
                                    }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-gray-50 p-3">
                                <p
                                    class="text-[10px] font-black text-gray-400 uppercase"
                                >
                                    E-Banking
                                </p>
                                <p
                                    class="mt-1 text-sm font-black text-[#2A4858]"
                                >
                                    {{ money(paymentSummary.ebanking_usd) }}
                                </p>
                                <p class="text-xs font-bold text-gray-500">
                                    ៛{{
                                        money(
                                            paymentSummary.ebanking_khr,
                                        ).replace('$', '')
                                    }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-gray-50 p-3">
                                <p
                                    class="text-[10px] font-black text-gray-400 uppercase"
                                >
                                    Pay Later
                                </p>
                                <p
                                    class="mt-1 text-sm font-black text-[#2A4858]"
                                >
                                    {{ money(paymentSummary.pay_later_usd) }}
                                </p>
                                <p class="text-xs font-bold text-gray-500">
                                    ៛{{
                                        money(
                                            paymentSummary.pay_later_khr,
                                        ).replace('$', '')
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="rounded-2xl border border-emerald-100 bg-emerald-50/60 p-4 shadow-sm"
                    >
                        <h2
                            class="mb-3 text-xs font-black tracking-wide text-[#2A4858] uppercase"
                        >
                            Expected Cash in Drawer
                        </h2>

                        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
                            <div class="rounded-xl bg-white p-3">
                                <p
                                    class="text-[10px] font-black text-gray-400 uppercase"
                                >
                                    USD Drawer
                                </p>
                                <p
                                    class="mt-1 text-lg font-black text-[#007882]"
                                >
                                    {{
                                        money(paymentSummary.expected_cash_usd)
                                    }}
                                </p>
                                <p class="text-[11px] text-gray-500">
                                    Opening
                                    {{
                                        money(posSession?.opening_cash_usd ?? 0)
                                    }}
                                    + cash {{ money(paymentSummary.cash_usd) }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-white p-3">
                                <p
                                    class="text-[10px] font-black text-gray-400 uppercase"
                                >
                                    KHR Drawer
                                </p>
                                <p
                                    class="mt-1 text-lg font-black text-[#007882]"
                                >
                                    ៛{{
                                        money(
                                            paymentSummary.expected_cash_khr,
                                        ).replace('$', '')
                                    }}
                                </p>
                                <p class="text-[11px] text-gray-500">
                                    Opening ៛{{
                                        money(
                                            posSession?.opening_cash_khr ?? 0,
                                        ).replace('$', '')
                                    }}
                                    + cash ៛{{
                                        money(paymentSummary.cash_khr).replace(
                                            '$',
                                            '',
                                        )
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <section
                    class="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm"
                >
                    <div v-if="hasInvoices" class="overflow-x-auto">
                        <table class="w-full border-collapse text-left">
                            <thead>
                                <tr class="bg-gray-50/50">
                                    <th
                                        class="w-12 px-4 py-4 text-center text-[10px] font-black tracking-widest text-gray-400 uppercase"
                                    >
                                        +/-
                                    </th>
                                    <th
                                        class="px-4 py-4 text-[10px] font-black tracking-widest text-gray-400 uppercase"
                                    >
                                        Date / Invoice
                                    </th>
                                    <th
                                        class="px-4 py-4 text-[10px] font-black tracking-widest text-gray-400 uppercase"
                                    >
                                        POS Name
                                    </th>
                                    <th
                                        class="px-4 py-4 text-[10px] font-black tracking-widest text-gray-400 uppercase"
                                    >
                                        Customer / Phone
                                    </th>
                                    <th
                                        class="px-4 py-4 text-center text-[10px] font-black tracking-widest text-gray-400 uppercase"
                                    >
                                        Seat
                                    </th>
                                    <th
                                        class="px-4 py-4 text-right text-[10px] font-black tracking-widest text-gray-400 uppercase"
                                    >
                                        Total Amount
                                    </th>
                                    <th
                                        class="px-4 py-4 text-right text-[10px] font-black tracking-widest text-gray-400 uppercase"
                                    >
                                        Pending Amount
                                    </th>
                                    <th
                                        class="px-6 py-4 text-right text-[10px] font-black tracking-widest text-gray-400 uppercase"
                                    >
                                        Action
                                    </th>
                                </tr>
                            </thead>

                            <template
                                v-for="group in groupedInvoices"
                                :key="group.key"
                            >
                                <tbody v-if="group.invoices.length > 0">
                                    <tr
                                        class="cursor-pointer border-y transition-colors hover:bg-gray-50"
                                        :class="group.rowClass"
                                        @click="toggleGroup(group.key)"
                                    >
                                        <td class="px-4 py-4 text-center">
                                            <ChevronDown
                                                class="mx-auto h-3.5 w-3.5 transition-transform"
                                                :class="[
                                                    group.iconClass,
                                                    collapsedGroups[group.key]
                                                        ? '-rotate-90'
                                                        : '',
                                                ]"
                                            />
                                        </td>
                                        <td colspan="4" class="px-2 py-4">
                                            <div
                                                class="flex items-center gap-3"
                                            >
                                                <span
                                                    class="rounded-full px-3 py-1 text-[10px] font-black uppercase"
                                                    :class="group.badgeClass"
                                                >
                                                    {{ group.label }}
                                                </span>
                                                <span
                                                    class="text-xs font-bold text-gray-400"
                                                >
                                                    {{ group.invoices.length }}
                                                    Invoice{{
                                                        group.invoices
                                                            .length === 1
                                                            ? ''
                                                            : 's'
                                                    }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 text-right">
                                            <span
                                                class="text-sm font-black text-[#2A4858]"
                                            >
                                                {{
                                                    money(
                                                        group.invoices.reduce(
                                                            (total, invoice) =>
                                                                total +
                                                                Number(
                                                                    invoice.grand_total ||
                                                                        0,
                                                                ),
                                                            0,
                                                        ),
                                                    )
                                                }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-right">
                                            <span
                                                class="text-sm font-black"
                                                :class="group.totalClass"
                                            >
                                                {{
                                                    money(
                                                        groupTotal(
                                                            group.invoices,
                                                        ),
                                                    )
                                                }}
                                            </span>
                                        </td>
                                        <td></td>
                                    </tr>
                                </tbody>

                                <tbody
                                    v-if="
                                        group.invoices.length > 0 &&
                                        !collapsedGroups[group.key]
                                    "
                                >
                                    <tr
                                        v-for="(
                                            invoice, index
                                        ) in group.invoices"
                                        :key="invoice.id"
                                        class="border-b border-gray-50 transition-colors hover:bg-gray-50/50"
                                    >
                                        <td
                                            class="px-4 py-4 text-center text-xs font-bold text-gray-300"
                                        >
                                            {{ index + 1 }}
                                        </td>
                                        <td class="px-4 py-4">
                                            <div
                                                class="text-[10px] font-bold text-gray-400 uppercase"
                                            >
                                                {{
                                                    invoice.display_date ??
                                                    invoice.date ??
                                                    '-'
                                                }}
                                            </div>
                                            <div
                                                class="text-sm font-black text-[#2A4858]"
                                            >
                                                #{{ invoice.invoice_no }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-4">
                                            <span
                                                class="rounded bg-gray-100 px-2 py-1 text-[10px] font-black text-gray-500"
                                            >
                                                {{
                                                    invoice.pos_name ??
                                                    'Terminal'
                                                }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div
                                                class="text-sm font-black text-[#2A4858]"
                                            >
                                                {{
                                                    invoice.customer_name ??
                                                    'Walk-in Customer'
                                                }}
                                            </div>
                                            <div
                                                class="text-[11px] text-gray-500"
                                            >
                                                {{
                                                    invoice.customer_phone ??
                                                    'No phone'
                                                }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <span
                                                class="rounded bg-gray-100 px-2 py-1 text-[10px] font-black text-gray-600"
                                            >
                                                {{
                                                    invoice.seat_name ??
                                                    'TAKEAWAY'
                                                }}
                                            </span>
                                        </td>
                                        <td
                                            class="px-4 py-4 text-right text-sm font-black"
                                        >
                                            {{ money(invoice.grand_total) }}
                                        </td>
                                        <td
                                            class="px-4 py-4 text-right text-sm font-black"
                                            :class="
                                                invoice.status === 'paid'
                                                    ? 'text-gray-400'
                                                    : 'text-[#2A4858]'
                                            "
                                        >
                                            {{
                                                money(
                                                    invoice.balance_amount ||
                                                        invoice.grand_total,
                                                )
                                            }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex justify-end">
                                                <DropdownMenu :modal="false">
                                                    <DropdownMenuTrigger
                                                        as-child
                                                    >
                                                        <Button
                                                            type="button"
                                                            variant="ghost"
                                                            size="icon"
                                                            class="size-8 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-[#007882]"
                                                            title="Actions"
                                                            @click.stop
                                                        >
                                                            <MoreVertical
                                                                class="h-4 w-4"
                                                            />
                                                            <span
                                                                class="sr-only"
                                                            >
                                                                Open actions
                                                            </span>
                                                        </Button>
                                                    </DropdownMenuTrigger>
                                                    <DropdownMenuContent
                                                        align="end"
                                                        class="z-[80] w-44"
                                                        @click.stop
                                                    >
                                                        <DropdownMenuItem
                                                            @select="
                                                                showInvoiceDetail(
                                                                    invoice,
                                                                )
                                                            "
                                                        >
                                                            <Eye
                                                                class="h-4 w-4 text-[#007882]"
                                                            />
                                                            View
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem
                                                            @select="
                                                                printInvoice(
                                                                    invoice,
                                                                )
                                                            "
                                                        >
                                                            <Printer
                                                                class="h-4 w-4 text-[#2A4858]"
                                                            />
                                                            Print Invoice
                                                        </DropdownMenuItem>
                                                        <DropdownMenuSeparator />
                                                        <DropdownMenuItem
                                                            v-if="
                                                                invoice.status !==
                                                                'paid'
                                                            "
                                                            @select="
                                                                openPayment(
                                                                    invoice,
                                                                )
                                                            "
                                                        >
                                                            <CreditCard
                                                                class="h-4 w-4 text-emerald-600"
                                                            />
                                                            Receive Payment
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem
                                                            v-if="
                                                                invoice.status ===
                                                                'paid'
                                                            "
                                                            @select="
                                                                printReceipt(
                                                                    invoice,
                                                                )
                                                            "
                                                        >
                                                            <ReceiptText
                                                                class="h-4 w-4 text-[#007882]"
                                                            />
                                                            View Receipt
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem
                                                            v-if="
                                                                latestActivePayment(
                                                                    invoice,
                                                                )
                                                            "
                                                            @select="
                                                                cancelInvoiceReceipts(
                                                                    invoice,
                                                                )
                                                            "
                                                        >
                                                            <XCircle
                                                                class="h-4 w-4 text-rose-600"
                                                            />
                                                            Cancel Receipt
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem
                                                            @select="
                                                                cancelInvoice(
                                                                    invoice,
                                                                )
                                                            "
                                                        >
                                                            <X
                                                                class="h-4 w-4 text-rose-600"
                                                            />
                                                            Cancel Invoice
                                                        </DropdownMenuItem>
                                                    </DropdownMenuContent>
                                                </DropdownMenu>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </template>
                        </table>
                    </div>
                    <TablePagination
                        v-if="hasInvoices"
                        :current-page="invoicesPage"
                        :total-pages="invoicesTotalPages"
                        :total-rows="invoicesTotalRows"
                        :page-start="invoicesPageStart"
                        :page-end="invoicesPageEnd"
                        :rows-per-page="invoicesPageSize"
                        @go-to-page="goToInvoicesPage"
                        @update-rows-per-page="setInvoicesRowsPerPage"
                    />

                    <div v-else class="p-20 text-center">
                        <div
                            class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-50"
                        >
                            <Filter class="h-7 w-7 text-gray-200" />
                        </div>
                        <h3 class="font-black text-[#2A4858]">
                            No matching records
                        </h3>
                        <p class="text-sm font-medium text-gray-400">
                            Try adjusting your date range or search terms
                        </p>
                    </div>
                </section>
            </div>

            <Payment
                v-if="selectedInvoice"
                :open="paymentOpen"
                :subtotal="selectedInvoice.subtotal"
                :discount-amount="selectedInvoice.discount_amount"
                :tax-amount="selectedInvoice.tax_amount"
                :final-amount="selectedInvoice.balance_amount"
                :exchange-rate="selectedInvoice.exchange_rate ?? 4100"
                :allow-pay-later="false"
                :payment-methods="paymentMethods"
                :membership-cards="
                    selectedInvoice.customer_id
                        ? (membershipCards?.[selectedInvoice.customer_id] ?? [])
                        : []
                "
                @close="closePayment"
                @confirm="confirmPayment"
            />

            <InvoiceView
                v-if="detailInvoice"
                :invoice="detailInvoice"
                @close="closeInvoiceDetail"
                @cancel-receipt="cancelReceipt"
            />

            <div
                v-if="receiptToCancel || invoiceReceiptsToCancel"
                class="fixed inset-0 z-[90] flex items-center justify-center bg-[#2a4858]/25 p-4 backdrop-blur-sm"
                @click.self="closeCancelReceiptConfirm"
            >
                <section
                    class="w-full max-w-md overflow-hidden rounded-lg bg-white shadow-2xl"
                >
                    <header class="border-b border-slate-100 p-5">
                        <div class="flex items-start gap-3">
                            <div
                                class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-700"
                            >
                                <ShieldAlert class="size-5" />
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-[#2a4858]">
                                    Cancel receipt?
                                </h2>
                                <p class="mt-1 text-sm text-slate-500">
                                    <template v-if="invoiceReceiptsToCancel">
                                        This will cancel
                                        {{
                                            activeReceiptCount(
                                                invoiceReceiptsToCancel,
                                            )
                                        }}
                                        receipt{{
                                            activeReceiptCount(
                                                invoiceReceiptsToCancel,
                                            ) === 1
                                                ? ''
                                                : 's'
                                        }}
                                        for invoice
                                        {{ invoiceReceiptsToCancel.invoice_no }}
                                        and reopen it for payment.
                                    </template>
                                    <template v-else-if="receiptToCancel">
                                        This will cancel receipt
                                        {{ receiptToCancel.payment_no }} and
                                        reopen the invoice for payment.
                                    </template>
                                </p>
                            </div>
                        </div>
                    </header>

                    <footer class="grid grid-cols-2 gap-3 bg-slate-50 p-5">
                        <button
                            type="button"
                            class="inline-flex h-11 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
                            @click="closeCancelReceiptConfirm"
                        >
                            No
                        </button>
                        <button
                            type="button"
                            class="inline-flex h-11 items-center justify-center rounded-lg bg-[#2a4858] px-4 text-sm font-bold text-white shadow-lg transition hover:bg-[#007882]"
                            @click="confirmCancelReceipt"
                        >
                            Yes
                        </button>
                    </footer>
                </section>
            </div>
        </main>
    </AppLayout>
</template>
