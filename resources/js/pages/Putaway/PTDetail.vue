<script setup lang="ts">
import { MoveRight } from 'lucide-vue-next';
import { computed } from 'vue';

type PutawayLine = {
    id: number;
    item_name?: string | null;
    item_code?: string | null;
    unit_code?: string | null;
    to_location?: string | null;
    quantity: number;
    unit_cost: number;
    total_cost: number;
};

type Putaway = {
    id: number;
    transfer_no: string;
    goods_receipt_no?: string | null;
    priority: string;
    assigned_staff: string;
    note?: string | null;
    created_at?: string | null;
    updated_at?: string | null;
    approved_at?: string | null;
    cancelled_at?: string | null;
    rejected_at?: string | null;
    created_by?: string | null;
    approved_by?: string | null;
    cancelled_by?: string | null;
    rejected_by?: string | null;
    item_count: number;
    total_quantity: number;
    status: string;
    lines: PutawayLine[];
};

const props = defineProps<{ putaway: Putaway }>();

const actionInfo = computed(() => {
    const s = props.putaway.status;
    if (s === 'approved') {
        return {
            label: 'Approved',
            name: props.putaway.approved_by,
            at: props.putaway.approved_at,
        };
    }
    if (s === 'cancelled') {
        return {
            label: 'Cancelled',
            name: props.putaway.cancelled_by,
            at: props.putaway.cancelled_at,
        };
    }
    if (s === 'rejected') {
        return {
            label: 'Rejected',
            name: props.putaway.rejected_by,
            at: props.putaway.rejected_at ?? props.putaway.updated_at,
        };
    }
    return null;
});

function priorityClass(priority: string) {
    const classes: Record<string, string> = {
        low: 'bg-slate-100 text-slate-600',
        normal: 'bg-blue-100 text-blue-700',
        urgent: 'bg-rose-100 text-rose-700',
    };
    return classes[priority] ?? 'bg-slate-100 text-slate-600';
}

function statusClass(status: string) {
    const classes: Record<string, string> = {
        draft: 'bg-slate-100 text-slate-700',
        submitted: 'bg-amber-100 text-amber-700',
        approved: 'bg-green-100 text-green-700',
        in_transit: 'bg-blue-100 text-blue-700',
        received: 'bg-emerald-100 text-emerald-700',
        cancelled: 'bg-rose-100 text-rose-700',
        rejected: 'bg-red-100 text-red-700',
    };
    return classes[status] ?? 'bg-slate-100 text-slate-700';
}

function statusLabel(status: string) {
    const labels: Record<string, string> = {
        draft: 'Draft',
        submitted: 'Submitted',
        approved: 'Putaway',
        in_transit: 'In Transit',
        received: 'Received',
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
                        <MoveRight class="mr-2 size-4 text-[#007882]" />
                        Info
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <label
                                class="mb-1 block text-xs font-bold text-slate-500 uppercase"
                                >Transfer #</label
                            >
                            <div
                                class="flex h-10 items-center rounded-lg border border-slate-200 bg-slate-50 px-3 font-mono text-sm font-bold text-[#007882]"
                            >
                                {{ putaway.transfer_no }}
                            </div>
                        </div>

                        <div>
                            <label
                                class="mb-1 block text-xs font-bold text-slate-500 uppercase"
                                >Source GR</label
                            >
                            <div
                                class="flex h-10 items-center rounded-lg border border-slate-200 bg-slate-50 px-3 font-mono text-sm font-semibold text-slate-700"
                            >
                                {{ putaway.goods_receipt_no ?? 'Direct' }}
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="flex-1">
                                <label
                                    class="mb-1 block text-xs font-bold text-slate-500 uppercase"
                                    >Priority</label
                                >
                                <span
                                    class="inline-flex rounded-full px-3 py-1 text-xs font-bold uppercase"
                                    :class="priorityClass(putaway.priority)"
                                >
                                    {{ putaway.priority }}
                                </span>
                            </div>
                            <div class="flex-1">
                                <label
                                    class="mb-1 block text-xs font-bold text-slate-500 uppercase"
                                    >Status</label
                                >
                                <span
                                    class="inline-flex rounded-full px-3 py-1 text-xs font-bold uppercase"
                                    :class="statusClass(putaway.status)"
                                >
                                    {{ statusLabel(putaway.status) }}
                                </span>
                            </div>
                        </div>

                        <div v-if="putaway.note">
                            <label
                                class="mb-1 block text-xs font-bold text-slate-500 uppercase"
                                >Note</label
                            >
                            <div
                                class="rounded-lg border border-slate-200 bg-slate-50 p-3 text-sm text-slate-600"
                            >
                                {{ putaway.note }}
                            </div>
                        </div>

                        <div>
                            <label
                                class="mb-1 block text-xs font-bold text-slate-500 uppercase"
                                >Created</label
                            >
                            <div
                                class="flex h-10 items-center rounded-lg border border-slate-200 bg-slate-50 px-3 text-xs text-slate-600"
                            >
                                {{ putaway.created_at ?? '-' }}
                            </div>
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
                                <p class="text-xs text-slate-400">Created by</p>
                                <p class="font-semibold text-slate-700">
                                    {{ putaway.created_by ?? '-' }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2">
                            <span
                                class="mt-0.5 inline-block h-2 w-2 shrink-0 rounded-full bg-[#23aa8f]"
                            ></span>
                            <div>
                                <p class="text-xs text-slate-400">
                                    Putaway staff
                                </p>
                                <p class="font-semibold text-slate-700">
                                    {{ putaway.assigned_staff ?? '-' }}
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
                                    'bg-rose-500':
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

            <!-- Right: Items + summary -->
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
                            Putaway Items
                        </h3>
                        <span class="text-xs text-slate-400">
                            {{ putaway.item_count }} SKU{{
                                putaway.item_count === 1 ? '' : 's'
                            }}
                            · {{ putaway.total_quantity }} units
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
                                        Qty
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left font-semibold"
                                    >
                                        Destination Bin
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="line in putaway.lines"
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
                                        {{ line.quantity }}
                                    </td>
                                    <td class="px-4 py-4">
                                        <span
                                            class="rounded bg-slate-100 px-2.5 py-1 text-xs font-bold text-[#007882]"
                                        >
                                            {{ line.to_location ?? '-' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="putaway.lines.length === 0">
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
                                Source GR
                            </p>
                            <p class="mt-0.5 text-lg font-bold">
                                {{
                                    putaway.goods_receipt_no ?? 'Direct Receipt'
                                }}
                            </p>
                            <p class="mt-0.5 text-xs text-white/60">
                                Priority: {{ putaway.priority }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p
                                class="text-xs font-bold text-white/50 uppercase"
                            >
                                Total Units
                            </p>
                            <p class="mt-0.5 text-2xl font-bold text-[#fafa6e]">
                                {{ putaway.total_quantity }}
                                <span
                                    class="text-base font-normal text-white/60"
                                    >units</span
                                >
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
