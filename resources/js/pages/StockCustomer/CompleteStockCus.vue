<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Search } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';

type Invoice = {
    id: number;
    invoice_no: string;
    issued_at?: string | null;
    customer_name: string;
    customer_phone?: string | null;
    line_count: number;
    total_invoiced: number;
    total_remaining: number;
    progress: number;
};

const props = defineProps<{ invoices: Invoice[] }>();
const search = ref('');

const filteredInvoices = computed(() => {
    const term = search.value.trim().toLowerCase();

    if (!term) return props.invoices;

    return props.invoices.filter((invoice) =>
        [invoice.invoice_no, invoice.customer_name, invoice.customer_phone]
            .filter(Boolean)
            .some((value) => String(value).toLowerCase().includes(term)),
    );
});
</script>

<template>
    <Head title="Invoices for Customer Stock" />

    <AppLayout>
        <main class="w-full bg-slate-100 p-4 text-slate-800 md:p-8">
            <header class="mb-6 flex items-center gap-4">
                <Link
                    href="/stock-customer"
                    class="flex h-10 w-10 items-center justify-center rounded-full border bg-white text-slate-400 transition hover:text-[#007882]"
                >
                    <ArrowLeft class="h-5 w-5" />
                </Link>
                <div>
                    <h1 class="text-2xl font-black text-[#2a4858]">
                        Select Invoice
                    </h1>
                    <p class="text-sm font-medium text-slate-500">
                        Choose the customer invoice that owns the leftover stock
                    </p>
                </div>
            </header>

            <div
                class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
            >
                <div
                    class="flex flex-col gap-3 border-b bg-slate-50/50 p-4 md:flex-row md:items-center md:justify-between"
                >
                    <h2
                        class="text-xs font-black tracking-widest text-slate-500 uppercase"
                    >
                        Available Invoices
                    </h2>
                    <div class="relative w-full md:w-72">
                        <Search
                            class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400"
                        />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search invoice or customer..."
                            class="w-full rounded-lg border border-slate-200 bg-white py-2 pr-4 pl-9 text-sm"
                        />
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[860px] text-left">
                        <thead
                            class="border-b bg-slate-50 text-[10px] font-black text-slate-500 uppercase"
                        >
                            <tr>
                                <th class="px-6 py-4">Invoice #</th>
                                <th class="px-6 py-4">Invoice Date</th>
                                <th class="px-6 py-4">Customer</th>
                                <th class="px-6 py-4">Remaining Qty</th>
                                <th class="px-6 py-4">Keep Progress</th>
                                <th class="px-6 py-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <tr
                                v-for="invoice in filteredInvoices"
                                :key="invoice.id"
                                class="transition hover:bg-slate-50"
                            >
                                <td class="px-6 py-4 font-black text-[#007882]">
                                    {{ invoice.invoice_no }}
                                </td>
                                <td
                                    class="px-6 py-4 font-medium text-slate-600"
                                >
                                    {{ invoice.issued_at ?? '-' }}
                                </td>
                                <td class="px-6 py-4 font-bold">
                                    {{ invoice.customer_name }}
                                    <div class="text-[10px] text-slate-400">
                                        {{ invoice.customer_phone ?? '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-bold">
                                        {{ invoice.total_remaining }} units
                                    </span>
                                    <div
                                        class="text-[10px] font-medium text-slate-400"
                                    >
                                        {{ invoice.line_count }} line items
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div
                                        class="h-1.5 w-28 overflow-hidden rounded-full bg-slate-100"
                                    >
                                        <div
                                            class="h-full bg-[#007882]"
                                            :style="{
                                                width: `${invoice.progress}%`,
                                            }"
                                        ></div>
                                    </div>
                                    <span
                                        class="text-[10px] font-bold text-slate-400"
                                        >{{ invoice.progress }}% kept</span
                                    >
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <Link
                                        :href="`/stock-customer/create?invoice_id=${invoice.id}`"
                                        class="rounded bg-[#2a4858] px-4 py-1.5 text-xs font-bold text-white shadow-sm"
                                    >
                                        Select Invoice
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="filteredInvoices.length === 0">
                                <td
                                    colspan="6"
                                    class="px-6 py-12 text-center text-sm text-slate-500"
                                >
                                    No invoice has remaining stockable items for
                                    customer keep stock.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </AppLayout>
</template>
