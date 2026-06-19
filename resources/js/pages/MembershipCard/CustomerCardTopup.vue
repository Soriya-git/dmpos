<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Banknote,
    CheckCircle2,
    Gift,
    Plus,
    WalletCards,
} from 'lucide-vue-next';
import { computed, watch } from 'vue';
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
};

const props = defineProps<{
    card: CustomerCard;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Membership Cards', href: '/membership-cards' },
    { title: 'Topup' },
];

const availableCurrencies = computed(() => {
    const existing = props.card.balances.map((balance) =>
        balance.currency.toUpperCase(),
    );

    return Array.from(new Set([...existing, 'USD', 'KHR']));
});

const form = useForm({
    currency: availableCurrencies.value[0] ?? 'USD',
    amount: '',
    promotion_amount: '',
    promotion_name: '',
    note: '',
});

const selectedBalance = computed(
    () =>
        props.card.balances.find(
            (balance) =>
                balance.currency.toUpperCase() === form.currency.toUpperCase(),
        ) ?? null,
);

const amountNumber = computed(() => Number(form.amount || 0));
const promotionNumber = computed(() => Number(form.promotion_amount || 0));
const totalCredit = computed(() => amountNumber.value + promotionNumber.value);
const balanceAfter = computed(
    () => Number(selectedBalance.value?.balance ?? 0) + totalCredit.value,
);

watch(
    () => form.promotion_amount,
    (value) => {
        if (Number(value || 0) <= 0) {
            form.promotion_name = '';
        }
    },
);

function money(currency: string, value: number) {
    if (currency.toUpperCase() === 'KHR') {
        return `${Math.round(value).toLocaleString()} KHR`;
    }

    return `${currency.toUpperCase()} ${Number(value).toLocaleString(
        undefined,
        {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        },
    )}`;
}

function setQuickAmount(value: number) {
    form.amount = String(value);
}

function submitTopup() {
    form.currency = form.currency.toUpperCase();

    form.post(`/membership-cards/${props.card.id}/topup`, {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Topup Membership Card" />

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
                        Topup Membership Card
                    </h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Recharge {{ card.customerName }}'s card with optional
                        promotion credit.
                    </p>
                </div>

                <div
                    class="flex items-center gap-2 rounded-lg border border-[#007882]/15 bg-[#007882]/5 px-3 py-2 text-xs font-black text-[#007882]"
                >
                    <WalletCards class="size-4" />
                    {{ card.cardNo }}
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-[420px_minmax(0,1fr)]">
                <section class="flex flex-col gap-5">
                    <div
                        class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm"
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
                    </div>

                    <div
                        class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm"
                    >
                        <div class="mb-4 flex items-center justify-between">
                            <div>
                                <h2 class="text-base font-black text-[#2A4858]">
                                    Current Balances
                                </h2>
                                <p class="text-xs text-slate-400">
                                    Stored by card and currency
                                </p>
                            </div>
                            <Banknote class="size-5 text-[#007882]" />
                        </div>

                        <div class="space-y-2">
                            <div
                                v-for="balance in card.balances"
                                :key="balance.currency"
                                class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2"
                            >
                                <span
                                    class="font-mono text-xs font-black text-slate-500"
                                >
                                    {{ balance.currency }}
                                </span>
                                <span class="text-sm font-black text-[#2A4858]">
                                    {{
                                        money(balance.currency, balance.balance)
                                    }}
                                </span>
                            </div>
                            <p
                                v-if="card.balances.length === 0"
                                class="rounded-lg bg-slate-50 px-3 py-6 text-center text-xs text-slate-400"
                            >
                                No wallet balance yet. The first topup will
                                create the selected currency balance.
                            </p>
                        </div>
                    </div>
                </section>

                <section
                    class="h-fit rounded-lg border border-slate-200 bg-white shadow-sm"
                >
                    <div class="border-b border-slate-100 p-5">
                        <h2 class="text-lg font-black text-[#2A4858]">
                            Recharge Detail
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Promotion amount is added to the customer's
                            spendable balance.
                        </p>
                    </div>

                    <form class="space-y-5 p-5" @submit.prevent="submitTopup">
                        <div class="grid gap-4 md:grid-cols-2">
                            <label class="block">
                                <span
                                    class="text-[10px] font-black tracking-widest text-slate-400 uppercase"
                                >
                                    Currency
                                </span>
                                <select
                                    v-model="form.currency"
                                    class="mt-1 h-10 w-full rounded-md border border-input/60 bg-transparent px-3 py-1 font-mono text-sm font-bold outline-none focus:border-[#007882] focus:ring-3 focus:ring-[#007882]/15"
                                >
                                    <option
                                        v-for="currency in availableCurrencies"
                                        :key="currency"
                                        :value="currency"
                                    >
                                        {{ currency }}
                                    </option>
                                </select>
                                <p
                                    v-if="form.errors.currency"
                                    class="mt-1 text-xs text-rose-600"
                                >
                                    {{ form.errors.currency }}
                                </p>
                            </label>

                            <label class="block">
                                <span
                                    class="text-[10px] font-black tracking-widest text-slate-400 uppercase"
                                >
                                    Recharge Amount
                                </span>
                                <Input
                                    v-model="form.amount"
                                    type="number"
                                    min="0.01"
                                    step="0.01"
                                    required
                                    class="mt-1 h-10 text-sm font-bold focus-visible:ring-[#007882]"
                                    placeholder="0.00"
                                />
                                <p
                                    v-if="form.errors.amount"
                                    class="mt-1 text-xs text-rose-600"
                                >
                                    {{ form.errors.amount }}
                                </p>
                            </label>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <Button
                                v-for="amount in [5, 10, 20, 50, 100]"
                                :key="amount"
                                type="button"
                                variant="outline"
                                class="h-8 rounded-lg text-xs font-bold"
                                @click="setQuickAmount(amount)"
                            >
                                <Plus class="size-3.5" />
                                {{ amount }}
                            </Button>
                        </div>

                        <div
                            class="rounded-lg border border-[#23AA8F]/20 bg-[#23AA8F]/5 p-4"
                        >
                            <div class="mb-3 flex items-center gap-2">
                                <Gift class="size-4 text-[#007882]" />
                                <span
                                    class="text-[10px] font-black tracking-widest text-[#007882] uppercase"
                                >
                                    Promotion Credit
                                </span>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <label class="block">
                                    <span
                                        class="text-[10px] font-black tracking-widest text-slate-400 uppercase"
                                    >
                                        Promotion Amount
                                    </span>
                                    <Input
                                        v-model="form.promotion_amount"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        class="mt-1 bg-white text-sm font-bold focus-visible:ring-[#007882]"
                                        placeholder="0.00"
                                    />
                                    <p
                                        v-if="form.errors.promotion_amount"
                                        class="mt-1 text-xs text-rose-600"
                                    >
                                        {{ form.errors.promotion_amount }}
                                    </p>
                                </label>

                                <label class="block">
                                    <span
                                        class="text-[10px] font-black tracking-widest text-slate-400 uppercase"
                                    >
                                        Promotion Name
                                    </span>
                                    <Input
                                        v-model="form.promotion_name"
                                        :disabled="promotionNumber <= 0"
                                        class="mt-1 bg-white text-sm font-bold focus-visible:ring-[#007882]"
                                        placeholder="Holiday bonus"
                                    />
                                    <p
                                        v-if="form.errors.promotion_name"
                                        class="mt-1 text-xs text-rose-600"
                                    >
                                        {{ form.errors.promotion_name }}
                                    </p>
                                </label>
                            </div>
                        </div>

                        <label class="block">
                            <span
                                class="text-[10px] font-black tracking-widest text-slate-400 uppercase"
                            >
                                Note
                            </span>
                            <textarea
                                v-model="form.note"
                                rows="3"
                                class="mt-1 w-full rounded-md border border-input/60 bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus:border-[#007882] focus:ring-3 focus:ring-[#007882]/15"
                                placeholder="Optional cashier note..."
                            ></textarea>
                            <p
                                v-if="form.errors.note"
                                class="mt-1 text-xs text-rose-600"
                            >
                                {{ form.errors.note }}
                            </p>
                        </label>

                        <div
                            class="grid gap-3 rounded-lg border border-slate-200 bg-slate-50 p-4 md:grid-cols-3"
                        >
                            <div>
                                <p
                                    class="text-[10px] font-black tracking-widest text-slate-400 uppercase"
                                >
                                    Before
                                </p>
                                <p class="mt-1 font-black text-slate-700">
                                    {{
                                        money(
                                            form.currency,
                                            selectedBalance?.balance ?? 0,
                                        )
                                    }}
                                </p>
                            </div>
                            <div>
                                <p
                                    class="text-[10px] font-black tracking-widest text-slate-400 uppercase"
                                >
                                    Total Credit
                                </p>
                                <p class="mt-1 font-black text-[#007882]">
                                    {{ money(form.currency, totalCredit) }}
                                </p>
                            </div>
                            <div>
                                <p
                                    class="text-[10px] font-black tracking-widest text-slate-400 uppercase"
                                >
                                    New Balance
                                </p>
                                <p class="mt-1 font-black text-[#2A4858]">
                                    {{ money(form.currency, balanceAfter) }}
                                </p>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 border-t pt-5">
                            <Button
                                type="button"
                                variant="outline"
                                class="rounded-lg text-xs font-bold text-slate-500"
                                @click="router.get('/membership-cards')"
                            >
                                Cancel
                            </Button>
                            <Button
                                type="submit"
                                class="rounded-lg bg-[#007882] text-xs font-bold text-white hover:bg-[#006871]"
                                :disabled="form.processing || amountNumber <= 0"
                            >
                                <CheckCircle2 class="size-4" />
                                Receive Topup
                            </Button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
