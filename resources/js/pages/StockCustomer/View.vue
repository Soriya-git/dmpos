<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Search } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';

type Item = {
    id: number;
    code: string;
    name: string;
    unit: string;
    totalQuantity: number;
};

type KeepRow = {
    id: number;
    date?: string | null;
    documentNo: string;
    invoiceNo: string;
    customer: string;
    phone?: string | null;
    location: string;
    quantity: number;
    unit: string;
    note?: string | null;
};

const props = defineProps<{
    item: Item;
    keeps: KeepRow[];
}>();

const search = ref('');

const filteredKeeps = computed(() => {
    const term = search.value.trim().toLowerCase();

    if (!term) return props.keeps;

    return props.keeps.filter((row) =>
        [
            row.documentNo,
            row.invoiceNo,
            row.customer,
            row.phone,
            row.location,
            row.note,
        ]
            .filter(Boolean)
            .some((value) => String(value).toLowerCase().includes(term)),
    );
});

function numberValue(value: number | string | null | undefined) {
    return Number(value ?? 0).toLocaleString(undefined, {
        minimumFractionDigits: 0,
        maximumFractionDigits: 4,
    });
}
</script>

<template>
    <Head :title="`${item.name} Customer Keep Stock`" />

    <AppLayout>
        <main class="w-full bg-slate-100 p-4 text-slate-800 md:p-8">
            <header
                class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-4">
                    <Link
                        href="/balance-on-hand"
                        class="flex h-10 w-10 items-center justify-center rounded-full border bg-white text-slate-400 transition hover:text-[#007882]"
                    >
                        <ArrowLeft class="h-5 w-5" />
                    </Link>
                    <div>
                        <h1 class="text-2xl font-black text-[#2a4858]">
                            Customer Keep Stock
                        </h1>
                        <p class="text-sm font-medium text-slate-500">
                            {{ item.code }} / {{ item.name }}
                        </p>
                    </div>
                </div>
                <div
                    class="rounded-xl border border-rose-100 bg-white px-5 py-3 text-right shadow-sm"
                >
                    <div
                        class="text-[10px] font-black tracking-widest text-slate-400 uppercase"
                    >
                        Total Customer Keep Qty
                    </div>
                    <div class="text-xl font-black text-rose-600">
                        {{ numberValue(item.totalQuantity) }} {{ item.unit }}
                    </div>
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
                        Customer Holdings
                    </h2>
                    <div class="relative w-full md:w-72">
                        <Search
                            class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400"
                        />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search customer, invoice or location..."
                            class="w-full rounded-lg border border-slate-200 bg-white py-2 pr-4 pl-9 text-sm"
                        />
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[920px] text-left">
                        <thead
                            class="border-b bg-slate-50 text-[10px] font-black text-slate-500 uppercase"
                        >
                            <tr>
                                <th class="px-6 py-4">Date</th>
                                <th class="px-6 py-4">Keep Stock #</th>
                                <th class="px-6 py-4">Invoice #</th>
                                <th class="px-6 py-4">Customer</th>
                                <th class="px-6 py-4">Location</th>
                                <th class="px-6 py-4 text-right">Qty</th>
                                <th class="px-6 py-4">Note</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <tr
                                v-for="row in filteredKeeps"
                                :key="row.id"
                                class="transition hover:bg-slate-50"
                            >
                                <td
                                    class="px-6 py-4 font-medium text-slate-600"
                                >
                                    {{ row.date ?? '-' }}
                                </td>
                                <td class="px-6 py-4 font-black text-[#007882]">
                                    {{ row.documentNo }}
                                </td>
                                <td class="px-6 py-4 font-bold">
                                    {{ row.invoiceNo }}
                                </td>
                                <td
                                    class="px-6 py-4 font-medium text-slate-600"
                                >
                                    {{ row.customer }}
                                    <div class="text-[10px] text-slate-400">
                                        {{ row.phone ?? '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="rounded bg-rose-50 px-2 py-1 font-bold text-rose-600"
                                    >
                                        {{ row.location }}
                                    </span>
                                </td>
                                <td
                                    class="px-6 py-4 text-right font-black text-rose-600"
                                >
                                    {{ numberValue(row.quantity) }}
                                    {{ row.unit }}
                                </td>
                                <td class="px-6 py-4 text-slate-500">
                                    {{ row.note ?? '-' }}
                                </td>
                            </tr>
                            <tr v-if="filteredKeeps.length === 0">
                                <td
                                    colspan="7"
                                    class="px-6 py-12 text-center text-sm text-slate-500"
                                >
                                    No customer keep stock found for this item.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </AppLayout>
</template>
