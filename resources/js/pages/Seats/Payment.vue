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
    paymentMethodId?: number | null;
};

type PaymentMethodOption = {
    id: number;
    code?: string | null;
    label: string;
    type: 'cash' | 'bank';
    currency: 'USD' | 'KHR';
};

const props = defineProps<{
    open: boolean;
    subtotal: number;
    discountAmount: number;
    taxAmount: number;
    finalAmount: number;
    exchangeRate?: number;
    allowPayLater?: boolean;
    paymentMethods?: PaymentMethodOption[];
}>();

const emit = defineEmits<{
    close: [];
    confirm: [
        payload: {
            changeKhrAmount: number;
            changeUsdAmount: number;
            method: string;
            paymentMethodId?: number | null;
            currency: 'USD' | 'KHR';
            receivedAmount: number;
            operationStatus: 'invoice' | 'invoice_receipt_done';
        },
    ];
}>();

const exchangeRate = computed(() => Number(props.exchangeRate ?? 4100));
const selectedMethodId = ref('cash-khr');
const receivedAmount = ref('');
const changeUsdAmount = ref('');
const changeKhrAmount = ref('');
const activeChangeCurrency = ref<'USD' | 'KHR'>('KHR');
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

function methodKey(
    method: Pick<PaymentMethodOption, 'code' | 'currency' | 'type'>,
) {
    const code = method.code?.toLowerCase().replaceAll('_', '-');

    if (code) return code;

    return `${method.type === 'bank' ? 'ebanking' : method.type}-${method.currency.toLowerCase()}`;
}

function iconForMethod(method: PaymentMethodOption) {
    if (method.type === 'cash') {
        return method.currency === 'USD' ? DollarSign : Banknote;
    }

    return method.currency === 'USD' ? Building2 : Smartphone;
}

const availableMethods = computed(() => {
    const databaseMethods =
        props.paymentMethods?.map<PaymentMethod>((method) => ({
            currency: method.currency,
            icon: iconForMethod(method),
            id: methodKey(method),
            label: method.label,
            paymentMethodId: method.id,
            type: method.type,
        })) ?? [];

    const baseMethods =
        databaseMethods.length > 0
            ? databaseMethods
            : methods.filter((method) => method.type !== 'pay_later');

    if (props.allowPayLater ?? true) {
        return [
            ...baseMethods,
            methods.find((method) => method.type === 'pay_later')!,
        ];
    }

    return baseMethods;
});

const selectedMethod = computed(() => {
    return (
        availableMethods.value.find(
            (method) => method.id === selectedMethodId.value,
        ) ?? availableMethods.value[0]
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

const changeDueUsd = computed(() => {
    if (selectedMethod.value.type !== 'cash') return 0;

    return selectedMethod.value.currency === 'USD'
        ? changeDue.value
        : changeDue.value / exchangeRate.value;
});

const changeDueKhr = computed(() => {
    if (selectedMethod.value.type !== 'cash') return 0;

    return selectedMethod.value.currency === 'KHR'
        ? changeDue.value
        : changeDue.value * exchangeRate.value;
});

const changeUsdNumeric = computed(() => Number(changeUsdAmount.value || 0));
const changeKhrNumeric = computed(() => Number(changeKhrAmount.value || 0));
const hasManualChangeSplit = computed(() => {
    return changeUsdAmount.value !== '' || changeKhrAmount.value !== '';
});

const finalChangeUsd = computed(() => {
    if (selectedMethod.value.type !== 'cash') return 0;

    if (!hasManualChangeSplit.value) {
        return selectedMethod.value.currency === 'USD' ? changeDueUsd.value : 0;
    }

    if (activeChangeCurrency.value === 'KHR') {
        const roundedKhrChange = roundKhrChange(changeKhrNumeric.value);

        return Math.max(
            0,
            changeDueUsd.value - roundedKhrChange / exchangeRate.value,
        );
    }

    return Math.min(changeDueUsd.value, Math.max(0, changeUsdNumeric.value));
});

const finalChangeKhr = computed(() => {
    if (selectedMethod.value.type !== 'cash') return 0;

    if (!hasManualChangeSplit.value) {
        return selectedMethod.value.currency === 'KHR'
            ? roundKhrChange(changeDueKhr.value)
            : 0;
    }

    if (activeChangeCurrency.value === 'USD') {
        return roundKhrChange(
            Math.max(
                0,
                changeDueKhr.value -
                    changeUsdNumeric.value * exchangeRate.value,
            ),
        );
    }

    return roundKhrChange(
        Math.min(changeDueKhr.value, Math.max(0, changeKhrNumeric.value)),
    );
});

const displayedChangeUsd = computed(() => {
    if (
        !hasManualChangeSplit.value &&
        selectedMethod.value.currency === 'USD'
    ) {
        return finalChangeUsd.value > 0 ? finalChangeUsd.value.toFixed(2) : '';
    }

    if (activeChangeCurrency.value === 'USD') return changeUsdAmount.value;

    return finalChangeUsd.value > 0 ? finalChangeUsd.value.toFixed(2) : '';
});

const displayedChangeKhr = computed(() => {
    if (
        !hasManualChangeSplit.value &&
        selectedMethod.value.currency === 'KHR'
    ) {
        return finalChangeKhr.value > 0
            ? String(Math.round(finalChangeKhr.value))
            : '';
    }

    if (activeChangeCurrency.value === 'KHR') return changeKhrAmount.value;

    return finalChangeKhr.value > 0
        ? String(Math.round(finalChangeKhr.value))
        : '';
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

function roundKhrChange(value: number) {
    const amount = Number(value || 0);

    if (amount <= 0) return 0;

    return Math.ceil(amount / 100) * 100;
}

function selectMethod(method: PaymentMethod) {
    selectedMethodId.value = method.id;
    receivedAmount.value = '';
    clearChangeAmounts();
}

function addAmount(amount: number) {
    receivedAmount.value = String(receivedNumeric.value + amount);
}

function clearAmount() {
    receivedAmount.value = '';
    clearChangeAmounts();
}

function clearChangeAmounts() {
    changeUsdAmount.value = '';
    changeKhrAmount.value = '';
    activeChangeCurrency.value =
        selectedMethod.value.currency === 'USD' ? 'USD' : 'KHR';
}

function updateChangeUsd(value: string) {
    activeChangeCurrency.value = 'USD';
    changeUsdAmount.value = value;
}

function updateChangeKhr(value: string) {
    activeChangeCurrency.value = 'KHR';
    changeKhrAmount.value = value;
}

function confirmPayment() {
    if (!canSubmit.value || processing.value) return;

    processing.value = true;

    emit('confirm', {
        changeKhrAmount: Math.round(finalChangeKhr.value),
        changeUsdAmount: Number(finalChangeUsd.value.toFixed(2)),
        method: selectedMethod.value.id,
        paymentMethodId: selectedMethod.value.paymentMethodId ?? null,
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
        class="fixed inset-0 z-[80] flex items-start justify-center overflow-y-auto bg-[#2A4858]/20 p-2 pt-3 backdrop-blur-sm md:items-center md:p-3"
    >
        <div
            class="flex h-auto max-h-[88vh] w-full max-w-[880px] flex-col overflow-hidden rounded-2xl bg-white shadow-2xl md:flex-row"
        >
            <section
                class="flex-1 overflow-y-auto border-r border-gray-100 p-4"
            >
                <div class="mb-3 flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-xl font-black text-[#2A4858]">
                            Payment Process
                        </h2>
                        <p class="mt-0.5 text-xs font-medium text-gray-400">
                            Please select a payment method to continue
                        </p>
                    </div>

                    <button
                        type="button"
                        class="flex h-8 w-8 items-center justify-center rounded-lg border border-gray-100 text-gray-400 transition hover:bg-gray-50 hover:text-[#2A4858]"
                        @click="emit('close')"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <div
                    class="relative mb-3 overflow-hidden rounded-xl bg-[#2A4858] p-4 text-white"
                >
                    <div class="relative z-10">
                        <span
                            class="text-[10px] font-bold tracking-widest uppercase opacity-60"
                        >
                            Total amount to pay
                        </span>
                        <div class="mt-1 flex flex-wrap items-baseline gap-2">
                            <h1 class="text-3xl font-black">
                                {{ moneyUsd(finalAmount) }}
                            </h1>
                            <span class="text-sm font-bold opacity-60">
                                / {{ moneyKhr(finalAmount * exchangeRate) }}
                            </span>
                        </div>
                    </div>
                    <div
                        class="absolute -right-4 -bottom-4 h-16 w-16 rounded-full bg-white/10 blur-xl"
                    ></div>
                </div>

                <div
                    class="mb-3 grid grid-cols-3 gap-2 rounded-xl bg-gray-50 p-2.5"
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

                <div class="grid grid-cols-2 gap-2 lg:grid-cols-3">
                    <button
                        v-for="method in availableMethods"
                        :key="method.id"
                        type="button"
                        class="group flex flex-col items-center gap-1.5 rounded-xl border-2 p-3 transition"
                        :class="
                            selectedMethodId === method.id
                                ? 'border-[#23AA8F] bg-teal-50'
                                : 'border-transparent bg-gray-50 hover:border-[#23AA8F]/30'
                        "
                        @click="selectMethod(method)"
                    >
                        <span
                            class="flex h-8 w-8 items-center justify-center rounded-full bg-white shadow-sm transition-transform group-hover:scale-110"
                        >
                            <component
                                :is="method.icon"
                                class="h-4 w-4"
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
                class="flex w-full flex-col overflow-y-auto bg-gray-50/50 p-4 md:w-[340px]"
            >
                <div
                    v-if="selectedMethod.type === 'cash'"
                    class="flex flex-1 flex-col"
                >
                    <div class="mb-3">
                        <label
                            class="mb-1.5 block text-[10px] font-black tracking-widest text-gray-400 uppercase"
                        >
                            Receive Amount
                        </label>
                        <div class="relative">
                            <span
                                class="absolute top-1/2 left-3 -translate-y-1/2 text-base font-black text-[#2A4858]"
                            >
                                {{ selectedMethod.currency }}
                            </span>
                            <input
                                v-model="receivedAmount"
                                type="number"
                                inputmode="decimal"
                                placeholder="0.00"
                                class="w-full rounded-xl border-2 border-gray-100 bg-white py-3 pr-3 pl-14 text-right text-xl font-black text-[#2A4858] transition-all outline-none focus:border-[#23AA8F]"
                            />
                        </div>
                    </div>

                    <div
                        class="mb-3 rounded-xl border border-[#86D780] bg-[#86D780]/20 p-3"
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
                            <h2 class="text-2xl font-black text-[#2A4858]">
                                {{ displayCurrencyAmount(changeDue) }}
                            </h2>
                            <span class="font-bold text-[#2A4858] opacity-60">
                                {{ selectedMethod.currency }}
                            </span>
                        </div>
                    </div>

                    <div
                        v-if="changeDue > 0"
                        class="mb-3 rounded-xl border border-gray-100 bg-white p-3"
                    >
                        <div
                            class="mb-2 flex items-center justify-between gap-2"
                        >
                            <p
                                class="text-[10px] font-black tracking-widest text-gray-400 uppercase"
                            >
                                Give Change As
                            </p>
                            <p
                                class="shrink-0 text-right text-[9px] font-bold text-[#2A4858]"
                            >
                                {{ moneyUsd(changeDueUsd) }} /
                                {{ moneyKhr(changeDueKhr) }}
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <label class="block">
                                <span
                                    class="mb-1 block text-[9px] font-black text-gray-400 uppercase"
                                >
                                    USD Change
                                </span>
                                <input
                                    :value="displayedChangeUsd"
                                    type="number"
                                    inputmode="decimal"
                                    min="0"
                                    step="0.01"
                                    class="h-9 w-full rounded-lg border border-gray-100 bg-gray-50 px-2 text-right text-sm font-black text-[#2A4858] outline-none focus:border-[#23AA8F]/60"
                                    @input="
                                        updateChangeUsd(
                                            ($event.target as HTMLInputElement)
                                                .value,
                                        )
                                    "
                                />
                            </label>

                            <label class="block">
                                <span
                                    class="mb-1 block text-[9px] font-black text-gray-400 uppercase"
                                >
                                    KHR Change
                                </span>
                                <input
                                    :value="displayedChangeKhr"
                                    type="number"
                                    inputmode="numeric"
                                    min="0"
                                    step="100"
                                    class="h-9 w-full rounded-lg border border-gray-100 bg-gray-50 px-2 text-right text-sm font-black text-[#2A4858] outline-none focus:border-[#23AA8F]/60"
                                    @input="
                                        updateChangeKhr(
                                            ($event.target as HTMLInputElement)
                                                .value,
                                        )
                                    "
                                />
                            </label>
                        </div>
                    </div>

                    <div class="mb-3 grid grid-cols-3 gap-2">
                        <button
                            v-for="amount in quickAmounts"
                            :key="amount"
                            type="button"
                            class="rounded-lg border border-gray-100 bg-white py-2 text-xs font-bold text-[#2A4858] shadow-sm transition active:scale-95"
                            @click="addAmount(amount)"
                        >
                            +{{ displayCurrencyAmount(amount) }}
                        </button>
                        <button
                            type="button"
                            class="col-span-3 py-1.5 text-xs font-bold text-red-400 hover:text-red-600"
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
                        class="mb-3 flex h-20 w-20 items-center justify-center rounded-2xl bg-white shadow-inner"
                    >
                        <QrCode class="h-10 w-10 text-[#23AA8F]" />
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
                        class="mb-3 flex h-16 w-16 items-center justify-center rounded-full bg-orange-50"
                    >
                        <FileText class="h-8 w-8 text-orange-400" />
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
                    class="mb-3 rounded-xl border border-gray-100 bg-white p-3"
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
                        class="w-full rounded-xl py-3 text-xs font-black tracking-widest text-white uppercase shadow-lg transition-all hover:-translate-y-0.5 disabled:cursor-not-allowed disabled:opacity-60"
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
                        class="w-full rounded-xl border-2 border-gray-100 bg-white py-3 text-xs font-black tracking-widest text-[#2A4858] uppercase transition-all hover:bg-gray-50"
                        @click="emit('close')"
                    >
                        Cancel
                    </button>
                </div>
            </section>
        </div>
    </div>
</template>
