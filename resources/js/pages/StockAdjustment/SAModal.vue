<script setup lang="ts">
import { CalendarDays, X } from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';

type AdjustmentStatus = 'draft' | 'approved' | 'cancelled';
type AdjustmentType = 'adjustment_in' | 'adjustment_out';

type AdjustmentLine = {
    id: number;
    itemCode: string;
    itemName: string;
    unit: string;
    location?: string | null;
    locationCode?: string | null;
    systemQuantity: number;
    adjustedQuantity: number;
    differenceQuantity: number;
    unitCost: number;
    totalCost: number;
    reason?: string | null;
    note?: string | null;
};

type AdjustmentRecord = {
    id: number;
    code: string;
    type: AdjustmentType;
    date?: string | null;
    displayDate?: string | null;
    warehouse: string;
    location: string;
    locationCode: string;
    branch: string;
    itemCount: number;
    totalQuantity: number;
    totalCost: number;
    reason?: string | null;
    createdBy: string;
    confirmedBy?: string | null;
    cancelledBy?: string | null;
    confirmedAt?: string | null;
    cancelledAt?: string | null;
    actionStatus?: string | null;
    status: AdjustmentStatus;
    lines: AdjustmentLine[];
};

const props = defineProps<{
    adjustment: AdjustmentRecord;
}>();

const emit = defineEmits<{
    close: [];
}>();

const actionInfo = computed(() => {
    if (props.adjustment.actionStatus === 'Approved') {
        return {
            status: 'Approved',
            name: props.adjustment.confirmedBy,
            at: actionDate(props.adjustment.confirmedAt),
        };
    }

    if (
        props.adjustment.actionStatus === 'Rejected' ||
        props.adjustment.actionStatus === 'Cancelled'
    ) {
        return {
            status: props.adjustment.actionStatus,
            name: props.adjustment.cancelledBy,
            at: actionDate(props.adjustment.cancelledAt),
        };
    }

    return null;
});

function actionDate(value: string | null | undefined) {
    if (!value) return null;

    const parsed = new Date(value.replace(' ', 'T'));

    if (!Number.isNaN(parsed.getTime())) {
        return parsed.toLocaleDateString(undefined, {
            month: 'short',
            day: '2-digit',
            year: 'numeric',
        });
    }

    return value.match(/^[A-Za-z]{3}\s+\d{1,2},\s+\d{4}/)?.[0] ?? value;
}

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

function typeLabel(value: string) {
    const labels: Record<string, string> = {
        adjustment_in: 'Adjust In',
        adjustment_out: 'Adjust Out',
    };

    return labels[value] ?? value.replaceAll('_', ' ');
}

function statusLabel(value: string) {
    const labels: Record<string, string> = {
        draft: 'Draft',
        approved: 'Approved',
        cancelled: 'Cancelled',
    };

    return labels[value] ?? value.replaceAll('_', ' ');
}

function statusClass(value: string) {
    const classes: Record<string, string> = {
        draft: 'bg-amber-100 text-amber-700',
        approved: 'bg-green-100 text-green-700',
        cancelled: 'bg-slate-100 text-slate-600',
    };

    return classes[value] ?? 'bg-slate-100 text-slate-600';
}
</script>

<template>
    <div
        class="fixed inset-0 z-[75] flex items-center justify-center bg-[#2a4858]/20 p-4 backdrop-blur-sm"
        @click.self="emit('close')"
    >
        <section
            class="flex max-h-[90vh] w-full max-w-6xl flex-col overflow-hidden rounded-lg bg-white shadow-2xl"
        >
            <header
                class="flex items-start justify-between gap-4 border-b border-slate-100 p-5"
            >
                <div>
                    <p
                        class="text-xs font-bold tracking-widest text-slate-400 uppercase"
                    >
                        Stock Adjustment Detail
                    </p>
                    <h2 class="mt-1 text-xl font-bold text-[#2a4858]">
                        {{ adjustment.code }}
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ typeLabel(adjustment.type) }} /
                        {{ adjustment.warehouse }}
                    </p>
                </div>
                <Button
                    type="button"
                    variant="outline"
                    class="h-9 w-9 rounded-lg border-slate-100 p-0 text-slate-400"
                    title="Close"
                    @click="emit('close')"
                >
                    <X class="size-4" />
                </Button>
            </header>

            <div class="min-h-0 flex-1 overflow-y-auto p-5">
                <div class="mb-4 grid gap-3 text-sm sm:grid-cols-3">
                    <div class="space-y-1 rounded-lg bg-slate-50 p-3">
                        <p class="text-xs font-bold text-slate-400 uppercase">
                            Schedule
                        </p>
                        <p class="font-bold text-[#2a4858]">
                            Date: {{ adjustment.displayDate ?? '-' }}
                        </p>
                        <p class="text-xs font-semibold text-[#007882]">
                            {{ adjustment.locationCode }} /
                            {{ adjustment.location }}
                        </p>
                    </div>
                    <div class="space-y-2 rounded-lg bg-slate-50 p-3">
                        <p class="text-xs font-bold text-slate-400 uppercase">
                            Status
                        </p>
                        <span
                            class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold uppercase"
                            :class="statusClass(adjustment.status)"
                        >
                            {{ statusLabel(adjustment.status) }}
                        </span>
                        <p class="text-xs font-semibold text-slate-500">
                            {{ adjustment.itemCount }} item{{
                                adjustment.itemCount === 1 ? '' : 's'
                            }}
                            /
                            {{ numberValue(adjustment.totalQuantity) }} qty
                        </p>
                    </div>
                    <div class="space-y-1 rounded-lg bg-slate-50 p-3">
                        <p class="text-xs font-bold text-slate-400 uppercase">
                            People
                        </p>
                        <p class="font-bold text-[#2a4858]">
                            Created: {{ adjustment.createdBy ?? '-' }}
                        </p>
                        <p class="text-xs font-semibold text-slate-500">
                            <span v-if="actionInfo">
                                {{ actionInfo.status
                                }}<template v-if="actionInfo.at">
                                    at
                                    {{ actionInfo.at }}</template
                                >
                                by {{ actionInfo.name ?? '-' }}
                            </span>
                            <span v-else>-</span>
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th
                                    class="py-3 pr-4 text-xs font-bold text-slate-400 uppercase"
                                >
                                    Item
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold text-slate-400 uppercase"
                                >
                                    Location
                                </th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-bold text-slate-400 uppercase"
                                >
                                    System
                                </th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-bold text-slate-400 uppercase"
                                >
                                    Difference
                                </th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-bold text-slate-400 uppercase"
                                >
                                    Cost
                                </th>
                                <th
                                    class="py-3 pl-4 text-right text-xs font-bold text-slate-400 uppercase"
                                >
                                    Total
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="line in adjustment.lines"
                                :key="line.id"
                                class="border-b border-slate-50"
                            >
                                <td class="py-4 pr-4">
                                    <div class="font-bold text-[#2a4858]">
                                        {{ line.itemName }}
                                    </div>
                                    <p class="mt-1 text-xs text-slate-400">
                                        {{ line.itemCode }} / {{ line.unit }}
                                    </p>
                                </td>
                                <td class="px-4 py-4">
                                    <p class="font-bold text-[#2a4858]">
                                        {{ line.locationCode ?? '-' }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-400">
                                        {{ line.location ?? '' }}
                                    </p>
                                </td>
                                <td
                                    class="px-4 py-4 text-right font-bold text-[#2a4858]"
                                >
                                    {{ numberValue(line.systemQuantity) }}
                                </td>
                                <td
                                    class="px-4 py-4 text-right font-bold"
                                    :class="
                                        line.differenceQuantity >= 0
                                            ? 'text-green-600'
                                            : 'text-red-600'
                                    "
                                >
                                    {{ numberValue(line.differenceQuantity) }}
                                </td>
                                <td
                                    class="px-4 py-4 text-right font-mono font-bold text-[#2a4858]"
                                >
                                    {{ money(line.unitCost) }}
                                </td>
                                <td
                                    class="py-4 pl-4 text-right font-mono font-bold text-[#007882]"
                                >
                                    {{ money(line.totalCost) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <footer
                class="flex flex-col justify-between gap-3 border-t border-slate-100 bg-slate-50 p-5 sm:flex-row sm:items-center"
            >
                <div class="flex items-center gap-2 text-sm text-slate-500">
                    <CalendarDays class="size-4 text-[#007882]" />
                    {{ adjustment.lines.length }} item line{{
                        adjustment.lines.length === 1 ? '' : 's'
                    }}
                </div>
                <div class="text-right">
                    <p
                        class="text-xs font-bold tracking-widest text-slate-400 uppercase"
                    >
                        Adjustment Value
                    </p>
                    <p class="text-xl font-bold text-[#007882]">
                        {{ money(adjustment.totalCost) }}
                    </p>
                </div>
            </footer>
        </section>
    </div>
</template>
