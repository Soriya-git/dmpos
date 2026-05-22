<script setup lang="ts">
import { X } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

type Keep = {
    id: number;
    transfer_no: string;
    created_at?: string | null;
    approved_at?: string | null;
    invoice_no?: string | null;
    customer_name: string;
    customer_phone?: string | null;
    created_by?: string | null;
    approved_by?: string | null;
    item_count: number;
    total_quantity: number;
    status: string;
    lines: {
        id: number;
        item_name?: string | null;
        item_code?: string | null;
        unit_code?: string | null;
        to_location?: string | null;
        quantity: number;
    }[];
};

defineProps<{ keep: Keep }>();
const emit = defineEmits<{ close: [] }>();

function statusLabel(status: string) {
    return status
        .split('_')
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
}

function statusClass(status: string) {
    const classes: Record<string, string> = {
        draft: 'bg-slate-100 text-slate-500',
        received: 'bg-emerald-100 text-emerald-700',
        rejected: 'bg-red-100 text-red-700',
        cancelled: 'bg-rose-100 text-rose-700',
    };

    return classes[status] ?? 'bg-slate-100 text-slate-600';
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
                        Customer Keep Stock Detail
                    </p>
                    <h2 class="mt-1 text-xl font-bold text-[#2a4858]">
                        {{ keep.transfer_no }}
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Invoice: {{ keep.invoice_no ?? '-' }} /
                        {{ keep.customer_name }}
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
                            Document
                        </p>
                        <p class="font-bold text-[#2a4858]">
                            Created: {{ keep.created_at ?? '-' }}
                        </p>
                        <p class="text-xs font-semibold text-blue-700">
                            Source: {{ keep.invoice_no ?? '-' }}
                        </p>
                    </div>
                    <div class="space-y-2 rounded-lg bg-slate-50 p-3">
                        <p class="text-xs font-bold text-slate-400 uppercase">
                            Status
                        </p>
                        <span
                            class="inline-flex rounded px-2.5 py-1 text-xs font-bold uppercase"
                            :class="statusClass(keep.status)"
                        >
                            {{ statusLabel(keep.status) }}
                        </span>
                        <p class="text-xs font-semibold text-slate-500">
                            {{ keep.item_count }} item(s) /
                            {{ keep.total_quantity }} qty
                        </p>
                    </div>
                    <div class="space-y-1 rounded-lg bg-slate-50 p-3">
                        <p class="text-xs font-bold text-slate-400 uppercase">
                            Customer
                        </p>
                        <p class="font-bold text-[#2a4858]">
                            {{ keep.customer_name }}
                        </p>
                        <p class="text-xs font-semibold text-slate-500">
                            {{ keep.customer_phone ?? '-' }}
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
                                    Qty
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold text-slate-400 uppercase"
                                >
                                    Customer Location
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="line in keep.lines"
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
                                    {{ line.quantity }}
                                </td>
                                <td class="px-4 py-4 font-bold text-[#007882]">
                                    {{ line.to_location ?? '-' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</template>
