<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    ArrowDownLeft,
    ArrowLeft,
    ArrowUpRight,
    Search,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import MembershipCardPreview from '@/components/master-data/MembershipCardPreview.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

type CardStatus = 'active' | 'inactive' | 'blocked' | 'expired' | 'cancelled';

type CustomerCard = {
    id: number;
    cardNo: string;
    cardName: string;
    status: CardStatus;
    customerName: string;
    customerPhone?: string | null;
    branchName?: string | null;
    issuedDate?: string | null;
    expiredDate?: string | null;
    remark?: string | null;
};

type CardTransaction = {
    id: number;
    transactionNo: string;
    type: string;
    direction: 'credit' | 'debit';
    currency: string;
    amount: number;
    promotionAmount: number;
    promotionName?: string | null;
    balanceBefore: number;
    balanceAfter: number;
    invoiceNo?: string | null;
    paymentNo?: string | null;
    note?: string | null;
    transactedAt?: string | null;
};

const props = defineProps<{
    card: CustomerCard;
    transactions: CardTransaction[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Membership Cards', href: '/membership-cards' },
    { title: 'Ledger' },
];

const search = ref('');
const typeFilter = ref<'all' | 'credit' | 'debit'>('all');

const filteredTransactions = computed(() => {
    const term = search.value.trim().toLowerCase();

    return props.transactions.filter((transaction) => {
        const matchesSearch =
            `${transaction.transactionNo} ${transaction.type} ${transaction.invoiceNo ?? ''} ${transaction.paymentNo ?? ''} ${transaction.note ?? ''}`
                .toLowerCase()
                .includes(term);
        const matchesType =
            typeFilter.value === 'all' ||
            transaction.direction === typeFilter.value;

        return matchesSearch && matchesType;
    });
});

function amountText(transaction: CardTransaction) {
    const sign = transaction.direction === 'credit' ? '+' : '-';
    const total = Number(transaction.amount + transaction.promotionAmount);

    if (transaction.currency.toUpperCase() === 'KHR') {
        return `${sign}${Math.round(total).toLocaleString()} KHR`;
    }

    return `${sign}${transaction.currency} ${total.toFixed(2)}`;
}

function balanceText(currency: string, value: number) {
    if (currency.toUpperCase() === 'KHR') {
        return `${Math.round(value).toLocaleString()} KHR`;
    }

    return `${currency} ${Number(value).toFixed(2)}`;
}
</script>

<template>
    <Head title="Membership Card Ledger" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-[calc(100dvh-4rem)] w-full [scrollbar-gutter:stable] flex-col gap-6 overflow-y-scroll bg-[#f8fafc] p-4 text-slate-800 md:h-[calc(100dvh-5rem)] md:p-6 xl:p-8 2xl:p-10"
        >
            <div
                class="flex flex-col justify-between gap-4 lg:flex-row lg:items-center"
            >
                <div>
                    <Button
                        variant="ghost"
                        class="mb-2 h-8 rounded-lg px-2 text-xs font-bold text-slate-500"
                        @click="router.get('/membership-cards')"
                    >
                        <ArrowLeft class="size-4" />
                        Back
                    </Button>
                    <h1
                        class="text-2xl font-black tracking-tight text-[#2A4858]"
                    >
                        Card Ledger
                    </h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Full transaction detail for {{ card.cardNo }}.
                    </p>
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-[420px_minmax(0,1fr)]">
                <section
                    class="h-fit rounded-lg border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <MembershipCardPreview
                        :card-no="card.cardNo"
                        :card-name="card.cardName"
                        :customer-name="card.customerName"
                        :company-branch="card.branchName ?? 'DM Group'"
                        :issued-date="card.issuedDate"
                        :expired-date="card.expiredDate"
                        :remark="card.remark"
                        :status="card.status"
                    />
                </section>

                <section
                    class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"
                >
                    <div class="border-b border-slate-100 p-5">
                        <div class="grid gap-3 md:grid-cols-[1fr_160px]">
                            <div class="relative">
                                <Search
                                    class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"
                                />
                                <Input
                                    v-model="search"
                                    class="h-10 rounded-lg border-slate-200 bg-slate-50 pl-9 text-sm focus-visible:ring-[#007882]"
                                    placeholder="Search transaction, invoice, payment, note..."
                                />
                            </div>
                            <select
                                v-model="typeFilter"
                                class="h-10 rounded-lg border border-slate-200 bg-slate-50 px-3 text-xs font-bold text-slate-500 outline-none focus:border-[#007882]"
                            >
                                <option value="all">All Ledger</option>
                                <option value="credit">Credits</option>
                                <option value="debit">Debits</option>
                            </select>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[840px] border-collapse">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th
                                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                                    >
                                        Date
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                                    >
                                        Transaction
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                                    >
                                        Reference
                                    </th>
                                    <th
                                        class="px-4 py-3 text-right text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                                    >
                                        Amount
                                    </th>
                                    <th
                                        class="px-4 py-3 text-right text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                                    >
                                        Balance
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="transaction in filteredTransactions"
                                    :key="transaction.id"
                                    class="border-b border-slate-100 last:border-b-0 hover:bg-slate-50"
                                >
                                    <td
                                        class="px-4 py-3 font-mono text-xs text-slate-500"
                                    >
                                        {{ transaction.transactedAt ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="flex size-9 items-center justify-center rounded-lg bg-slate-100"
                                            >
                                                <ArrowDownLeft
                                                    v-if="
                                                        transaction.direction ===
                                                        'credit'
                                                    "
                                                    class="size-4 text-emerald-600"
                                                />
                                                <ArrowUpRight
                                                    v-else
                                                    class="size-4 text-rose-600"
                                                />
                                            </div>
                                            <div>
                                                <p
                                                    class="text-sm font-black text-slate-700"
                                                >
                                                    {{ transaction.type }}
                                                </p>
                                                <p
                                                    class="font-mono text-[10px] text-slate-400"
                                                >
                                                    {{
                                                        transaction.transactionNo
                                                    }}
                                                </p>
                                                <p
                                                    v-if="
                                                        transaction.promotionName
                                                    "
                                                    class="text-[10px] font-bold text-[#007882]"
                                                >
                                                    {{
                                                        transaction.promotionName
                                                    }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td
                                        class="px-4 py-3 text-xs text-slate-500"
                                    >
                                        <div>
                                            {{ transaction.invoiceNo ?? '-' }}
                                        </div>
                                        <div
                                            class="font-mono text-[10px] text-slate-400"
                                        >
                                            {{ transaction.paymentNo ?? '' }}
                                        </div>
                                    </td>
                                    <td
                                        class="px-4 py-3 text-right text-sm font-black"
                                        :class="
                                            transaction.direction === 'credit'
                                                ? 'text-emerald-600'
                                                : 'text-rose-600'
                                        "
                                    >
                                        {{ amountText(transaction) }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div
                                            class="text-xs font-bold text-slate-700"
                                        >
                                            {{
                                                balanceText(
                                                    transaction.currency,
                                                    transaction.balanceAfter,
                                                )
                                            }}
                                        </div>
                                        <div class="text-[10px] text-slate-400">
                                            Before:
                                            {{
                                                balanceText(
                                                    transaction.currency,
                                                    transaction.balanceBefore,
                                                )
                                            }}
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="filteredTransactions.length === 0">
                                    <td
                                        colspan="5"
                                        class="px-4 py-10 text-center text-sm text-slate-400"
                                    >
                                        No transaction ledger found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
