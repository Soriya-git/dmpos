<script setup lang="ts">
import { PackageCheck } from 'lucide-vue-next';
import { computed } from 'vue';

type GoodsReceiptLine = {
    id: number;
    item_name?: string | null;
    item_code?: string | null;
    unit_code?: string | null;
    staging_area?: string | null;
    quantity_received: number;
    unit_cost: number;
};

type GoodsReceipt = {
    id: number;
    receipt_no: string;
    purchase_order_no?: string | null;
    note?: string | null;
    status: string;
    created_at?: string | null;
    updated_at?: string | null;
    received_at?: string | null;
    staging_area?: string | null;
    operator?: string | null;
    cancelled_by?: string | null;
    line_count: number;
    photos?: string[];
    lines: GoodsReceiptLine[];
};

const props = defineProps<{
    receipt: GoodsReceipt;
}>();

const actionInfo = computed(() => {
    const s = props.receipt.status;
    if (s === 'cancelled') {
        return {
            label: 'Cancelled',
            name: props.receipt.cancelled_by,
            at: formatDate(props.receipt.updated_at),
        };
    }
    if (s === 'rejected') {
        return {
            label: 'Rejected',
            name: props.receipt.operator,
            at: formatDate(props.receipt.updated_at),
        };
    }
    if (['approved', 'partially_received', 'received'].includes(s)) {
        return {
            label: statusLabel(s),
            name: props.receipt.operator,
            at: formatDate(
                props.receipt.received_at ?? props.receipt.updated_at,
            ),
        };
    }
    return null;
});

function formatDate(value: string | null | undefined) {
    if (!value) return null;
    const parsed = new Date(value.replace(' ', 'T'));
    if (!Number.isNaN(parsed.getTime())) {
        return parsed.toLocaleDateString(undefined, {
            month: 'short',
            day: '2-digit',
            year: 'numeric',
        });
    }
    return value;
}

function statusLabel(status: string) {
    const labels: Record<string, string> = {
        draft: 'Draft',
        in_progress: 'In Progress',
        approved: 'Received',
        received: 'Received',
        partially_received: 'Partially Received',
        cancelled: 'Cancelled',
        rejected: 'Rejected',
    };
    return (
        labels[status] ??
        status
            .split('_')
            .map((w) => w.charAt(0).toUpperCase() + w.slice(1))
            .join(' ')
    );
}

function statusClass(status: string) {
    const classes: Record<string, string> = {
        draft: 'bg-slate-100 text-slate-700',
        in_progress: 'bg-amber-100 text-amber-700',
        approved: 'bg-green-100 text-green-700',
        partially_received: 'bg-blue-100 text-blue-700',
        received: 'bg-emerald-100 text-emerald-700',
        cancelled: 'bg-rose-100 text-rose-700',
        rejected: 'bg-red-100 text-red-700',
    };
    return classes[status] ?? 'bg-slate-100 text-slate-700';
}

function numberValue(value: number | string | null | undefined) {
    return Number(value ?? 0).toLocaleString(undefined, {
        minimumFractionDigits: 0,
        maximumFractionDigits: 4,
    });
}
</script>

<template>
    <div class="w-full">
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-4 2xl:gap-8">
            <!-- Left: Info + Audit -->
            <div class="space-y-6 xl:col-span-1">
                <div
                    class="rounded-lg border border-slate-100 bg-white p-6 shadow-sm"
                >
                    <h3
                        class="mb-4 flex items-center text-sm font-bold text-slate-700 uppercase"
                    >
                        <PackageCheck class="mr-2 size-4 text-[#007882]" />
                        Info
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <label
                                class="mb-1 block text-xs font-bold text-slate-500 uppercase"
                            >
                                GR Number
                            </label>
                            <div
                                class="flex h-10 items-center rounded-lg border border-slate-200 bg-slate-50 px-3 font-mono text-sm font-bold text-[#007882]"
                            >
                                {{ receipt.receipt_no }}
                            </div>
                        </div>

                        <div>
                            <label
                                class="mb-1 block text-xs font-bold text-slate-500 uppercase"
                            >
                                Source PO
                            </label>
                            <div
                                class="flex h-10 items-center rounded-lg border border-slate-200 bg-slate-50 px-3 font-mono text-sm font-semibold text-slate-700"
                            >
                                {{ receipt.purchase_order_no ?? 'Direct' }}
                            </div>
                        </div>

                        <div>
                            <label
                                class="mb-1 block text-xs font-bold text-slate-500 uppercase"
                            >
                                Staging Area
                            </label>
                            <div
                                class="flex h-10 items-center rounded-lg border border-slate-200 bg-slate-50 px-3 text-sm text-slate-600"
                            >
                                {{ receipt.staging_area ?? 'INBOUND' }}
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label
                                    class="mb-1 block text-xs font-bold text-[#23aa8f] uppercase"
                                >
                                    Received
                                </label>
                                <div
                                    class="flex h-10 items-center rounded-lg border border-emerald-200 bg-emerald-50 px-3 text-xs text-slate-600"
                                >
                                    {{ formatDate(receipt.received_at) ?? '-' }}
                                </div>
                            </div>
                            <div>
                                <label
                                    class="mb-1 block text-xs font-bold text-slate-500 uppercase"
                                >
                                    Created
                                </label>
                                <div
                                    class="flex h-10 items-center rounded-lg border border-slate-200 bg-slate-50 px-3 text-xs text-slate-600"
                                >
                                    {{ formatDate(receipt.created_at) ?? '-' }}
                                </div>
                            </div>
                        </div>

                        <div>
                            <label
                                class="mb-1 block text-xs font-bold text-slate-500 uppercase"
                            >
                                Status
                            </label>
                            <span
                                class="inline-flex rounded-full px-3 py-1 text-xs font-bold uppercase"
                                :class="statusClass(receipt.status)"
                            >
                                {{ statusLabel(receipt.status) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div
                    v-if="receipt.note || receipt.photos?.length"
                    class="rounded-lg border border-slate-100 bg-white p-5 shadow-sm"
                >
                    <div v-if="receipt.note" class="mb-4">
                        <p
                            class="mb-1 text-xs font-bold text-slate-400 uppercase"
                        >
                            Note
                        </p>
                        <p class="text-sm whitespace-pre-wrap text-slate-700">
                            {{ receipt.note }}
                        </p>
                    </div>
                    <div v-if="receipt.photos?.length">
                        <p
                            class="mb-2 text-xs font-bold text-slate-400 uppercase"
                        >
                            Receipt Pictures
                        </p>
                        <div class="grid grid-cols-2 gap-2">
                            <a
                                v-for="photo in receipt.photos"
                                :key="photo"
                                :href="photo"
                                target="_blank"
                                rel="noopener"
                            >
                                <img
                                    :src="photo"
                                    alt="Goods receipt"
                                    class="aspect-square w-full rounded-lg object-cover"
                                />
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Audit -->
                <div
                    class="rounded-lg border border-slate-100 bg-white p-5 shadow-sm"
                >
                    <p
                        class="mb-3 text-xs font-bold tracking-wider text-slate-400 uppercase"
                    >
                        Audit
                    </p>
                    <div class="space-y-2 text-sm">
                        <div class="flex items-start gap-2">
                            <span
                                class="mt-0.5 inline-block h-2 w-2 shrink-0 rounded-full bg-[#007882]"
                            ></span>
                            <div>
                                <p class="text-xs text-slate-400">Operator</p>
                                <p class="font-semibold text-slate-700">
                                    {{ receipt.operator ?? '-' }}
                                </p>
                            </div>
                        </div>
                        <div v-if="actionInfo" class="flex items-start gap-2">
                            <span
                                class="mt-0.5 inline-block h-2 w-2 shrink-0 rounded-full"
                                :class="{
                                    'bg-green-500': [
                                        'Approved',
                                        'Received',
                                        'Partially Received',
                                    ].includes(actionInfo.label),
                                    'bg-red-500':
                                        actionInfo.label === 'Rejected',
                                    'bg-rose-500':
                                        actionInfo.label === 'Cancelled',
                                    'bg-blue-500':
                                        actionInfo.label === 'In Progress',
                                }"
                            ></span>
                            <div>
                                <p class="text-xs text-slate-400">
                                    {{ actionInfo.label }} by
                                </p>
                                <p class="font-semibold text-slate-700">
                                    {{ actionInfo.name ?? '-' }}
                                </p>
                                <p
                                    v-if="actionInfo.at"
                                    class="text-xs text-slate-400"
                                >
                                    {{ actionInfo.at }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Items table + summary bar -->
            <div class="space-y-6 xl:col-span-3">
                <div
                    class="overflow-hidden rounded-lg border border-slate-100 bg-white shadow-sm"
                >
                    <div
                        class="flex items-center justify-between border-b border-slate-100 bg-slate-50 p-4"
                    >
                        <h3
                            class="text-xs font-bold tracking-wider text-slate-700 uppercase"
                        >
                            Received Items
                        </h3>
                        <span class="text-xs text-slate-400">
                            {{ receipt.line_count }} SKU{{
                                receipt.line_count === 1 ? '' : 's'
                            }}
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead
                                class="border-b border-slate-100 text-slate-500"
                            >
                                <tr>
                                    <th
                                        class="min-w-56 px-4 py-3 text-left font-semibold"
                                    >
                                        Item
                                    </th>
                                    <th
                                        class="px-4 py-3 text-right font-semibold"
                                    >
                                        Qty Received
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left font-semibold"
                                    >
                                        Staging Zone
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="line in receipt.lines"
                                    :key="line.id"
                                    class="border-b border-slate-50 last:border-0"
                                >
                                    <td class="px-4 py-4">
                                        <div class="font-bold text-[#2a4858]">
                                            {{ line.item_name ?? 'Item' }}
                                        </div>
                                        <p class="mt-1 text-xs text-slate-400">
                                            {{ line.item_code ?? '-' }} /
                                            {{ line.unit_code ?? '-' }}
                                        </p>
                                    </td>
                                    <td
                                        class="px-4 py-4 text-right font-bold text-[#23aa8f]"
                                    >
                                        {{
                                            numberValue(line.quantity_received)
                                        }}
                                    </td>
                                    <td class="px-4 py-4">
                                        <span
                                            class="rounded bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600"
                                        >
                                            {{ line.staging_area ?? 'INBOUND' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="receipt.lines.length === 0">
                                    <td
                                        colspan="3"
                                        class="px-4 py-12 text-center text-sm text-slate-400"
                                    >
                                        No items recorded.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Summary bar -->
                <div class="rounded-lg bg-[#2a4858] p-6 text-white shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="text-xs font-bold text-white/50 uppercase"
                            >
                                Source PO
                            </p>
                            <p class="mt-0.5 text-lg font-bold">
                                {{
                                    receipt.purchase_order_no ??
                                    'Direct Receipt'
                                }}
                            </p>
                            <p
                                v-if="receipt.staging_area"
                                class="mt-0.5 text-xs text-white/60"
                            >
                                Staging: {{ receipt.staging_area }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p
                                class="text-xs font-bold text-white/50 uppercase"
                            >
                                Items Received
                            </p>
                            <p class="mt-0.5 text-2xl font-bold text-[#fafa6e]">
                                {{ receipt.line_count }}
                                <span
                                    class="text-base font-normal text-white/60"
                                    >SKU{{
                                        receipt.line_count === 1 ? '' : 's'
                                    }}</span
                                >
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
