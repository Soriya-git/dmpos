<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CheckCircle2,
    CreditCard,
    RotateCcw,
    Search,
    UserRound,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import MembershipCardPreview from '@/components/master-data/MembershipCardPreview.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

type CardStatus = 'active' | 'inactive' | 'blocked' | 'expired' | 'cancelled';

type CustomerOption = {
    id: number;
    name: string;
    phone?: string | null;
    branchId?: number | null;
};

const props = defineProps<{
    customers: CustomerOption[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Membership Cards', href: '/membership-cards' },
    { title: 'Create' },
];

const customerSearch = ref('');
const selectedCustomerId = ref<number | null>(null);

const form = useForm({
    customer_id: null as number | null,
    card_no: makeCardNo(),
    card_name: 'Member Pass',
    status: 'active' as CardStatus,
    issued_date: new Date().toISOString().slice(0, 10),
    expired_date: '',
    remark: '',
});

const selectedCustomer = computed(
    () =>
        props.customers.find(
            (customer) => customer.id === selectedCustomerId.value,
        ) ?? null,
);

const filteredCustomers = computed(() => {
    const term = customerSearch.value.trim().toLowerCase();

    if (!term) {
        return props.customers.slice(0, 30);
    }

    return props.customers
        .filter((customer) =>
            `${customer.name} ${customer.phone ?? ''}`
                .toLowerCase()
                .includes(term),
        )
        .slice(0, 30);
});

watch(selectedCustomer, (customer) => {
    form.customer_id = customer?.id ?? null;
});

function makeCardNo() {
    return `MC-${new Date().getFullYear()}-${Math.floor(
        Math.random() * 900000 + 100000,
    )}`;
}

function selectCustomer(customer: CustomerOption) {
    selectedCustomerId.value = customer.id;
    customerSearch.value = `${customer.name} ${customer.phone ?? ''}`.trim();
}

function resetForm() {
    selectedCustomerId.value = null;
    customerSearch.value = '';
    form.clearErrors();
    form.customer_id = null;
    form.card_no = makeCardNo();
    form.card_name = 'Member Pass';
    form.status = 'active';
    form.issued_date = new Date().toISOString().slice(0, 10);
    form.expired_date = '';
    form.remark = '';
}

function submit() {
    form.post('/membership-cards', {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Create Membership Card" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 lg:p-6">
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
                        Create Membership Card
                    </h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Issue a new reusable payment card for an existing
                        customer.
                    </p>
                </div>

                <div
                    class="flex items-center gap-2 rounded-lg border border-[#007882]/15 bg-[#007882]/5 px-3 py-2 text-xs font-black text-[#007882]"
                >
                    <CreditCard class="size-4" />
                    New Card
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-[420px_minmax(0,1fr)]">
                <section
                    class="h-fit rounded-xl border border-slate-200 bg-white p-6 shadow-sm"
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
                        :card-no="form.card_no"
                        :card-name="form.card_name"
                        :customer-name="selectedCustomer?.name"
                        company-branch="DM Group / Central Branch"
                        :issued-date="form.issued_date"
                        :expired-date="form.expired_date"
                        :remark="form.remark"
                        :status="form.status"
                    />
                </section>

                <section
                    class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
                >
                    <div class="bg-[#2A4858] p-6 text-white">
                        <h2 class="text-lg font-bold">Card Properties</h2>
                        <p
                            class="mt-1 text-[10px] tracking-widest text-white/50 uppercase"
                        >
                            Customer relation and card master data
                        </p>
                    </div>

                    <form class="space-y-5 p-5" @submit.prevent="submit">
                        <div class="rounded-xl bg-slate-50 p-4">
                            <div class="mb-3 flex items-center gap-2">
                                <UserRound class="size-4 text-[#007882]" />
                                <span
                                    class="text-[10px] font-black tracking-widest text-slate-400 uppercase"
                                >
                                    Select Customer
                                </span>
                            </div>

                            <div class="relative">
                                <Search
                                    class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"
                                />
                                <Input
                                    v-model="customerSearch"
                                    type="search"
                                    class="h-10 rounded-lg border-slate-200 bg-white pl-9 text-sm font-bold focus-visible:ring-[#007882]"
                                    placeholder="Search by customer name or phone number"
                                />
                            </div>

                            <div
                                class="mt-2 max-h-56 overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-sm"
                            >
                                <button
                                    v-for="customer in filteredCustomers"
                                    :key="customer.id"
                                    type="button"
                                    class="flex w-full items-center justify-between gap-3 border-b border-slate-100 px-3 py-2 text-left last:border-b-0 hover:bg-[#23AA8F]/10"
                                    :class="
                                        selectedCustomerId === customer.id
                                            ? 'bg-[#23AA8F]/10'
                                            : ''
                                    "
                                    @click="selectCustomer(customer)"
                                >
                                    <span class="min-w-0">
                                        <span
                                            class="block truncate text-sm font-black text-[#2A4858]"
                                        >
                                            {{ customer.name }}
                                        </span>
                                        <span
                                            class="block truncate text-xs text-slate-400"
                                        >
                                            {{ customer.phone || 'No phone' }}
                                        </span>
                                    </span>
                                    <CheckCircle2
                                        v-if="
                                            selectedCustomerId === customer.id
                                        "
                                        class="size-4 shrink-0 text-[#007882]"
                                    />
                                </button>

                                <p
                                    v-if="filteredCustomers.length === 0"
                                    class="px-3 py-8 text-center text-xs text-slate-400"
                                >
                                    No matching customer found.
                                </p>
                            </div>

                            <p
                                v-if="form.errors.customer_id"
                                class="mt-2 text-xs font-bold text-rose-600"
                            >
                                {{ form.errors.customer_id }}
                            </p>
                        </div>

                        <div class="space-y-4 rounded-xl bg-slate-50 p-4">
                            <div class="grid gap-3 md:grid-cols-2">
                                <label class="block">
                                    <span
                                        class="text-[10px] font-bold text-slate-400 uppercase"
                                    >
                                        Card Number
                                    </span>
                                    <Input
                                        v-model="form.card_no"
                                        required
                                        class="mt-1 bg-white font-mono text-sm font-bold focus-visible:ring-[#007882]"
                                    />
                                    <p
                                        v-if="form.errors.card_no"
                                        class="mt-1 text-xs text-rose-600"
                                    >
                                        {{ form.errors.card_no }}
                                    </p>
                                </label>

                                <label class="block">
                                    <span
                                        class="text-[10px] font-bold text-slate-400 uppercase"
                                    >
                                        Card Name
                                    </span>
                                    <Input
                                        v-model="form.card_name"
                                        class="mt-1 bg-white text-sm font-bold focus-visible:ring-[#007882]"
                                    />
                                    <p
                                        v-if="form.errors.card_name"
                                        class="mt-1 text-xs text-rose-600"
                                    >
                                        {{ form.errors.card_name }}
                                    </p>
                                </label>
                            </div>

                            <div class="grid gap-3 md:grid-cols-3">
                                <label class="block">
                                    <span
                                        class="text-[10px] font-bold text-slate-400 uppercase"
                                    >
                                        Issued Date
                                    </span>
                                    <Input
                                        v-model="form.issued_date"
                                        type="date"
                                        class="mt-1 bg-white text-xs font-bold focus-visible:ring-[#007882]"
                                    />
                                </label>

                                <label class="block">
                                    <span
                                        class="text-[10px] font-bold text-slate-400 uppercase"
                                    >
                                        Expiry Date
                                    </span>
                                    <Input
                                        v-model="form.expired_date"
                                        type="date"
                                        class="mt-1 bg-white text-xs font-bold focus-visible:ring-[#007882]"
                                    />
                                    <p
                                        v-if="form.errors.expired_date"
                                        class="mt-1 text-xs text-rose-600"
                                    >
                                        {{ form.errors.expired_date }}
                                    </p>
                                </label>

                                <label class="block">
                                    <span
                                        class="text-[10px] font-bold text-slate-400 uppercase"
                                    >
                                        Status
                                    </span>
                                    <select
                                        v-model="form.status"
                                        class="mt-1 h-9 w-full rounded-md border border-input bg-white px-3 py-1 text-xs font-bold shadow-xs outline-none focus:border-[#007882]"
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

                            <div
                                class="rounded-lg border border-slate-200 bg-white p-3"
                            >
                                <span
                                    class="text-[10px] font-bold text-slate-400 uppercase"
                                >
                                    Balance
                                </span>
                                <p
                                    class="mt-1 text-xs font-semibold text-slate-500"
                                >
                                    Card balance is created from the topup
                                    ledger after the card is issued.
                                </p>
                            </div>

                            <label class="block">
                                <span
                                    class="text-[10px] font-bold text-slate-400 uppercase"
                                >
                                    Remarks
                                </span>
                                <textarea
                                    v-model="form.remark"
                                    rows="3"
                                    class="mt-1 w-full rounded-md border border-input bg-white px-3 py-2 text-sm shadow-xs outline-none focus:border-[#007882]"
                                    placeholder="Card terms, customer note, or special handling..."
                                ></textarea>
                                <p
                                    v-if="form.errors.remark"
                                    class="mt-1 text-xs text-rose-600"
                                >
                                    {{ form.errors.remark }}
                                </p>
                            </label>
                        </div>

                        <div class="flex justify-end gap-3 border-t pt-5">
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
                                :disabled="form.processing"
                            >
                                <CreditCard class="size-4" />
                                Save Card
                            </Button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
