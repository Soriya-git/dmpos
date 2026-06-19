<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ClipboardCheck, Search } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

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

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Stock Operations' },
    { title: 'Putaway', href: '/putaway' },
    { title: 'Completed GRs' },
];

const search = ref('');

const filteredReceipts = computed(() => {
    const term = search.value.trim().toLowerCase();
    if (!term) return props.receipts;
    return props.receipts.filter((r) =>
        [r.receipt_no, r.purchase_order_no, r.staging_area]
            .filter(Boolean)
            .some((v) => String(v).toLowerCase().includes(term)),
    );
});
</script>

<template>
    <Head title="Completed GRs for Putaway" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #actions>
            <Button
                type="button"
                variant="ghost"
                class="h-9 font-semibold text-slate-600"
                @click="router.visit('/putaway')"
            >
                ← Back
            </Button>
        </template>

        <main
            class="h-[calc(100dvh-4rem)] w-full scrollbar-gutter-stable overflow-y-scroll bg-[#f8fafc] p-4 text-slate-800 md:h-[calc(100dvh-5rem)] md:p-6 xl:p-8 2xl:p-10"
        >
            <div
                class="min-h-[56vh] overflow-hidden rounded-lg border border-slate-100 bg-white shadow-sm"
            >
                <div
                    class="flex items-center justify-between border-b border-slate-100 bg-slate-50 p-4"
                >
                    <h2
                        class="text-xs font-bold tracking-wider text-slate-700 uppercase"
                    >
                        Available for Putaway
                    </h2>
                    <div class="relative w-72">
                        <Search
                            class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"
                        />
                        <Input
                            v-model="search"
                            type="text"
                            placeholder="Search GR, PO or staging..."
                            class="h-9 rounded-lg border-slate-200 bg-white pl-9 focus-visible:ring-[#007882]/30"
                        />
                    </div>
                </div>

                <div v-if="filteredReceipts.length > 0" class="overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        <thead
                            class="bg-slate-50 text-xs font-bold text-slate-600 uppercase"
                        >
                            <tr>
                                <th class="px-6 py-4">GR Document #</th>
                                <th class="px-6 py-4">Receipt Date</th>
                                <th class="px-6 py-4">Source PO</th>
                                <th class="px-6 py-4">Remaining Items</th>
                                <th class="px-6 py-4">Putaway Progress</th>
                                <th class="px-6 py-4">Staging Area</th>
                                <th class="px-6 py-4 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <tr
                                v-for="receipt in filteredReceipts"
                                :key="receipt.id"
                                class="transition hover:bg-slate-50/50"
                            >
                                <td
                                    class="px-6 py-4 font-mono font-bold text-[#007882]"
                                >
                                    {{ receipt.receipt_no }}
                                </td>
                                <td class="px-6 py-4 text-slate-500">
                                    {{ receipt.received_at ?? '-' }}
                                </td>
                                <td
                                    class="px-6 py-4 font-semibold text-slate-700"
                                >
                                    {{ receipt.purchase_order_no ?? '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-bold text-slate-800">
                                        {{ receipt.total_remaining }} units
                                    </span>
                                    <div class="mt-0.5 text-xs text-slate-400">
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
                                        class="mt-1 block text-xs text-slate-400"
                                    >
                                        {{ receipt.progress }}% put away
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="rounded bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-600"
                                    >
                                        {{ receipt.staging_area ?? 'STAGE' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button
                                        type="button"
                                        class="inline-flex h-8 items-center rounded-lg bg-[#007882] px-4 text-xs font-bold text-white transition hover:bg-[#006873]"
                                        @click="
                                            router.visit(
                                                `/putaway/create?goods_receipt_id=${receipt.id}`,
                                            )
                                        "
                                    >
                                        Select Receipt
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div
                    v-if="filteredReceipts.length === 0"
                    class="p-16 text-center"
                >
                    <div
                        class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-slate-50"
                    >
                        <ClipboardCheck class="size-6 text-slate-300" />
                    </div>
                    <h3 class="font-bold text-[#2a4858]">
                        No completed GRs found
                    </h3>
                    <p class="mt-1 text-sm text-slate-500">
                        No approved goods receipts are waiting for putaway.
                    </p>
                </div>
            </div>
        </main>
    </AppLayout>
</template>
