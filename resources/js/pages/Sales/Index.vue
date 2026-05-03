<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Calendar,
    ChevronDown,
    Eye,
    Filter,
    Printer,
    RotateCcw,
    Search,
    X,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { usePosFormatting } from '@/composables/usePosFormatting';
import AppLayout from '@/layouts/AppLayout.vue';
import Payment from '@/pages/Seats/Payment.vue';

type InvoiceStatus =
    | 'draft'
    | 'issued'
    | 'partially_paid'
    | 'paid'
    | 'pay_later';

type SaleInvoice = {
    id: number;
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
    lines: SaleInvoiceLine[];
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

type PaymentPayload = {
    method: string;
    currency: 'USD' | 'KHR';
    receivedAmount: number;
    operationStatus: 'invoice' | 'invoice_receipt_done';
};

const props = defineProps<{
    posSession?: {
        id: number;
        session_no: string;
    };
    invoices: SaleInvoice[];
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
const { money } = usePosFormatting();

const unpaidInvoices = computed(() => {
    return props.invoices.filter((invoice) => invoice.status !== 'paid');
});

const settledInvoices = computed(() => {
    return props.invoices.filter((invoice) => invoice.status === 'paid');
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

function confirmPayment(payload: PaymentPayload) {
    if (!selectedInvoice.value) return;

    router.post(
        `/sales/invoices/${selectedInvoice.value.id}/receive`,
        {
            method: payload.method,
            currency: payload.currency,
            received_amount: payload.receivedAmount,
            operation_status: payload.operationStatus,
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
                    class="mb-6 space-y-4 rounded-3xl border border-gray-100 bg-white p-4 shadow-sm"
                >
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
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
                                    class="h-12 rounded-2xl border-none bg-gray-50 pr-4 pl-12 text-sm font-semibold text-[#2A4858] focus-visible:ring-[#23AA8F]/40"
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
                                    class="h-12 rounded-2xl border-none bg-gray-50 pr-4 pl-12 text-sm font-semibold text-[#2A4858] focus-visible:ring-[#23AA8F]/40"
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
                                    class="h-12 rounded-2xl border-none bg-gray-50 pr-4 pl-12 text-sm focus-visible:ring-[#23AA8F]/40"
                                    @keyup.enter="applyFilters"
                                />
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
                                        Amount
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
                                            <div
                                                class="flex items-center justify-end gap-2"
                                            >
                                                <Button
                                                    type="button"
                                                    variant="outline"
                                                    class="h-8 w-8 rounded-lg border-gray-100 bg-white p-0 text-[#2A4858] hover:border-[#23AA8F]/40 hover:bg-[#23AA8F]/10"
                                                    title="View invoice items"
                                                    @click.stop="
                                                        showInvoiceDetail(
                                                            invoice,
                                                        )
                                                    "
                                                >
                                                    <Eye class="h-4 w-4" />
                                                </Button>

                                                <Button
                                                    type="button"
                                                    variant="outline"
                                                    class="h-8 w-8 rounded-lg border-gray-100 bg-white p-0 text-[#2A4858] hover:border-[#23AA8F]/40 hover:bg-[#23AA8F]/10"
                                                    title="Print invoice"
                                                    @click.stop="
                                                        printInvoice(invoice)
                                                    "
                                                >
                                                    <Printer class="h-4 w-4" />
                                                </Button>

                                                <Button
                                                    v-if="
                                                        invoice.status !==
                                                        'paid'
                                                    "
                                                    type="button"
                                                    class="h-8 rounded-lg bg-[#23AA8F] px-3 text-[10px] font-black text-white hover:bg-[#007882]"
                                                    @click.stop="
                                                        openPayment(invoice)
                                                    "
                                                >
                                                    Receive
                                                </Button>
                                                <span
                                                    v-else
                                                    class="text-[10px] font-black text-gray-300 uppercase"
                                                >
                                                    {{
                                                        statusLabel(
                                                            invoice.status,
                                                        )
                                                    }}
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </template>
                        </table>
                    </div>

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
                @close="closePayment"
                @confirm="confirmPayment"
            />

            <div
                v-if="detailInvoice"
                class="fixed inset-0 z-[75] flex items-center justify-center bg-[#2A4858]/20 p-4 backdrop-blur-sm"
                @click.self="closeInvoiceDetail"
            >
                <section
                    class="flex max-h-[90vh] w-full max-w-3xl flex-col overflow-hidden rounded-3xl bg-white shadow-2xl"
                >
                    <header
                        class="flex items-start justify-between gap-4 border-b border-gray-100 p-5"
                    >
                        <div>
                            <p
                                class="text-[10px] font-black tracking-widest text-gray-400 uppercase"
                            >
                                Invoice Detail
                            </p>
                            <h2 class="mt-1 text-xl font-black text-[#2A4858]">
                                #{{ detailInvoice.invoice_no }}
                            </h2>
                            <p class="mt-1 text-xs font-medium text-gray-400">
                                {{ detailInvoice.display_date }} /
                                {{
                                    detailInvoice.customer_name ??
                                    'Walk-in Customer'
                                }}
                            </p>
                        </div>

                        <Button
                            type="button"
                            variant="outline"
                            class="h-9 w-9 rounded-xl border-gray-100 p-0 text-gray-400 hover:bg-gray-50 hover:text-[#2A4858]"
                            @click="closeInvoiceDetail"
                        >
                            <X class="h-4 w-4" />
                        </Button>
                    </header>

                    <div class="min-h-0 flex-1 overflow-y-auto p-5">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead>
                                    <tr class="border-b border-gray-100">
                                        <th
                                            class="py-3 pr-4 text-[10px] font-black tracking-widest text-gray-400 uppercase"
                                        >
                                            Item
                                        </th>
                                        <th
                                            class="px-4 py-3 text-right text-[10px] font-black tracking-widest text-gray-400 uppercase"
                                        >
                                            Qty
                                        </th>
                                        <th
                                            class="px-4 py-3 text-right text-[10px] font-black tracking-widest text-gray-400 uppercase"
                                        >
                                            Price
                                        </th>
                                        <th
                                            class="px-4 py-3 text-right text-[10px] font-black tracking-widest text-gray-400 uppercase"
                                        >
                                            Tax
                                        </th>
                                        <th
                                            class="py-3 pl-4 text-right text-[10px] font-black tracking-widest text-gray-400 uppercase"
                                        >
                                            Total
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="line in detailInvoice.lines"
                                        :key="line.id"
                                        class="border-b border-gray-50"
                                    >
                                        <td class="py-4 pr-4">
                                            <div
                                                class="text-sm font-black text-[#2A4858]"
                                            >
                                                {{ line.menu_name }}
                                            </div>
                                            <p
                                                v-if="line.note"
                                                class="mt-1 text-[11px] font-medium text-[#007882]"
                                            >
                                                {{ line.note }}
                                            </p>
                                        </td>
                                        <td
                                            class="px-4 py-4 text-right text-sm font-bold text-[#2A4858]"
                                        >
                                            {{ line.quantity }}
                                        </td>
                                        <td
                                            class="px-4 py-4 text-right text-sm font-bold text-[#2A4858]"
                                        >
                                            {{ money(line.unit_price) }}
                                        </td>
                                        <td
                                            class="px-4 py-4 text-right text-sm font-bold text-[#2A4858]"
                                        >
                                            {{ money(line.tax_amount) }}
                                        </td>
                                        <td
                                            class="py-4 pl-4 text-right text-sm font-black text-[#007882]"
                                        >
                                            {{ money(line.line_total) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div
                            v-if="detailInvoice.lines.length === 0"
                            class="rounded-2xl border border-dashed border-gray-200 p-8 text-center text-sm font-medium text-gray-400"
                        >
                            No items found for this invoice.
                        </div>
                    </div>

                    <footer
                        class="grid gap-2 border-t border-gray-100 bg-gray-50/60 p-5 text-sm sm:grid-cols-4"
                    >
                        <div>
                            <p
                                class="text-[10px] font-black tracking-widest text-gray-400 uppercase"
                            >
                                Subtotal
                            </p>
                            <p class="font-black text-[#2A4858]">
                                {{ money(detailInvoice.subtotal) }}
                            </p>
                        </div>
                        <div>
                            <p
                                class="text-[10px] font-black tracking-widest text-gray-400 uppercase"
                            >
                                Discount
                            </p>
                            <p class="font-black text-[#2A4858]">
                                {{ money(detailInvoice.discount_amount) }}
                            </p>
                        </div>
                        <div>
                            <p
                                class="text-[10px] font-black tracking-widest text-gray-400 uppercase"
                            >
                                Tax
                            </p>
                            <p class="font-black text-[#2A4858]">
                                {{ money(detailInvoice.tax_amount) }}
                            </p>
                        </div>
                        <div class="sm:text-right">
                            <p
                                class="text-[10px] font-black tracking-widest text-gray-400 uppercase"
                            >
                                Grand Total
                            </p>
                            <p class="text-lg font-black text-[#007882]">
                                {{ money(detailInvoice.grand_total) }}
                            </p>
                        </div>
                    </footer>
                </section>
            </div>
        </main>
    </AppLayout>
</template>
