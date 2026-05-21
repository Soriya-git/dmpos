<script setup lang="ts">
import { CalendarDays, X } from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';

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
    created_by?: string | null;
    approved_by?: string | null;
    approved_at?: string | null;
    display_approved_at?: string | null;
    rejected_by?: string | null;
    rejected_at?: string | null;
    display_rejected_at?: string | null;
    cancelled_by?: string | null;
    cancelled_at?: string | null;
    display_cancelled_at?: string | null;
    grand_total: number;
    lines: PurchaseOrderLine[];
};

const props = defineProps<{
    order: PurchaseOrder;
}>();

const emit = defineEmits<{
    close: [];
}>();

const actionInfo = computed(() => {
    const byStatus = {
        approved: {
            status: 'Approved',
            name: props.order.approved_by,
            at: actionDate(
                props.order.approved_at,
                props.order.display_approved_at,
            ),
        },
        rejected: {
            status: 'Rejected',
            name: props.order.rejected_by,
            at: actionDate(
                props.order.rejected_at,
                props.order.display_rejected_at,
            ),
        },
        cancelled: {
            status: 'Cancelled',
            name: props.order.cancelled_by,
            at: actionDate(
                props.order.cancelled_at,
                props.order.display_cancelled_at,
            ),
        },
    } as const;

    return byStatus[props.order.status as keyof typeof byStatus] ?? null;
});

function actionDate(
    value: string | null | undefined,
    fallback: string | null | undefined,
) {
    const source = value ?? fallback;

    if (!source) return null;

    const parsed = new Date(source.replace(' ', 'T'));

    if (!Number.isNaN(parsed.getTime())) {
        return parsed.toLocaleDateString(undefined, {
            month: 'short',
            day: '2-digit',
            year: 'numeric',
        });
    }

    return fallback?.match(/^[A-Za-z]{3}\s+\d{1,2},\s+\d{4}/)?.[0] ?? source;
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
                        Purchase Order Detail
                    </p>
                    <h2 class="mt-1 text-xl font-bold text-[#2a4858]">
                        {{ order.po_no }}
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ order.supplier_name ?? 'No supplier' }}
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
                            Order:
                            {{
                                order.display_order_date ??
                                order.order_date ??
                                '-'
                            }}
                        </p>
                        <p class="text-xs font-semibold text-amber-700">
                            Expected:
                            {{
                                order.display_expected_date ??
                                order.expected_date ??
                                '-'
                            }}
                        </p>
                    </div>
                    <div class="space-y-2 rounded-lg bg-slate-50 p-3">
                        <p class="text-xs font-bold text-slate-400 uppercase">
                            Status
                        </p>
                        <span
                            class="mt-1 inline-flex rounded-full px-2.5 py-1 text-xs font-bold uppercase"
                            :class="statusClass(order.status)"
                        >
                            {{ statusLabel(order.status) }}
                        </span>
                    </div>
                    <div class="space-y-1 rounded-lg bg-slate-50 p-3">
                        <p class="text-xs font-bold text-slate-400 uppercase">
                            People
                        </p>
                        <p class="font-bold text-[#2a4858]">
                            Created: {{ order.created_by ?? '-' }}
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
                                    class="px-4 py-3 text-right text-xs font-bold text-slate-400 uppercase"
                                >
                                    Ordered
                                </th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-bold text-slate-400 uppercase"
                                >
                                    Received
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
                                v-for="line in order.lines"
                                :key="line.id"
                                class="border-b border-slate-50"
                            >
                                <td class="py-4 pr-4">
                                    <div class="font-bold text-[#2a4858]">
                                        {{ line.item_name ?? 'Item' }}
                                    </div>
                                    <p class="mt-1 text-xs text-slate-400">
                                        {{ line.item_code ?? '-' }} /
                                        {{ line.unit_code ?? '-' }}
                                    </p>
                                </td>
                                <td
                                    class="px-4 py-4 text-right font-bold text-[#2a4858]"
                                >
                                    {{ numberValue(line.quantity_ordered) }}
                                </td>
                                <td
                                    class="px-4 py-4 text-right font-bold text-[#2a4858]"
                                >
                                    {{ numberValue(line.quantity_received) }}
                                </td>
                                <td
                                    class="px-4 py-4 text-right font-mono font-bold text-[#2a4858]"
                                >
                                    {{ money(line.unit_cost) }}
                                </td>
                                <td
                                    class="py-4 pl-4 text-right font-mono font-bold text-[#007882]"
                                >
                                    {{ money(line.line_total) }}
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
                    {{ order.lines.length }} item line{{
                        order.lines.length === 1 ? '' : 's'
                    }}
                </div>
                <div class="text-right">
                    <p
                        class="text-xs font-bold tracking-widest text-slate-400 uppercase"
                    >
                        Grand Total
                    </p>
                    <p class="text-xl font-bold text-[#007882]">
                        {{ money(order.grand_total) }}
                    </p>
                </div>
            </footer>
        </section>
    </div>
</template>
