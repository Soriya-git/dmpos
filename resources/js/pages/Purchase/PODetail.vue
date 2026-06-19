<script setup lang="ts">
import { FileText } from 'lucide-vue-next';
import { computed } from 'vue';

type PurchaseStatus =
    | 'created'
    | 'approved'
    | 'rejected'
    | 'cancelled'
    | 'in_progress_receipt'
    | 'partially_received'
    | 'received'
    | 'closed';

type PurchaseOrderLine = {
    id: number;
    item_name?: string | null;
    item_code?: string | null;
    unit_code?: string | null;
    quantity_ordered: number;
    quantity_received: number;
    quantity_remaining: number;
    unit_cost: number;
    line_total: number;
    status: string;
    note?: string | null;
};

type PurchaseOrder = {
    id: number;
    po_no: string;
    supplier_name?: string | null;
    supplier_phone?: string | null;
    supplier_address?: string | null;
    status: PurchaseStatus;
    order_date?: string | null;
    display_order_date?: string | null;
    expected_date?: string | null;
    display_expected_date?: string | null;
    note?: string | null;
    created_by?: string | null;
    approved_by?: string | null;
    display_approved_at?: string | null;
    rejected_by?: string | null;
    display_rejected_at?: string | null;
    cancelled_by?: string | null;
    display_cancelled_at?: string | null;
    grand_total: number;
    lines: PurchaseOrderLine[];
};

const props = defineProps<{
    order: PurchaseOrder;
}>();

const actionInfo = computed(() => {
    const map = {
        approved: {
            label: 'Approved',
            name: props.order.approved_by,
            at: props.order.display_approved_at,
        },
        rejected: {
            label: 'Rejected',
            name: props.order.rejected_by,
            at: props.order.display_rejected_at,
        },
        cancelled: {
            label: 'Cancelled',
            name: props.order.cancelled_by,
            at: props.order.display_cancelled_at,
        },
    } as const;

    return map[props.order.status as keyof typeof map] ?? null;
});

function money(value: number | string | null | undefined) {
    return Number(value ?? 0).toLocaleString(undefined, {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

function numberValue(value: number | string | null | undefined) {
    return Number(value ?? 0).toLocaleString(undefined, {
        minimumFractionDigits: 0,
        maximumFractionDigits: 4,
    });
}

function statusLabel(value: string) {
    const labels: Record<string, string> = {
        created: 'Pending',
        approved: 'Approved',
        rejected: 'Rejected',
        cancelled: 'Cancelled',
        in_progress_receipt: 'Receiving',
        partially_received: 'Partial',
        received: 'Received',
        closed: 'Closed',
        open: 'Open',
    };
    return labels[value] ?? value.replaceAll('_', ' ');
}

function statusClass(value: string) {
    const classes: Record<string, string> = {
        created: 'bg-amber-100 text-amber-700',
        approved: 'bg-green-100 text-green-700',
        rejected: 'bg-red-100 text-red-700',
        cancelled: 'bg-slate-100 text-slate-600',
        in_progress_receipt: 'bg-cyan-100 text-cyan-700',
        partially_received: 'bg-blue-100 text-blue-700',
        received: 'bg-blue-100 text-blue-700',
        closed: 'bg-slate-900 text-white',
        open: 'bg-amber-100 text-amber-700',
    };
    return classes[value] ?? 'bg-slate-100 text-slate-600';
}

function lineStatusClass(value: string) {
    const classes: Record<string, string> = {
        open: 'bg-amber-100 text-amber-700',
        cancelled: 'bg-slate-100 text-slate-500',
        received: 'bg-green-100 text-green-700',
        partial: 'bg-blue-100 text-blue-700',
    };
    return classes[value] ?? 'bg-slate-100 text-slate-500';
}
</script>

<template>
    <div class="w-full">
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-4 2xl:gap-8">
            <!-- Left: Info -->
            <div class="space-y-6 xl:col-span-1">
                <div
                    class="rounded-lg border border-slate-100 bg-white p-6 shadow-sm"
                >
                    <h3
                        class="mb-4 flex items-center text-sm font-bold text-slate-700 uppercase"
                    >
                        <FileText class="mr-2 size-4 text-[#007882]" />
                        Info
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <label
                                class="mb-1 block text-xs font-bold text-slate-500 uppercase"
                            >
                                PO Number
                            </label>
                            <div
                                class="flex h-10 items-center rounded-lg border border-slate-200 bg-slate-50 px-3 font-mono text-sm font-bold text-[#007882]"
                            >
                                {{ order.po_no }}
                            </div>
                        </div>

                        <div>
                            <label
                                class="mb-1 block text-xs font-bold text-slate-500 uppercase"
                            >
                                Supplier
                            </label>
                            <div
                                class="flex h-10 items-center rounded-lg border border-slate-200 bg-slate-50 px-3 text-sm font-semibold text-slate-700"
                            >
                                {{ order.supplier_name ?? '-' }}
                            </div>
                        </div>

                        <div>
                            <label
                                class="mb-1 block text-xs font-bold text-slate-500 uppercase"
                            >
                                Supplier Phone
                            </label>
                            <div
                                class="flex h-10 items-center rounded-lg border border-slate-200 bg-slate-50 px-3 text-sm text-slate-500"
                            >
                                {{ order.supplier_phone ?? '-' }}
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label
                                    class="mb-1 block text-xs font-bold text-amber-600 uppercase"
                                >
                                    Expected
                                </label>
                                <div
                                    class="flex h-10 items-center rounded-lg border border-amber-200 bg-amber-50 px-3 text-xs text-slate-600"
                                >
                                    {{
                                        order.display_expected_date ??
                                        order.expected_date ??
                                        '-'
                                    }}
                                </div>
                            </div>
                            <div>
                                <label
                                    class="mb-1 block text-xs font-bold text-slate-500 uppercase"
                                >
                                    Order Date
                                </label>
                                <div
                                    class="flex h-10 items-center rounded-lg border border-slate-200 bg-slate-50 px-3 text-xs text-slate-600"
                                >
                                    {{
                                        order.display_order_date ??
                                        order.order_date ??
                                        '-'
                                    }}
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
                                :class="statusClass(order.status)"
                            >
                                {{ statusLabel(order.status) }}
                            </span>
                        </div>

                        <div v-if="order.note">
                            <label
                                class="mb-1 block text-xs font-bold text-slate-500 uppercase"
                            >
                                Note
                            </label>
                            <div
                                class="min-h-[4.5rem] rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-500"
                            >
                                {{ order.note }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Audit trail -->
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
                                <p class="text-xs text-slate-400">Created by</p>
                                <p class="font-semibold text-slate-700">
                                    {{ order.created_by ?? '-' }}
                                </p>
                            </div>
                        </div>
                        <div v-if="actionInfo" class="flex items-start gap-2">
                            <span
                                class="mt-0.5 inline-block h-2 w-2 shrink-0 rounded-full"
                                :class="{
                                    'bg-green-500':
                                        actionInfo.label === 'Approved',
                                    'bg-red-500':
                                        actionInfo.label === 'Rejected',
                                    'bg-slate-400':
                                        actionInfo.label === 'Cancelled',
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

            <!-- Right: Items table -->
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
                            Order Items
                        </h3>
                        <span class="text-xs text-slate-400">
                            {{ order.lines.length }} line{{
                                order.lines.length === 1 ? '' : 's'
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
                                        Ordered
                                    </th>
                                    <th
                                        class="px-4 py-3 text-right font-semibold"
                                    >
                                        Received
                                    </th>
                                    <th
                                        class="px-4 py-3 text-right font-semibold"
                                    >
                                        Remaining
                                    </th>
                                    <th
                                        class="px-4 py-3 text-right font-semibold"
                                    >
                                        Unit Cost
                                    </th>
                                    <th
                                        class="px-4 py-3 text-right font-semibold"
                                    >
                                        Line Total
                                    </th>
                                    <th
                                        class="px-4 py-3 text-center font-semibold"
                                    >
                                        Status
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="line in order.lines"
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
                                        <p
                                            v-if="line.note"
                                            class="mt-1 text-xs text-slate-400 italic"
                                        >
                                            {{ line.note }}
                                        </p>
                                    </td>
                                    <td
                                        class="px-4 py-4 text-right font-bold text-[#2a4858]"
                                    >
                                        {{ numberValue(line.quantity_ordered) }}
                                    </td>
                                    <td
                                        class="px-4 py-4 text-right font-bold text-[#23aa8f]"
                                    >
                                        {{
                                            numberValue(line.quantity_received)
                                        }}
                                    </td>
                                    <td
                                        class="px-4 py-4 text-right font-bold text-slate-500"
                                    >
                                        {{
                                            numberValue(line.quantity_remaining)
                                        }}
                                    </td>
                                    <td
                                        class="px-4 py-4 text-right font-mono font-bold text-slate-600"
                                    >
                                        {{ money(line.unit_cost) }}
                                    </td>
                                    <td
                                        class="px-4 py-4 text-right font-mono font-bold text-[#007882]"
                                    >
                                        {{ money(line.line_total) }}
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <span
                                            class="rounded-full px-2.5 py-1 text-xs font-bold uppercase"
                                            :class="
                                                lineStatusClass(line.status)
                                            "
                                        >
                                            {{ statusLabel(line.status) }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Grand total bar -->
                <div class="rounded-lg bg-[#2a4858] p-6 text-white shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="text-xs font-bold text-white/50 uppercase"
                            >
                                Supplier
                            </p>
                            <p class="mt-0.5 text-lg font-bold">
                                {{ order.supplier_name ?? '-' }}
                            </p>
                            <p
                                v-if="order.supplier_phone"
                                class="mt-0.5 text-xs text-white/60"
                            >
                                {{ order.supplier_phone }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p
                                class="text-xs font-bold text-white/50 uppercase"
                            >
                                Grand Total
                            </p>
                            <p class="mt-0.5 text-2xl font-bold text-[#fafa6e]">
                                {{ money(order.grand_total) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
