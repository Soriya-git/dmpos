<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Search } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';

type Receipt = {
    id: number;
    receipt_no: string;
    received_at?: string | null;
    purchase_order_no?: string | null;
    staging_area?: string | null;
    line_count: number;
    total_received: number;
    total_remaining: number;
    progress: number;
};

const props = defineProps<{ receipts: Receipt[] }>();
const search = ref('');

const filteredReceipts = computed(() => {
    const term = search.value.trim().toLowerCase();

    if (!term) {
        return props.receipts;
    }

    return props.receipts.filter((receipt) =>
        [receipt.receipt_no, receipt.purchase_order_no, receipt.staging_area]
            .filter(Boolean)
            .some((value) => String(value).toLowerCase().includes(term)),
    );
});
</script>

<template>
    <Head title="Completed GRs for Putaway" />

    <AppLayout>
        <main class="w-full bg-slate-100 p-4 text-slate-800 md:p-8">
            <header class="mb-6 flex items-center gap-4">
                <Link
                    href="/putaway"
                    class="flex h-10 w-10 items-center justify-center rounded-full border bg-white text-slate-400 transition hover:text-[#007882]"
                >
                    <ArrowLeft class="h-5 w-5" />
                </Link>
                <div>
                    <h1 class="text-2xl font-black text-[#2a4858]">
                        Select Pending Goods Receipt
                    </h1>
                    <p class="text-sm font-medium text-slate-500">
                        Completed receipts awaiting movement from staging to
                        storage
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
                        Available for Putaway
                    </h2>
                    <div class="relative w-full md:w-72">
                        <Search
                            class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400"
                        />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search GR, PO or staging..."
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
                                <th class="px-6 py-4">GR Document #</th>
                                <th class="px-6 py-4">Receipt Date</th>
                                <th class="px-6 py-4">Source PO</th>
                                <th class="px-6 py-4">Remaining Items</th>
                                <th class="px-6 py-4">Putaway Progress</th>
                                <th class="px-6 py-4">Staging Area</th>
                                <th class="px-6 py-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <tr
                                v-for="receipt in filteredReceipts"
                                :key="receipt.id"
                                class="transition hover:bg-slate-50"
                            >
                                <td class="px-6 py-4 font-black text-[#007882]">
                                    {{ receipt.receipt_no }}
                                </td>
                                <td
                                    class="px-6 py-4 font-medium text-slate-600"
                                >
                                    {{ receipt.received_at ?? '-' }}
                                </td>
                                <td class="px-6 py-4 font-bold">
                                    {{ receipt.purchase_order_no ?? '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-bold"
                                        >{{
                                            receipt.total_remaining
                                        }}
                                        units</span
                                    >
                                    <div
                                        class="text-[10px] font-medium text-slate-400"
                                    >
                                        {{ receipt.line_count }} line items
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div
                                        class="h-1.5 w-28 overflow-hidden rounded-full bg-slate-100"
                                    >
                                        <div
                                            class="h-full bg-[#007882]"
                                            :style="{
                                                width: `${receipt.progress}%`,
                                            }"
                                        ></div>
                                    </div>
                                    <span
                                        class="text-[10px] font-bold text-slate-400"
                                        >{{ receipt.progress }}% put away</span
                                    >
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="rounded bg-amber-50 px-2 py-1 font-bold text-amber-600"
                                    >
                                        {{ receipt.staging_area ?? 'STAGE' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <Link
                                        :href="`/putaway/create?goods_receipt_id=${receipt.id}`"
                                        class="rounded bg-[#2a4858] px-4 py-1.5 text-xs font-bold text-white shadow-sm"
                                    >
                                        Select Receipt
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="filteredReceipts.length === 0">
                                <td
                                    colspan="7"
                                    class="px-6 py-12 text-center text-sm text-slate-500"
                                >
                                    No completed goods receipts are waiting for
                                    putaway.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </AppLayout>
</template>
