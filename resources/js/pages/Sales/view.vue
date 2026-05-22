<script setup lang="ts">
import { X } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { usePosFormatting } from '@/composables/usePosFormatting';

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

type SaleInvoice = {
    id: number;
    invoice_no: string;
    display_date?: string | null;
    customer_name?: string | null;
    subtotal: number;
    discount_amount: number;
    tax_amount: number;
    grand_total: number;
    paid_amount: number;
    balance_amount: number;
    order_created_by: string[];
    invoice_created_by?: string | null;
    receipt_created_by?: string | null;
    payments: InvoicePayment[];
    lines: SaleInvoiceLine[];
};

defineProps<{
    invoice: SaleInvoice;
}>();

const emit = defineEmits<{
    close: [];
    cancelReceipt: [payment: InvoicePayment];
}>();

const { money } = usePosFormatting();

function namesList(names: string[] | null | undefined) {
    return names?.length ? names.join(', ') : '-';
}
</script>

<template>
    <div
        class="fixed inset-0 z-[75] flex items-center justify-center bg-[#2A4858]/20 p-3 backdrop-blur-sm"
        @click.self="emit('close')"
    >
        <section
            class="flex max-h-[92vh] w-full max-w-4xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl"
        >
            <header
                class="flex items-start justify-between gap-4 border-b border-gray-100 px-4 py-3"
            >
                <div>
                    <p
                        class="text-[10px] font-semibold tracking-widest text-gray-400 uppercase"
                    >
                        Invoice Detail
                    </p>
                    <h2 class="mt-0.5 text-lg font-semibold text-[#2A4858]">
                        #{{ invoice.invoice_no }}
                    </h2>
                    <p class="mt-0.5 text-xs text-gray-400">
                        {{ invoice.display_date }} /
                        {{ invoice.customer_name ?? 'Walk-in Customer' }}
                    </p>
                </div>

                <Button
                    type="button"
                    variant="outline"
                    class="h-8 w-8 rounded-lg border-gray-100 p-0 text-gray-400 hover:bg-gray-50 hover:text-[#2A4858]"
                    @click="emit('close')"
                >
                    <X class="h-4 w-4" />
                </Button>
            </header>

            <div class="min-h-0 flex-1 overflow-y-auto p-4">
                <div class="mb-3 grid gap-2 text-xs sm:grid-cols-3">
                    <div class="rounded-lg bg-gray-50 px-3 py-2">
                        <p
                            class="text-[9px] font-semibold tracking-widest text-gray-400 uppercase"
                        >
                            Order Created By
                        </p>
                        <p class="mt-1 text-[#2A4858]">
                            {{ namesList(invoice.order_created_by) }}
                        </p>
                    </div>
                    <div class="rounded-lg bg-gray-50 px-3 py-2">
                        <p
                            class="text-[9px] font-semibold tracking-widest text-gray-400 uppercase"
                        >
                            Invoice Created By
                        </p>
                        <p class="mt-1 text-[#2A4858]">
                            {{ invoice.invoice_created_by ?? '-' }}
                        </p>
                    </div>
                    <div class="rounded-lg bg-gray-50 px-3 py-2">
                        <p
                            class="text-[9px] font-semibold tracking-widest text-gray-400 uppercase"
                        >
                            Receipt Created By
                        </p>
                        <p class="mt-1 text-[#2A4858]">
                            {{ invoice.receipt_created_by ?? '-' }}
                        </p>
                    </div>
                </div>

                <div
                    v-if="invoice.payments.length"
                    class="mb-3 rounded-lg border border-gray-100"
                >
                    <div
                        class="border-b border-gray-100 px-3 py-2 text-[10px] font-semibold tracking-widest text-gray-400 uppercase"
                    >
                        Receipts
                    </div>
                    <div class="divide-y divide-gray-100">
                        <div
                            v-for="payment in invoice.payments"
                            :key="payment.id"
                            class="grid gap-2 px-3 py-2 text-xs sm:grid-cols-[1fr_auto_auto]"
                        >
                            <div>
                                <p class="font-medium text-[#2A4858]">
                                    {{ payment.payment_no }}
                                </p>
                                <p class="text-[11px] text-gray-400">
                                    {{ payment.method ?? 'Payment' }} /
                                    {{ payment.paid_at ?? '-' }} /
                                    {{ payment.received_by ?? '-' }}
                                </p>
                            </div>
                            <p
                                class="font-semibold"
                                :class="
                                    payment.status === 'cancelled'
                                        ? 'text-gray-400 line-through'
                                        : 'text-[#007882]'
                                "
                            >
                                {{ money(payment.amount_usd_equivalent) }}
                            </p>
                            <Button
                                v-if="payment.status !== 'cancelled'"
                                type="button"
                                variant="outline"
                                class="h-7 rounded-lg border-rose-100 px-2 text-[11px] font-semibold text-rose-600 hover:bg-rose-50"
                                @click="emit('cancelReceipt', payment)"
                            >
                                Cancel
                            </Button>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-gray-100">
                                <th
                                    class="py-2 pr-3 text-[10px] font-semibold tracking-widest text-gray-400 uppercase"
                                >
                                    Item
                                </th>
                                <th
                                    class="px-3 py-2 text-right text-[10px] font-semibold tracking-widest text-gray-400 uppercase"
                                >
                                    Qty
                                </th>
                                <th
                                    class="px-3 py-2 text-right text-[10px] font-semibold tracking-widest text-gray-400 uppercase"
                                >
                                    Price
                                </th>
                                <th
                                    class="px-3 py-2 text-right text-[10px] font-semibold tracking-widest text-gray-400 uppercase"
                                >
                                    Tax
                                </th>
                                <th
                                    class="py-2 pl-3 text-right text-[10px] font-semibold tracking-widest text-gray-400 uppercase"
                                >
                                    Total
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="line in invoice.lines"
                                :key="line.id"
                                class="border-b border-gray-50"
                            >
                                <td class="py-2 pr-3">
                                    <div
                                        class="text-xs font-medium text-[#2A4858]"
                                    >
                                        {{ line.menu_name }}
                                    </div>
                                    <p
                                        v-if="line.note"
                                        class="mt-0.5 text-[11px] text-[#007882]"
                                    >
                                        {{ line.note }}
                                    </p>
                                </td>
                                <td
                                    class="px-3 py-2 text-right text-xs text-[#2A4858]"
                                >
                                    {{ line.quantity }}
                                </td>
                                <td
                                    class="px-3 py-2 text-right text-xs text-[#2A4858]"
                                >
                                    {{ money(line.unit_price) }}
                                </td>
                                <td
                                    class="px-3 py-2 text-right text-xs text-[#2A4858]"
                                >
                                    {{ money(line.tax_amount) }}
                                </td>
                                <td
                                    class="py-2 pl-3 text-right text-xs font-semibold text-[#007882]"
                                >
                                    {{ money(line.line_total) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div
                    v-if="invoice.lines.length === 0"
                    class="rounded-lg border border-dashed border-gray-200 p-6 text-center text-sm text-gray-400"
                >
                    No items found for this invoice.
                </div>
            </div>

            <footer
                class="grid gap-2 border-t border-gray-100 bg-gray-50/60 px-4 py-3 text-xs sm:grid-cols-6"
            >
                <div>
                    <p
                        class="text-[9px] font-semibold tracking-widest text-gray-400 uppercase"
                    >
                        Subtotal
                    </p>
                    <p class="font-medium text-[#2A4858]">
                        {{ money(invoice.subtotal) }}
                    </p>
                </div>
                <div>
                    <p
                        class="text-[9px] font-semibold tracking-widest text-gray-400 uppercase"
                    >
                        Discount
                    </p>
                    <p class="font-medium text-[#2A4858]">
                        {{ money(invoice.discount_amount) }}
                    </p>
                </div>
                <div>
                    <p
                        class="text-[9px] font-semibold tracking-widest text-gray-400 uppercase"
                    >
                        Tax
                    </p>
                    <p class="font-medium text-[#2A4858]">
                        {{ money(invoice.tax_amount) }}
                    </p>
                </div>
                <div class="sm:text-right">
                    <p
                        class="text-[9px] font-semibold tracking-widest text-gray-400 uppercase"
                    >
                        Grand Total
                    </p>
                    <p class="font-semibold text-[#007882]">
                        {{ money(invoice.grand_total) }}
                    </p>
                </div>
                <div class="sm:text-right">
                    <p
                        class="text-[9px] font-semibold tracking-widest text-gray-400 uppercase"
                    >
                        Paid
                    </p>
                    <p class="font-medium text-[#2A4858]">
                        {{ money(invoice.paid_amount) }}
                    </p>
                </div>
                <div class="sm:text-right">
                    <p
                        class="text-[9px] font-semibold tracking-widest text-gray-400 uppercase"
                    >
                        Balance
                    </p>
                    <p class="font-semibold text-orange-500">
                        {{ money(invoice.balance_amount) }}
                    </p>
                </div>
            </footer>
        </section>
    </div>
</template>
