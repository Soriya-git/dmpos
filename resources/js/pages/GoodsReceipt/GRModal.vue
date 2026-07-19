<script setup lang="ts">
import { X } from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';

type GoodsReceipt = {
    id: number;
    receipt_no: string;
    purchase_order_no?: string | null;
    status: string;
    created_at?: string | null;
    updated_at?: string | null;
    received_at?: string | null;
    staging_area?: string | null;
    operator?: string | null;
    cancelled_by?: string | null;
    line_count: number;
    lines: {
        id: number;
        item_name?: string | null;
        item_code?: string | null;
        unit_code?: string | null;
        staging_area?: string | null;
        quantity_received: number;
    }[];
};

const props = defineProps<{
    receipt: GoodsReceipt;
}>();

const emit = defineEmits<{
    close: [];
}>();

const actionInfo = computed(() => {
    if (props.receipt.status === 'cancelled') {
        return {
            status: 'Cancelled',
            name: props.receipt.cancelled_by,
            at: actionDate(props.receipt.updated_at),
        };
    }

    if (props.receipt.status === 'rejected') {
        return {
            status: 'Rejected',
            name: props.receipt.operator,
            at: actionDate(props.receipt.updated_at),
        };
    }

    if (
        ['approved', 'partially_received', 'received'].includes(
            props.receipt.status,
        )
    ) {
        return {
            status: statusLabel(props.receipt.status),
            name: props.receipt.operator,
            at: actionDate(
                props.receipt.received_at ?? props.receipt.updated_at,
            ),
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

function statusClass(status: string) {
    const classes: Record<string, string> = {
        draft: 'bg-slate-100 text-slate-700',
        approved: 'bg-green-100 text-green-700',
        rejected: 'bg-red-100 text-red-700',
        in_progress: 'bg-amber-100 text-amber-700',
        partially_received: 'bg-blue-100 text-blue-700',
        received: 'bg-emerald-100 text-emerald-700',
        cancelled: 'bg-rose-100 text-rose-700',
    };

    return classes[status] ?? 'bg-slate-100 text-slate-700';
}

function statusLabel(status: string) {
    if (status === 'approved' || status === 'received') return 'Received';
    return status
        .split('_')
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
}
</script>

<template>
    <div
        class="fixed inset-0 z-[75] flex items-center justify-center bg-[#2a4858]/20 p-4 backdrop-blur-sm"
        @click.self="emit('close')"
    >
        <section
            class="flex max-h-[90vh] w-full max-w-5xl flex-col overflow-hidden rounded-lg bg-white shadow-2xl"
        >
            <header
                class="flex items-start justify-between gap-4 border-b border-slate-100 p-5"
            >
                <div>
                    <p
                        class="text-xs font-bold tracking-widest text-slate-400 uppercase"
                    >
                        Goods Receipt Detail
                    </p>
                    <h2 class="mt-1 text-xl font-bold text-[#2a4858]">
                        {{ receipt.receipt_no }}
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Source PO: {{ receipt.purchase_order_no ?? '-' }}
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
                            Saved: {{ receipt.created_at ?? '-' }}
                        </p>
                        <p class="text-xs font-semibold text-emerald-700">
                            Received: {{ receipt.received_at ?? '-' }}
                        </p>
                    </div>
                    <div class="space-y-2 rounded-lg bg-slate-50 p-3">
                        <p class="text-xs font-bold text-slate-400 uppercase">
                            Status
                        </p>
                        <span
                            class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold uppercase"
                            :class="statusClass(receipt.status)"
                        >
                            {{ statusLabel(receipt.status) }}
                        </span>
                        <p class="text-xs font-semibold text-slate-500">
                            {{ receipt.line_count }} SKU{{
                                receipt.line_count === 1 ? '' : 's'
                            }}
                            /
                            {{ receipt.staging_area ?? '-' }}
                        </p>
                    </div>
                    <div class="space-y-1 rounded-lg bg-slate-50 p-3">
                        <p class="text-xs font-bold text-slate-400 uppercase">
                            People
                        </p>
                        <p class="font-bold text-[#2a4858]">
                            Created: {{ receipt.operator ?? '-' }}
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
                                    Received
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold text-slate-400 uppercase"
                                >
                                    Staging Zone
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="line in receipt.lines"
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
                                    {{ line.quantity_received }}
                                </td>
                                <td class="px-4 py-4 font-bold text-[#2a4858]">
                                    {{ line.staging_area ?? '-' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</template>
