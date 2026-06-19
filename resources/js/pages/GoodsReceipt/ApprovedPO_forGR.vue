<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ClipboardList } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

type ApprovedPurchaseOrder = {
    id: number;
    po_no: string;
    supplier_name?: string | null;
    supplier_phone?: string | null;
    order_date?: string | null;
    expected_date?: string | null;
    receipt_status: string;
    ordered_quantity: number;
    received_quantity: number;
    remaining_quantity: number;
    progress: number;
    lines: unknown[];
};

defineProps<{
    orders: ApprovedPurchaseOrder[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Stock Operations' },
    { title: 'Goods Receipt', href: '/goods-receipts' },
    { title: 'Approved POs' },
];

function statusClass(status: string) {
    const classes: Record<string, string> = {
        'Ready to Receive': 'bg-emerald-100 text-emerald-700',
        'Partially Receiving': 'bg-amber-100 text-amber-700',
        'Fully Received': 'bg-blue-100 text-blue-700',
    };

    return classes[status] ?? 'bg-slate-100 text-slate-700';
}
</script>

<template>
    <Head title="Approved POs for Receipt" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #actions>
            <Button
                type="button"
                variant="ghost"
                class="h-9 font-semibold text-slate-600"
                @click="router.visit('/goods-receipts')"
            >
                ← Back
            </Button>
        </template>

        <main
            class="h-[calc(100dvh-4rem)] w-full scrollbar-gutter-stable overflow-y-scroll bg-[#f8fafc] p-4 text-slate-800 md:h-[calc(100dvh-5rem)] md:p-6 xl:p-8 2xl:p-10"
        >
            <div class="min-h-[56vh] overflow-hidden rounded-lg border border-slate-100 bg-white shadow-sm">
                <div v-if="orders.length > 0" class="overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        <thead class="bg-slate-50 text-xs font-bold text-slate-600 uppercase">
                            <tr>
                                <th class="px-6 py-4">PO Number</th>
                                <th class="px-6 py-4">Supplier</th>
                                <th class="px-6 py-4">Order Date</th>
                                <th class="px-6 py-4">Expected Delivery</th>
                                <th class="px-6 py-4">Progress</th>
                                <th class="px-6 py-4">Receipt Status</th>
                                <th class="px-6 py-4 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <tr
                                v-for="order in orders"
                                :key="order.id"
                                class="transition hover:bg-slate-50/50"
                            >
                                <td class="px-6 py-4 font-mono font-bold text-[#007882]">
                                    {{ order.po_no }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-slate-800">
                                        {{ order.supplier_name ?? '-' }}
                                    </div>
                                    <div class="mt-1 text-xs text-slate-400">
                                        {{ order.supplier_phone ?? '' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-slate-500">
                                    {{ order.order_date ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-slate-500">
                                    {{ order.expected_date ?? '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="h-1.5 w-28 overflow-hidden rounded-full bg-slate-100">
                                        <div
                                            class="h-full bg-[#007882]"
                                            :style="{ width: `${order.progress}%` }"
                                        ></div>
                                    </div>
                                    <span class="mt-1 block text-xs text-slate-400">
                                        {{ order.progress }}% · {{ order.remaining_quantity }} remaining
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="rounded-full px-2.5 py-1 text-xs font-bold uppercase"
                                        :class="statusClass(order.receipt_status)"
                                    >
                                        {{ order.receipt_status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <Link
                                        :href="`/goods-receipts/create?purchase_order_id=${order.id}`"
                                        class="inline-flex h-8 items-center rounded-lg bg-[#007882] px-4 text-xs font-bold text-white transition hover:bg-[#006873]"
                                    >
                                        Start Receipt
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="orders.length === 0" class="p-16 text-center">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-slate-50">
                        <ClipboardList class="size-6 text-slate-300" />
                    </div>
                    <h3 class="font-bold text-[#2a4858]">No approved POs found</h3>
                    <p class="mt-1 text-sm text-slate-500">
                        No approved purchase orders are waiting for receipt.
                    </p>
                </div>
            </div>
        </main>
    </AppLayout>
</template>
