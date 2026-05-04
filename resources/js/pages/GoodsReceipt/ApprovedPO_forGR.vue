<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, ClipboardList } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';

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

    <AppLayout>
        <main class="w-full bg-slate-100 p-4 text-slate-800 md:p-8">
            <header class="mb-8 flex items-center gap-4">
                <Link
                    href="/goods-receipts"
                    class="flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-400 transition hover:text-[#007882]"
                >
                    <ArrowLeft class="h-5 w-5" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-[#2a4858]">
                        Approved POs for Receipt
                    </h1>
                    <p class="text-slate-500">
                        Approved purchase orders still open for one or more
                        goods receipts
                    </p>
                </div>
            </header>

            <div
                class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
            >
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[900px] text-left">
                        <thead
                            class="bg-slate-50 text-xs font-bold tracking-widest text-slate-500 uppercase"
                        >
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
                        <tbody
                            class="divide-y divide-slate-100 text-sm font-medium"
                        >
                            <tr
                                v-for="order in orders"
                                :key="order.id"
                                class="transition hover:bg-slate-50"
                            >
                                <td class="px-6 py-4 font-bold text-slate-700">
                                    {{ order.po_no }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold">
                                        {{ order.supplier_name ?? 'Supplier' }}
                                    </div>
                                    <div class="text-[10px] text-slate-400">
                                        {{ order.supplier_phone ?? 'No phone' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-500">
                                    {{ order.order_date ?? '-' }}
                                </td>
                                <td
                                    class="px-6 py-4 text-xs font-bold text-slate-500"
                                >
                                    {{ order.expected_date ?? '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    <div
                                        class="h-1.5 w-28 overflow-hidden rounded-full bg-slate-100"
                                    >
                                        <div
                                            class="h-full bg-[#007882]"
                                            :style="{
                                                width: `${order.progress}%`,
                                            }"
                                        ></div>
                                    </div>
                                    <span
                                        class="text-[10px] font-bold text-slate-400"
                                    >
                                        {{ order.progress }}% received,
                                        {{ order.remaining_quantity }}
                                        remaining
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="rounded-full px-3 py-1 text-[0.7rem] font-extrabold uppercase"
                                        :class="
                                            statusClass(order.receipt_status)
                                        "
                                    >
                                        {{ order.receipt_status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <Link
                                        :href="`/goods-receipts/create?purchase_order_id=${order.id}`"
                                        class="rounded-md bg-[#007882] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#006873]"
                                    >
                                        Start Receipt
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="orders.length === 0">
                                <td
                                    colspan="7"
                                    class="px-6 py-12 text-center text-sm text-slate-500"
                                >
                                    <ClipboardList
                                        class="mx-auto mb-3 h-8 w-8 text-slate-300"
                                    />
                                    No approved purchase orders are waiting for
                                    receipt.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </AppLayout>
</template>
