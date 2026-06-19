<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    ArrowDownLeft,
    ArrowUpRight,
    CreditCard,
    Eye,
    Plus,
    Search,
    WalletCards,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import MembershipCardPreview from '@/components/master-data/MembershipCardPreview.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

type CardStatus = 'active' | 'inactive' | 'blocked' | 'expired' | 'cancelled';

type CardBalance = {
    currency: string;
    balance: number;
};

type CardTransaction = {
    id: number;
    transactionNo: string;
    type: string;
    direction: 'credit' | 'debit';
    currency: string;
    amount: number;
    promotionAmount: number;
    balanceBefore: number;
    balanceAfter: number;
    transactedAt?: string | null;
};

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
    balances: CardBalance[];
    transactions: CardTransaction[];
};

const props = defineProps<{
    cards: CustomerCard[];
    filters?: {
        card?: number | null;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Membership Cards', href: '/membership-cards' },
];

const search = ref('');
const statusFilter = ref<'all' | CardStatus>('all');
const selectedCardId = ref<number | null>(
    props.filters?.card ?? props.cards[0]?.id ?? null,
);

const filteredCards = computed(() => {
    const term = search.value.trim().toLowerCase();

    return props.cards.filter((card) => {
        const matchesSearch =
            `${card.cardNo} ${card.cardName} ${card.customerName} ${card.customerPhone ?? ''}`
                .toLowerCase()
                .includes(term);
        const matchesStatus =
            statusFilter.value === 'all' || card.status === statusFilter.value;

        return matchesSearch && matchesStatus;
    });
});

const selectedCard = computed(
    () =>
        props.cards.find((card) => card.id === selectedCardId.value) ??
        filteredCards.value[0] ??
        null,
);

const totalCards = computed(() => props.cards.length);
const activeCards = computed(
    () => props.cards.filter((card) => card.status === 'active').length,
);
const totalBalanceUsd = computed(() =>
    props.cards.reduce((total, card) => {
        const usd = card.balances.find(
            (balance) => balance.currency.toUpperCase() === 'USD',
        );

        return total + Number(usd?.balance ?? 0);
    }, 0),
);

function selectCard(card: CustomerCard) {
    selectedCardId.value = card.id;
}

function money(balance: CardBalance) {
    if (balance.currency.toUpperCase() === 'KHR') {
        return `${Math.round(balance.balance).toLocaleString()} KHR`;
    }

    return `${balance.currency} ${Number(balance.balance).toLocaleString(
        undefined,
        {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        },
    )}`;
}

function transactionAmount(transaction: CardTransaction) {
    const sign = transaction.direction === 'credit' ? '+' : '-';
    const value = Number(transaction.amount + transaction.promotionAmount);

    if (transaction.currency.toUpperCase() === 'KHR') {
        return `${sign}${Math.round(value).toLocaleString()} KHR`;
    }

    return `${sign}${transaction.currency} ${value.toFixed(2)}`;
}

function statusClass(status: CardStatus) {
    const classes: Record<CardStatus, string> = {
        active: 'border-emerald-200 bg-emerald-50 text-emerald-700',
        inactive: 'border-slate-200 bg-slate-100 text-slate-600',
        blocked: 'border-red-200 bg-red-50 text-red-700',
        expired: 'border-amber-200 bg-amber-50 text-amber-700',
        cancelled: 'border-slate-300 bg-slate-100 text-slate-500',
    };

    return classes[status];
}

function goTopup(card: CustomerCard | null = selectedCard.value) {
    if (!card) return;

    router.get(`/membership-cards/${card.id}/topup`);
}

function goTransactions(card: CustomerCard | null = selectedCard.value) {
    if (!card) return;

    router.get(`/membership-cards/${card.id}/transactions`);
}
</script>

<template>
    <Head title="Membership Cards" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-[calc(100dvh-4rem)] w-full [scrollbar-gutter:stable] flex-col gap-6 overflow-y-scroll bg-[#f8fafc] p-4 text-slate-800 md:h-[calc(100dvh-5rem)] md:p-6 xl:p-8 2xl:p-10"
        >
            <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_440px]">
                <section class="flex flex-col gap-6">
                    <div
                        class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm"
                    >
                        <div
                            class="flex flex-col justify-between gap-4 lg:flex-row lg:items-center"
                        >
                            <div>
                                <h1
                                    class="text-2xl font-black tracking-tight text-[#2A4858]"
                                >
                                    Membership Directory
                                </h1>
                                <p class="mt-1 text-sm text-slate-500">
                                    Customer smart cards, balances, and ledger
                                    activity.
                                </p>
                            </div>

                            <Button
                                class="h-9 rounded-lg bg-[#007882] px-4 text-xs font-bold text-white hover:bg-[#006871]"
                                @click="router.get('/membership-cards/create')"
                            >
                                <Plus class="size-4" />
                                Issue New Card
                            </Button>
                        </div>

                        <div class="mt-5 grid gap-3 md:grid-cols-[1fr_180px]">
                            <div class="relative">
                                <Search
                                    class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"
                                />
                                <Input
                                    v-model="search"
                                    class="h-10 rounded-lg border-slate-200 bg-slate-50 pl-9 text-sm focus-visible:ring-[#007882]"
                                    placeholder="Search card holder, phone, or card no..."
                                />
                            </div>

                            <select
                                v-model="statusFilter"
                                class="h-10 rounded-lg border border-slate-200 bg-slate-50 px-3 text-xs font-bold text-slate-500 outline-none focus:border-[#007882]"
                            >
                                <option value="all">All Statuses</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="blocked">Blocked</option>
                                <option value="expired">Expired</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-3">
                        <div
                            class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm"
                        >
                            <p
                                class="text-[10px] font-black tracking-widest text-slate-400 uppercase"
                            >
                                Total Cards
                            </p>
                            <p class="mt-2 text-2xl font-black text-[#2A4858]">
                                {{ totalCards }}
                            </p>
                        </div>
                        <div
                            class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm"
                        >
                            <p
                                class="text-[10px] font-black tracking-widest text-slate-400 uppercase"
                            >
                                Active Cards
                            </p>
                            <p class="mt-2 text-2xl font-black text-[#007882]">
                                {{ activeCards }}
                            </p>
                        </div>
                        <div
                            class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm"
                        >
                            <p
                                class="text-[10px] font-black tracking-widest text-slate-400 uppercase"
                            >
                                USD Wallets
                            </p>
                            <p class="mt-2 text-2xl font-black text-[#2A4858]">
                                ${{ totalBalanceUsd.toFixed(2) }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"
                    >
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[900px] border-collapse">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th
                                            class="w-12 px-4 py-3 text-center text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                                        >
                                            #
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                                        >
                                            Card / Holder
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                                        >
                                            Phone
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                                        >
                                            Balances
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                                        >
                                            Status
                                        </th>
                                        <th
                                            class="px-4 py-3 text-right text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                                        >
                                            Action
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="(card, index) in filteredCards"
                                        :key="card.id"
                                        class="border-b border-slate-100 last:border-b-0 hover:bg-slate-50"
                                        :class="
                                            selectedCard?.id === card.id
                                                ? 'bg-[#23AA8F]/5'
                                                : ''
                                        "
                                    >
                                        <td
                                            class="px-4 py-3 text-center text-[10px] text-slate-400"
                                        >
                                            {{
                                                String(index + 1).padStart(
                                                    2,
                                                    '0',
                                                )
                                            }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <button
                                                type="button"
                                                class="text-left"
                                                @click="selectCard(card)"
                                            >
                                                <span
                                                    class="block font-mono text-xs font-black text-[#007882]"
                                                >
                                                    {{ card.cardNo }}
                                                </span>
                                                <span
                                                    class="block text-sm font-bold text-slate-700"
                                                >
                                                    {{ card.customerName }}
                                                </span>
                                                <span
                                                    class="block text-[11px] text-slate-400"
                                                >
                                                    {{ card.cardName }}
                                                </span>
                                            </button>
                                        </td>
                                        <td
                                            class="px-4 py-3 text-sm text-slate-500"
                                        >
                                            {{ card.customerPhone ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex flex-wrap gap-1.5">
                                                <span
                                                    v-for="balance in card.balances"
                                                    :key="`${card.id}-${balance.currency}`"
                                                    class="rounded bg-slate-100 px-2 py-1 text-[10px] font-black text-slate-600"
                                                >
                                                    {{ money(balance) }}
                                                </span>
                                                <span
                                                    v-if="
                                                        card.balances.length ===
                                                        0
                                                    "
                                                    class="text-xs text-slate-400"
                                                >
                                                    No balance
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span
                                                class="rounded border px-2 py-0.5 text-[10px] font-bold uppercase"
                                                :class="
                                                    statusClass(card.status)
                                                "
                                            >
                                                {{ card.status }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <div
                                                class="inline-flex items-center gap-1"
                                            >
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    class="h-8 rounded-lg text-xs font-bold"
                                                    @click="goTopup(card)"
                                                >
                                                    <Plus class="size-3.5" />
                                                    Topup
                                                </Button>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    class="size-8 rounded-lg text-slate-500 hover:text-[#007882]"
                                                    @click="
                                                        goTransactions(card)
                                                    "
                                                >
                                                    <Eye class="size-4" />
                                                    <span class="sr-only">
                                                        Ledger
                                                    </span>
                                                </Button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="filteredCards.length === 0">
                                        <td
                                            colspan="6"
                                            class="px-4 py-10 text-center text-sm text-slate-400"
                                        >
                                            No membership cards found.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <aside class="flex flex-col gap-6">
                    <div
                        class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm"
                    >
                        <div class="mb-4 flex items-center justify-between">
                            <span
                                class="text-[10px] font-black tracking-widest text-slate-400 uppercase"
                            >
                                Interactive Virtual Card
                            </span>
                            <span
                                class="rounded bg-teal-50 px-2 py-1 text-[10px] font-black text-[#007882]"
                            >
                                Click to Flip
                            </span>
                        </div>

                        <MembershipCardPreview
                            v-if="selectedCard"
                            :card-no="selectedCard.cardNo"
                            :card-name="selectedCard.cardName"
                            :customer-name="selectedCard.customerName"
                            :company-branch="
                                selectedCard.branchName ?? 'DM Group'
                            "
                            :issued-date="selectedCard.issuedDate"
                            :expired-date="selectedCard.expiredDate"
                            :remark="selectedCard.remark"
                            :status="selectedCard.status"
                        />
                        <div
                            v-else
                            class="py-16 text-center text-sm text-slate-400"
                        >
                            Select a card to preview.
                        </div>
                    </div>

                    <div
                        class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm"
                    >
                        <div class="mb-4 flex items-center justify-between">
                            <div>
                                <h2 class="text-base font-black text-[#2A4858]">
                                    Wallet Snapshot
                                </h2>
                                <p class="text-xs text-slate-400">
                                    Current card balances
                                </p>
                            </div>
                            <WalletCards class="size-5 text-[#007882]" />
                        </div>

                        <div v-if="selectedCard" class="space-y-2">
                            <div
                                v-for="balance in selectedCard.balances"
                                :key="balance.currency"
                                class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2"
                            >
                                <span
                                    class="font-mono text-xs font-black text-slate-500"
                                >
                                    {{ balance.currency }}
                                </span>
                                <span class="text-sm font-black text-[#2A4858]">
                                    {{ money(balance) }}
                                </span>
                            </div>
                            <p
                                v-if="selectedCard.balances.length === 0"
                                class="rounded-lg bg-slate-50 px-3 py-6 text-center text-xs text-slate-400"
                            >
                                No wallet balance yet.
                            </p>
                        </div>
                    </div>

                    <div
                        class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm"
                    >
                        <div class="mb-4 flex items-center justify-between">
                            <div>
                                <h2 class="text-base font-black text-[#2A4858]">
                                    Recent Ledger
                                </h2>
                                <p class="text-xs text-slate-400">
                                    Latest card activity
                                </p>
                            </div>
                            <Button
                                v-if="selectedCard"
                                variant="ghost"
                                size="sm"
                                class="h-8 rounded-lg text-xs font-bold text-[#007882]"
                                @click="goTransactions()"
                            >
                                View All
                            </Button>
                        </div>

                        <div v-if="selectedCard" class="space-y-2">
                            <div
                                v-for="transaction in selectedCard.transactions"
                                :key="transaction.id"
                                class="flex items-center justify-between rounded-lg border border-slate-100 p-3"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex size-8 items-center justify-center rounded-lg bg-slate-100"
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
                                            class="text-xs font-black text-slate-700"
                                        >
                                            {{ transaction.type }}
                                        </p>
                                        <p class="text-[10px] text-slate-400">
                                            {{
                                                transaction.transactedAt ?? '-'
                                            }}
                                        </p>
                                    </div>
                                </div>
                                <span
                                    class="text-xs font-black"
                                    :class="
                                        transaction.direction === 'credit'
                                            ? 'text-emerald-600'
                                            : 'text-rose-600'
                                    "
                                >
                                    {{ transactionAmount(transaction) }}
                                </span>
                            </div>
                            <p
                                v-if="selectedCard.transactions.length === 0"
                                class="rounded-lg bg-slate-50 px-3 py-8 text-center text-xs text-slate-400"
                            >
                                No transaction ledger recorded.
                            </p>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </AppLayout>
</template>
