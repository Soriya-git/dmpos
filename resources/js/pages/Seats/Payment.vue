<script setup lang="ts">
import {
    Banknote,
    Building2,
    Clock3,
    Coins,
    DollarSign,
    FileText,
    LoaderCircle,
    QrCode,
    Smartphone,
    X,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import type { Component } from 'vue';

type PaymentMethod = {
    id: string;
    label: string;
    type: 'cash' | 'bank' | 'pay_later';
    currency: 'USD' | 'KHR';
    icon: Component;
};

const props = defineProps<{
    open: boolean;
    subtotal: number;
    discountAmount: number;
    taxAmount: number;
    finalAmount: number;
    exchangeRate?: number;
}>();

const emit = defineEmits<{
    close: [];
    confirm: [
        payload: {
            method: string;
            currency: 'USD' | 'KHR';
            receivedAmount: number;
            operationStatus: 'invoice' | 'invoice_receipt_done';
        },
    ];
}>();

const exchangeRate = computed(() => Number(props.exchangeRate ?? 4100));
const selectedMethodId = ref('cash-khr');
const receivedAmount = ref('');
const processing = ref(false);

const methods: PaymentMethod[] = [
    {
        id: 'cash-khr',
        label: 'Cash KHR',
        type: 'cash',
        currency: 'KHR',
        icon: Banknote,
    },
    {
        id: 'cash-usd',
        label: 'Cash USD',
        type: 'cash',
        currency: 'USD',
        icon: DollarSign,
    },
    {
        id: 'ebanking-khr',
        label: 'E-Banking KHR',
        type: 'bank',
        currency: 'KHR',
        icon: Smartphone,
    },
    {
        id: 'ebanking-usd',
        label: 'E-Banking USD',
        type: 'bank',
        currency: 'USD',
        icon: Building2,
    },
    {
        id: 'pay-later',
        label: 'Pay Later',
        type: 'pay_later',
        currency: 'USD',
        icon: Clock3,
    },
];

const selectedMethod = computed(() => {
    return (
        methods.find((method) => method.id === selectedMethodId.value) ??
        methods[0]
    );
});

const totalInSelectedCurrency = computed(() => {
    if (selectedMethod.value.currency === 'KHR') {
        return props.finalAmount * exchangeRate.value;
    }

    return props.finalAmount;
});

const receivedNumeric = computed(() => Number(receivedAmount.value || 0));

const changeDue = computed(() => {
    if (selectedMethod.value.type !== 'cash') return 0;

    return Math.max(0, receivedNumeric.value - totalInSelectedCurrency.value);
});

const operationStatus = computed<'invoice' | 'invoice_receipt_done'>(() => {
    return selectedMethod.value.type === 'pay_later'
        ? 'invoice'
        : 'invoice_receipt_done';
});

const operationStatusLabel = computed(() => {
    return operationStatus.value === 'invoice'
        ? 'Invoice'
        : 'Invoice and Receipt Done';
});

const mainActionLabel = computed(() => {
    if (selectedMethod.value.type === 'pay_later') return 'Pay Later';
    if (selectedMethod.value.type === 'bank') return 'Confirm Bank Receipt';

    return 'Receive Payment';
});

const quickAmounts = computed(() => {
    return selectedMethod.value.currency === 'USD'
        ? [1, 5, 10]
        : [1000, 5000, 10000];
});

const canSubmit = computed(() => {
    if (selectedMethod.value.type !== 'cash') return true;

    return receivedNumeric.value >= totalInSelectedCurrency.value;
});

function moneyUsd(value: number) {
    return `$${Number(value ?? 0).toFixed(2)}`;
}

function moneyKhr(value: number) {
    return `${Math.round(Number(value ?? 0)).toLocaleString()} KHR`;
}

function displayCurrencyAmount(value: number) {
    return selectedMethod.value.currency === 'USD'
        ? Number(value).toFixed(2)
        : Math.round(Number(value)).toLocaleString();
}

function selectMethod(method: PaymentMethod) {
    selectedMethodId.value = method.id;
    receivedAmount.value = '';
}

function addAmount(amount: number) {
    receivedAmount.value = String(receivedNumeric.value + amount);
}

function clearAmount() {
    receivedAmount.value = '';
}

function confirmPayment() {
    if (!canSubmit.value || processing.value) return;

    processing.value = true;

    emit('confirm', {
        method: selectedMethod.value.id,
        currency: selectedMethod.value.currency,
        receivedAmount: receivedNumeric.value,
        operationStatus: operationStatus.value,
    });

    processing.value = false;
}
</script>

<template>
    <div
        v-if="open"
        class="fixed inset-0 z-[80] flex items-center justify-center bg-[#2A4858]/20 p-4 backdrop-blur-sm"
    >
        <div
            class="flex h-auto max-h-[92vh] w-full max-w-[1040px] flex-col overflow-hidden rounded-3xl bg-white shadow-2xl md:max-h-[900px] md:flex-row 2xl:max-h-[900px]"
        >
            <section
                class="flex-1 overflow-y-auto border-r border-gray-100 p-6 md:p-8"
            >
                <div class="mb-6 flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-black text-[#2A4858]">
                            Payment Process
                        </h2>
                        <p class="mt-1 text-sm font-medium text-gray-400">
                            Please select a payment method to continue
                        </p>
                    </div>

                    <button
                        type="button"
                        class="flex h-9 w-9 items-center justify-center rounded-xl border border-gray-100 text-gray-400 transition hover:bg-gray-50 hover:text-[#2A4858]"
                        @click="emit('close')"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <div
                    class="relative mb-6 overflow-hidden rounded-2xl bg-[#2A4858] p-6 text-white"
                >
                    <div class="relative z-10">
                        <span
                            class="text-xs font-bold tracking-widest uppercase opacity-60"
                        >
                            Total amount to pay
                        </span>
                        <div class="mt-1 flex flex-wrap items-baseline gap-2">
                            <h1 class="text-4xl font-black">
                                {{ moneyUsd(finalAmount) }}
                            </h1>
                            <span class="text-lg font-bold opacity-60">
                                / {{ moneyKhr(finalAmount * exchangeRate) }}
                            </span>
                        </div>
                    </div>
                    <div
                        class="absolute -right-4 -bottom-4 h-24 w-24 rounded-full bg-white/10 blur-2xl"
                    ></div>
                </div>

                <div
                    class="mb-6 grid grid-cols-3 gap-2 rounded-2xl bg-gray-50 p-3"
                >
                    <div>
                        <p
                            class="text-[9px] font-black tracking-widest text-gray-400 uppercase"
                        >
                            Subtotal
                        </p>
                        <p class="mt-1 text-xs font-black text-[#2A4858]">
                            {{ moneyUsd(subtotal) }}
                        </p>
                    </div>
                    <div>
                        <p
                            class="text-[9px] font-black tracking-widest text-gray-400 uppercase"
                        >
                            Discount
                        </p>
                        <p class="mt-1 text-xs font-black text-[#2A4858]">
                            {{ moneyUsd(discountAmount) }}
                        </p>
                    </div>
                    <div>
                        <p
                            class="text-[9px] font-black tracking-widest text-gray-400 uppercase"
                        >
                            Tax
                        </p>
                        <p class="mt-1 text-xs font-black text-[#2A4858]">
                            {{ moneyUsd(taxAmount) }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 lg:grid-cols-3">
                    <button
                        v-for="method in methods"
                        :key="method.id"
                        type="button"
                        class="group flex flex-col items-center gap-2 rounded-2xl border-2 p-4 transition"
                        :class="
                            selectedMethodId === method.id
                                ? 'border-[#23AA8F] bg-teal-50'
                                : 'border-transparent bg-gray-50 hover:border-[#23AA8F]/30'
                        "
                        @click="selectMethod(method)"
                    >
                        <span
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-white shadow-sm transition-transform group-hover:scale-110"
                        >
                            <component
                                :is="method.icon"
                                class="h-5 w-5"
                                :class="
                                    method.type === 'pay_later'
                                        ? 'text-orange-400'
                                        : 'text-[#23AA8F]'
                                "
                            />
                        </span>
                        <span
                            class="text-[10px] font-black text-[#2A4858] uppercase"
                        >
                            {{ method.label }}
                        </span>
                    </button>
                </div>
            </section>

            <section
                class="flex w-full flex-col bg-gray-50/50 p-6 md:w-[400px] md:p-8"
            >
                <div
                    v-if="selectedMethod.type === 'cash'"
                    class="flex flex-1 flex-col"
                >
                    <div class="mb-6">
                        <label
                            class="mb-2 block text-[10px] font-black tracking-widest text-gray-400 uppercase"
                        >
                            Receive Amount
                        </label>
                        <div class="relative">
                            <span
                                class="absolute top-1/2 left-4 -translate-y-1/2 text-xl font-black text-[#2A4858]"
                            >
                                {{ selectedMethod.currency }}
                            </span>
                            <input
                                v-model="receivedAmount"
                                type="number"
                                placeholder="0.00"
                                class="w-full rounded-2xl border-2 border-gray-100 bg-white py-4 pr-4 pl-16 text-right text-2xl font-black text-[#2A4858] transition-all outline-none focus:border-[#23AA8F]"
                            />
                        </div>
                    </div>

                    <div
                        class="mb-8 rounded-2xl border border-[#86D780] bg-[#86D780]/20 p-5"
                    >
                        <div class="mb-1 flex items-center justify-between">
                            <span
                                class="text-[10px] font-bold tracking-wider text-[#2A4858] uppercase opacity-60"
                            >
                                Change Due
                            </span>
                            <Coins class="h-3.5 w-3.5 text-[#23AA8F]" />
                        </div>
                        <div class="flex items-baseline justify-between">
                            <h2 class="text-3xl font-black text-[#2A4858]">
                                {{ displayCurrencyAmount(changeDue) }}
                            </h2>
                            <span class="font-bold text-[#2A4858] opacity-60">
                                {{ selectedMethod.currency }}
                            </span>
                        </div>
                    </div>

                    <div class="mb-6 grid grid-cols-3 gap-2">
                        <button
                            v-for="amount in quickAmounts"
                            :key="amount"
                            type="button"
                            class="rounded-xl border border-gray-100 bg-white py-3 text-xs font-bold text-[#2A4858] shadow-sm transition active:scale-95"
                            @click="addAmount(amount)"
                        >
                            +{{ displayCurrencyAmount(amount) }}
                        </button>
                        <button
                            type="button"
                            class="col-span-3 py-2 text-xs font-bold text-red-400 hover:text-red-600"
                            @click="clearAmount"
                        >
                            Clear Amount
                        </button>
                    </div>
                </div>

                <div
                    v-else-if="selectedMethod.type === 'bank'"
                    class="flex flex-1 flex-col items-center justify-center text-center"
                >
                    <div
                        class="mb-4 flex h-24 w-24 items-center justify-center rounded-3xl bg-white shadow-inner"
                    >
                        <QrCode class="h-11 w-11 text-[#23AA8F]" />
                    </div>
                    <p class="text-sm font-bold text-[#2A4858]">
                        Waiting for E-Banking Scan
                    </p>
                    <p class="mt-1 text-xs text-gray-400">
                        Status: Pending connection
                    </p>
                </div>

                <div
                    v-else
                    class="flex flex-1 flex-col items-center justify-center text-center"
                >
                    <div
                        class="mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-orange-50"
                    >
                        <FileText class="h-9 w-9 text-orange-400" />
                    </div>
                    <h3 class="text-lg font-black text-[#2A4858]">
                        Deferred Payment
                    </h3>
                    <p class="mt-2 px-6 text-xs text-gray-500">
                        The order will stay at invoice status and can be paid
                        later.
                    </p>
                </div>

                <div
                    class="mb-4 rounded-2xl border border-gray-100 bg-white p-4"
                >
                    <p
                        class="text-[10px] font-black tracking-widest text-gray-400 uppercase"
                    >
                        Operation Status
                    </p>
                    <p
                        class="mt-1 text-sm font-black"
                        :class="
                            operationStatus === 'invoice'
                                ? 'text-orange-500'
                                : 'text-[#007882]'
                        "
                    >
                        {{ operationStatusLabel }}
                    </p>
                </div>

                <div class="mt-auto space-y-3">
                    <button
                        type="button"
                        class="w-full rounded-2xl py-4 text-sm font-black tracking-widest text-white uppercase shadow-lg transition-all hover:-translate-y-0.5 disabled:cursor-not-allowed disabled:opacity-60"
                        :class="
                            selectedMethod.type === 'pay_later'
                                ? 'bg-orange-400 shadow-orange-400/20 hover:bg-orange-500'
                                : 'bg-[#23AA8F] shadow-[#23AA8F]/20 hover:bg-[#007882]'
                        "
                        :disabled="!canSubmit || processing"
                        @click="confirmPayment"
                    >
                        <span
                            class="inline-flex items-center justify-center gap-2"
                        >
                            <LoaderCircle
                                v-if="processing"
                                class="h-4 w-4 animate-spin"
                            />
                            {{ mainActionLabel }}
                        </span>
                    </button>

                    <button
                        type="button"
                        class="w-full rounded-2xl border-2 border-gray-100 bg-white py-4 text-sm font-black tracking-widest text-[#2A4858] uppercase transition-all hover:bg-gray-50"
                        @click="emit('close')"
                    >
                        Cancel
                    </button>
                </div>
            </section>
        </div>
    </div>
</template>
