<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    CreditCard,
    Plus,
    RotateCcw,
    Save,
    Search,
    WalletCards,
} from 'lucide-vue-next';
import { computed, reactive, ref } from 'vue';
import MasterDataTable from '@/components/master-data/MasterDataTable.vue';
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

type MembershipCardRecord = {
    id: number;
    customerName: string;
    customerPhone: string;
    cardNo: string;
    cardName: string;
    status: CardStatus;
    issuedDate: string;
    expiredDate: string;
    remark: string;
    balances: CardBalance[];
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Master Data' },
    { title: 'Membership Cards', href: '/master-data/membership-cards' },
];

const cards = ref<MembershipCardRecord[]>([
    {
        id: 1,
        customerName: 'Sok Phalla',
        customerPhone: '012 555 999',
        cardNo: 'MC-8820-9921',
        cardName: 'VIP Gold Pass',
        status: 'active',
        issuedDate: '2026-05-23',
        expiredDate: '2028-05-23',
        remark: 'Non-transferable. Present at checkout to redeem member privileges.',
        balances: [
            { currency: 'USD', balance: 13 },
            { currency: 'KHR', balance: 40000 },
        ],
    },
    {
        id: 2,
        customerName: 'Dara Kim',
        customerPhone: '010 333 222',
        cardNo: 'MC-1100-2200',
        cardName: 'Family Member',
        status: 'inactive',
        issuedDate: '2026-05-10',
        expiredDate: '2027-05-10',
        remark: 'Family account membership card.',
        balances: [{ currency: 'CNY', balance: 100 }],
    },
]);

const search = ref('');
const selectedId = ref<number | null>(cards.value[0]?.id ?? null);

const form = reactive({
    cardNo: '',
    cardName: '',
    customerName: '',
    customerPhone: '',
    issuedDate: '',
    expiredDate: '',
    status: 'active' as CardStatus,
    remark: '',
});

const balanceRows = ref<CardBalance[]>([]);

const selectedCard = computed(
    () => cards.value.find((card) => card.id === selectedId.value) ?? null,
);

const filteredCards = computed(() => {
    const term = search.value.trim().toLowerCase();

    if (!term) {
        return cards.value;
    }

    return cards.value.filter((card) =>
        [
            card.customerName,
            card.customerPhone,
            card.cardNo,
            card.cardName,
            card.status,
            ...card.balances.map((balance) => balance.currency),
        ]
            .join(' ')
            .toLowerCase()
            .includes(term),
    );
});

function loadCard(card: MembershipCardRecord) {
    selectedId.value = card.id;
    form.cardNo = card.cardNo;
    form.cardName = card.cardName;
    form.customerName = card.customerName;
    form.customerPhone = card.customerPhone;
    form.issuedDate = card.issuedDate;
    form.expiredDate = card.expiredDate;
    form.status = card.status;
    form.remark = card.remark;
    balanceRows.value = card.balances.map((balance) => ({ ...balance }));
}

function newCard() {
    selectedId.value = null;
    form.cardNo = `MC-${Date.now().toString().slice(-4)}-${Math.floor(
        Math.random() * 9000 + 1000,
    )}`;
    form.cardName = 'New Member Pass';
    form.customerName = '';
    form.customerPhone = '';
    form.issuedDate = new Date().toISOString().slice(0, 10);
    form.expiredDate = '';
    form.status = 'active';
    form.remark = '';
    balanceRows.value = [{ currency: 'USD', balance: 0 }];
}

function resetForm() {
    if (selectedCard.value) {
        loadCard(selectedCard.value);
    } else {
        newCard();
    }
}

function saveCard() {
    const payload: MembershipCardRecord = {
        id: selectedId.value ?? Date.now(),
        customerName: form.customerName || 'Guest Member',
        customerPhone: form.customerPhone,
        cardNo: form.cardNo,
        cardName: form.cardName,
        status: form.status,
        issuedDate: form.issuedDate,
        expiredDate: form.expiredDate,
        remark: form.remark,
        balances: balanceRows.value
            .filter((balance) => balance.currency.trim())
            .map((balance) => ({
                currency: balance.currency.trim().toUpperCase(),
                balance: Number(balance.balance || 0),
            })),
    };

    const existingIndex = cards.value.findIndex(
        (card) => card.id === payload.id,
    );

    if (existingIndex >= 0) {
        cards.value[existingIndex] = payload;
    } else {
        cards.value.unshift(payload);
    }

    loadCard(payload);
}

function addBalanceRow() {
    balanceRows.value.push({ currency: '', balance: 0 });
}

function removeBalanceRow(index: number) {
    balanceRows.value.splice(index, 1);
}

function balanceSummary(card: MembershipCardRecord) {
    return card.balances
        .map(
            (balance) =>
                `${balance.currency} ${balance.balance.toLocaleString()}`,
        )
        .join(' / ');
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

if (cards.value[0]) {
    loadCard(cards.value[0]);
} else {
    newCard();
}
</script>

<template>
    <Head title="Membership Cards" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #actions>
            <Button
                class="h-9 rounded-lg bg-[#007882] px-4 text-xs font-bold text-white hover:bg-[#006871]"
                @click="newCard"
            >
                <Plus class="size-4" />
                New Card
            </Button>
        </template>

        <div
            class="flex h-[calc(100dvh-4rem)] w-full [scrollbar-gutter:stable] flex-col gap-6 overflow-y-scroll bg-[#f8fafc] p-4 text-slate-800 md:h-[calc(100dvh-5rem)] md:p-6 xl:p-8 2xl:p-10"
        >
            <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_460px]">
                <div class="space-y-6">
                    <div class="relative w-full max-w-sm">
                        <Search
                            class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"
                        />
                        <Input
                            v-model="search"
                            placeholder="Search cards..."
                            class="h-9 rounded-lg border-slate-200 bg-white pl-9 text-xs focus-visible:ring-[#007882]"
                        />
                    </div>

                    <MasterDataTable
                        :rows="filteredCards"
                        empty-text="No membership cards found."
                    >
                        <template #head>
                            <th
                                class="w-12 px-4 py-3 text-center text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                            >
                                #
                            </th>
                            <th
                                class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                            >
                                Card
                            </th>
                            <th
                                class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                            >
                                Customer
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
                                class="w-20 px-4 py-3 text-center text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                            >
                                Action
                            </th>
                        </template>
                        <template #row="{ row, index }">
                            <td
                                class="px-4 py-3 text-center text-[10px] text-slate-400"
                            >
                                {{ String(index + 1).padStart(2, '0') }}
                            </td>
                            <td class="px-4 py-3">
                                <div
                                    class="font-mono text-xs font-bold text-[#007882]"
                                >
                                    {{ row.cardNo }}
                                </div>
                                <div class="text-xs text-slate-500">
                                    {{ row.cardName }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm font-bold text-slate-700">
                                    {{ row.customerName }}
                                </div>
                                <div class="text-xs text-slate-400">
                                    {{ row.customerPhone || '-' }}
                                </div>
                            </td>
                            <td
                                class="px-4 py-3 text-xs font-bold text-slate-600"
                            >
                                {{ balanceSummary(row) || '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="rounded border px-2 py-0.5 text-[10px] font-bold uppercase"
                                    :class="statusClass(row.status)"
                                >
                                    {{ row.status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    class="mx-auto size-8 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-[#007882]"
                                    title="Edit card"
                                    @click="loadCard(row)"
                                >
                                    <CreditCard class="size-4" />
                                    <span class="sr-only">Edit card</span>
                                </Button>
                            </td>
                        </template>
                    </MasterDataTable>

                    <div
                        class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm"
                    >
                        <div class="mb-5 flex items-center justify-between">
                            <div>
                                <h2 class="text-lg font-black text-[#2A4858]">
                                    Card Properties
                                </h2>
                                <p
                                    class="text-[10px] font-semibold tracking-widest text-slate-400 uppercase"
                                >
                                    Table: membership_cards
                                </p>
                            </div>
                            <WalletCards class="size-5 text-[#007882]" />
                        </div>

                        <form class="space-y-4" @submit.prevent="saveCard">
                            <div class="grid gap-4 md:grid-cols-2">
                                <label class="block">
                                    <span
                                        class="text-[10px] font-black tracking-widest text-slate-400 uppercase"
                                    >
                                        Card Number
                                    </span>
                                    <Input
                                        v-model="form.cardNo"
                                        required
                                        class="mt-1 font-mono text-sm font-bold focus-visible:ring-[#007882]"
                                    />
                                </label>
                                <label class="block">
                                    <span
                                        class="text-[10px] font-black tracking-widest text-slate-400 uppercase"
                                    >
                                        Card Tier Name
                                    </span>
                                    <Input
                                        v-model="form.cardName"
                                        required
                                        class="mt-1 text-sm font-bold focus-visible:ring-[#007882]"
                                    />
                                </label>
                            </div>

                            <div
                                class="rounded-lg border border-dashed border-slate-200 bg-slate-50 p-4"
                            >
                                <span
                                    class="mb-3 block text-[9px] font-black tracking-widest text-slate-400 uppercase"
                                >
                                    Customer Relation
                                </span>
                                <div class="grid gap-3 md:grid-cols-2">
                                    <Input
                                        v-model="form.customerName"
                                        placeholder="Customer name"
                                        class="bg-white text-sm font-bold focus-visible:ring-[#007882]"
                                    />
                                    <Input
                                        v-model="form.customerPhone"
                                        placeholder="Phone number"
                                        class="bg-white text-sm focus-visible:ring-[#007882]"
                                    />
                                </div>
                            </div>

                            <div class="grid gap-4 md:grid-cols-3">
                                <label class="block">
                                    <span
                                        class="text-[10px] font-black tracking-widest text-slate-400 uppercase"
                                    >
                                        Issued Date
                                    </span>
                                    <Input
                                        v-model="form.issuedDate"
                                        type="date"
                                        class="mt-1 text-xs font-bold focus-visible:ring-[#007882]"
                                    />
                                </label>
                                <label class="block">
                                    <span
                                        class="text-[10px] font-black tracking-widest text-[#007882] uppercase"
                                    >
                                        Expiry Date
                                    </span>
                                    <Input
                                        v-model="form.expiredDate"
                                        type="date"
                                        class="mt-1 border-[#007882]/20 bg-amber-50 text-xs font-bold text-amber-800 focus-visible:ring-[#007882]"
                                    />
                                </label>
                                <label class="block">
                                    <span
                                        class="text-[10px] font-black tracking-widest text-slate-400 uppercase"
                                    >
                                        Status
                                    </span>
                                    <select
                                        v-model="form.status"
                                        class="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-xs font-bold shadow-xs outline-none focus:border-[#007882]"
                                    >
                                        <option value="active">Active</option>
                                        <option value="inactive">
                                            Inactive
                                        </option>
                                        <option value="blocked">Blocked</option>
                                        <option value="expired">Expired</option>
                                        <option value="cancelled">
                                            Cancelled
                                        </option>
                                    </select>
                                </label>
                            </div>

                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span
                                        class="text-[10px] font-black tracking-widest text-slate-400 uppercase"
                                    >
                                        Currency Balances
                                    </span>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        class="h-8 rounded-lg text-xs font-bold"
                                        @click="addBalanceRow"
                                    >
                                        <Plus class="size-3.5" />
                                        Currency
                                    </Button>
                                </div>
                                <div
                                    v-for="(balance, index) in balanceRows"
                                    :key="index"
                                    class="grid grid-cols-[96px_minmax(0,1fr)_36px] gap-2"
                                >
                                    <Input
                                        v-model="balance.currency"
                                        maxlength="3"
                                        placeholder="USD"
                                        class="font-mono text-xs font-bold uppercase focus-visible:ring-[#007882]"
                                    />
                                    <Input
                                        v-model.number="balance.balance"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        class="text-xs font-bold focus-visible:ring-[#007882]"
                                    />
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="icon"
                                        class="size-9 rounded-lg text-slate-400 hover:text-rose-600"
                                        @click="removeBalanceRow(index)"
                                    >
                                        <span class="text-lg leading-none"
                                            >×</span
                                        >
                                        <span class="sr-only"
                                            >Remove currency</span
                                        >
                                    </Button>
                                </div>
                            </div>

                            <label class="block">
                                <span
                                    class="text-[10px] font-black tracking-widest text-slate-400 uppercase"
                                >
                                    Remarks / Expiry Policies
                                </span>
                                <textarea
                                    v-model="form.remark"
                                    rows="3"
                                    class="mt-1 w-full rounded-md border border-input bg-transparent px-3 py-2 text-xs shadow-xs outline-none focus:border-[#007882]"
                                    placeholder="Card terms of use..."
                                ></textarea>
                            </label>

                            <div class="flex justify-end gap-3 border-t pt-4">
                                <Button
                                    type="button"
                                    variant="outline"
                                    class="rounded-lg text-xs font-bold text-slate-500"
                                    @click="resetForm"
                                >
                                    <RotateCcw class="size-4" />
                                    Reset
                                </Button>
                                <Button
                                    type="submit"
                                    class="rounded-lg bg-[#007882] text-xs font-bold text-white hover:bg-[#006871]"
                                >
                                    <Save class="size-4" />
                                    Save Card
                                </Button>
                            </div>
                        </form>
                    </div>
                </div>

                <div
                    class="h-fit rounded-lg border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <div class="mb-5 text-center">
                        <span
                            class="mb-1 block text-[10px] font-black tracking-widest text-slate-400 uppercase"
                        >
                            Interactive Pass Preview
                        </span>
                        <p class="text-xs font-medium text-slate-500">
                            Click or tap the card to flip.
                        </p>
                    </div>

                    <MembershipCardPreview
                        :card-no="form.cardNo"
                        :card-name="form.cardName"
                        :customer-name="form.customerName"
                        company-branch="DM Group / Central Branch"
                        :issued-date="form.issuedDate"
                        :expired-date="form.expiredDate"
                        :remark="form.remark"
                        :status="form.status"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
