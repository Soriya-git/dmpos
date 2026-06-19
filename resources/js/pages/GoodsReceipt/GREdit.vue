<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { PackageOpen, Plus, Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';

type PoLine = {
    id: number;
    item_name?: string | null;
    item_code?: string | null;
    unit_code?: string | null;
    quantity_ordered: number;
    quantity_available: number;
};

type GRLine = {
    id: number;
    purchase_order_line_id: number;
    stock_location_id?: number | null;
    item_name?: string | null;
    item_code?: string | null;
    unit_code?: string | null;
    quantity_received: number;
};

type GoodsReceipt = {
    id: number;
    receipt_no: string;
    purchase_order_no?: string | null;
    note?: string | null;
    po_lines?: PoLine[] | null;
    lines: GRLine[];
};

type StagingLocation = {
    id: number;
    name: string;
    code?: string | null;
    warehouse_name?: string | null;
};

const props = defineProps<{
    receipt: GoodsReceipt;
    stagingLocations: StagingLocation[];
}>();

const form = useForm({
    note: props.receipt.note ?? '',
    lines: props.receipt.lines.map((l, i) => ({
        allocation_key: `${l.purchase_order_line_id}-${l.id}-${i}`,
        purchase_order_line_id: l.purchase_order_line_id,
        stock_location_id: l.stock_location_id ?? (props.stagingLocations[0]?.id ?? ''),
        quantity_received: l.quantity_received,
        selected: l.quantity_received > 0,
    })) as {
        allocation_key: string;
        purchase_order_line_id: number;
        stock_location_id: number | '';
        quantity_received: number;
        selected: boolean;
    }[],
});

function poLineFor(lineId: number) {
    return props.receipt.po_lines?.find((l) => l.id === lineId);
}

function allocationsFor(lineId: number) {
    return form.lines.filter((l) => l.purchase_order_line_id === lineId);
}

function allocatedQuantity(lineId: number, exceptKey?: string) {
    return form.lines
        .filter((l) => l.purchase_order_line_id === lineId && l.allocation_key !== exceptKey)
        .reduce((sum, l) => sum + Number(l.quantity_received || 0), 0);
}

function maxQuantityFor(allocation: { purchase_order_line_id: number; allocation_key: string }) {
    const poLine = poLineFor(allocation.purchase_order_line_id);
    const available = poLine?.quantity_available ?? 9999;
    return Math.max(0, available - allocatedQuantity(allocation.purchase_order_line_id, allocation.allocation_key));
}

function canAddAllocation(lineId: number) {
    const poLine = poLineFor(lineId);
    return (poLine?.quantity_available ?? 0) - allocatedQuantity(lineId) > 0;
}

function addAllocation(lineId: number) {
    const remaining = maxQuantityFor({ purchase_order_line_id: lineId, allocation_key: '' });
    if (remaining <= 0) return;

    form.lines.push({
        allocation_key: `${lineId}-new-${Date.now()}`,
        purchase_order_line_id: lineId,
        stock_location_id: props.stagingLocations[0]?.id ?? '',
        quantity_received: remaining,
        selected: true,
    });
}

function removeAllocation(allocationKey: string) {
    const allocation = form.lines.find((l) => l.allocation_key === allocationKey);
    if (!allocation || allocationsFor(allocation.purchase_order_line_id).length <= 1) return;
    form.lines = form.lines.filter((l) => l.allocation_key !== allocationKey);
}

function updateSelected(allocationKey: string, event: Event) {
    const line = form.lines.find((l) => l.allocation_key === allocationKey);
    if (!line) return;
    line.selected = (event.target as HTMLInputElement).checked;
    if (!line.selected) {
        line.quantity_received = 0;
    } else if (line.quantity_received <= 0) {
        line.quantity_received = maxQuantityFor(line);
    }
}

function updateQuantity(allocationKey: string, event: Event) {
    const line = form.lines.find((l) => l.allocation_key === allocationKey);
    if (!line) return;
    const v = Number((event.target as HTMLInputElement).value || 0);
    line.quantity_received = Math.min(Math.max(0, v), maxQuantityFor(line));
}

function updateStagingZone(allocationKey: string, event: Event) {
    const line = form.lines.find((l) => l.allocation_key === allocationKey);
    if (!line) return;
    const v = (event.target as HTMLSelectElement).value;
    line.stock_location_id = v ? Number(v) : '';
}

function submit() {
    form.patch(`/goods-receipts/${props.receipt.id}`, { preserveScroll: true });
}

const canSubmit = computed(() =>
    form.lines.some((l) => l.selected && Number(l.quantity_received) > 0) &&
    form.lines
        .filter((l) => l.selected && Number(l.quantity_received) > 0)
        .every((l) => l.stock_location_id) &&
    !form.processing,
);

defineExpose({ submit, isProcessing: computed(() => form.processing), canSubmit });
</script>

<template>
    <div class="w-full">
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-4 2xl:gap-8">

            <!-- Left: Receipt info + Note -->
            <aside class="space-y-6 xl:col-span-1">
                <div class="rounded-lg border border-slate-100 bg-white p-6 shadow-sm">
                    <h2 class="mb-4 text-sm font-bold text-slate-700 uppercase">Source PO</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="mb-1 block text-xs font-bold text-slate-500 uppercase">
                                Purchase Order
                            </label>
                            <div class="flex h-10 items-center rounded-lg border border-slate-200 bg-slate-50 px-3 font-mono text-sm font-semibold text-slate-700">
                                {{ receipt.purchase_order_no ?? '-' }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-slate-100 bg-white p-6 shadow-sm">
                    <h2 class="mb-4 text-sm font-bold text-slate-700 uppercase">Receipt Details</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="mb-1 block text-xs font-bold text-slate-500 uppercase">
                                Receipt Number
                            </label>
                            <div class="flex h-10 items-center rounded-lg border border-slate-200 bg-slate-50 px-3 font-mono text-sm font-bold text-slate-500">
                                {{ receipt.receipt_no }}
                            </div>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold text-slate-500 uppercase">
                                Note
                            </label>
                            <textarea
                                v-model="form.note"
                                rows="3"
                                class="min-h-20 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#007882] focus:ring-2 focus:ring-[#007882]/20"
                                placeholder="Optional receipt note"
                            ></textarea>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Right: Items table -->
            <section class="space-y-6 xl:col-span-3">
                <div class="overflow-hidden rounded-lg border border-slate-100 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50 p-4">
                        <h2 class="text-xs font-bold tracking-wider text-slate-700 uppercase">
                            Verify Inbound Items
                        </h2>
                        <span v-if="form.lines.length > 0" class="text-xs text-slate-400">
                            {{ form.lines.filter((l) => l.selected).length }} /
                            {{ form.lines.length }} selected
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="border-b border-slate-100 text-slate-500">
                                <tr>
                                    <th class="w-12 px-4 py-3 text-center font-semibold">Use</th>
                                    <th class="min-w-48 px-4 py-3 text-left font-semibold">Product / SKU</th>
                                    <th class="px-4 py-3 text-center font-semibold">Ordered</th>
                                    <th class="px-4 py-3 text-center font-semibold text-[#007882]">Qty Received</th>
                                    <th class="px-4 py-3 text-left font-semibold text-[#007882]">Staging Zone</th>
                                    <th class="w-20 px-4 py-3 text-center font-semibold">Split</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr
                                    v-for="allocation in form.lines"
                                    :key="allocation.allocation_key"
                                    class="transition hover:bg-slate-50/50"
                                >
                                    <td class="px-4 py-4 text-center">
                                        <input
                                            type="checkbox"
                                            :checked="allocation.selected"
                                            class="h-4 w-4 rounded border-slate-300 text-[#007882] focus:ring-[#007882]"
                                            @change="updateSelected(allocation.allocation_key, $event)"
                                        />
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="font-bold text-[#2a4858]">
                                            {{ poLineFor(allocation.purchase_order_line_id)?.item_name ?? 'Item' }}
                                        </div>
                                        <p class="mt-1 font-mono text-xs text-slate-400">
                                            {{ poLineFor(allocation.purchase_order_line_id)?.item_code ?? '-' }}
                                            <span v-if="poLineFor(allocation.purchase_order_line_id)?.unit_code">
                                                / {{ poLineFor(allocation.purchase_order_line_id)?.unit_code }}
                                            </span>
                                        </p>
                                    </td>
                                    <td class="px-4 py-4 text-center font-bold text-slate-400">
                                        {{ poLineFor(allocation.purchase_order_line_id)?.quantity_ordered ?? '-' }}
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <input
                                            type="number"
                                            min="0"
                                            :value="allocation.quantity_received"
                                            :disabled="!allocation.selected"
                                            class="w-24 rounded-lg border-2 border-[#007882] py-1 text-center font-bold text-[#007882]"
                                            :class="{ 'border-slate-200 bg-slate-50 text-slate-400': !allocation.selected }"
                                            @input="updateQuantity(allocation.allocation_key, $event)"
                                        />
                                    </td>
                                    <td class="px-4 py-4">
                                        <select
                                            :value="allocation.stock_location_id"
                                            :disabled="!allocation.selected"
                                            class="h-9 w-full min-w-44 rounded-lg border border-slate-200 bg-white px-2 text-sm outline-none focus:border-[#007882] focus:ring-2 focus:ring-[#007882]/20 disabled:bg-slate-50 disabled:text-slate-400"
                                            @change="updateStagingZone(allocation.allocation_key, $event)"
                                        >
                                            <option value="" disabled>Select staging zone</option>
                                            <option
                                                v-for="location in stagingLocations"
                                                :key="location.id"
                                                :value="location.id"
                                            >
                                                {{ location.code ?? location.name }} — {{ location.warehouse_name }}
                                            </option>
                                        </select>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex items-center justify-center gap-1">
                                            <button
                                                type="button"
                                                :disabled="!canAddAllocation(allocation.purchase_order_line_id)"
                                                class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-[#007882] hover:bg-[#007882]/10 disabled:cursor-not-allowed disabled:text-slate-300"
                                                title="Add staging split"
                                                @click="addAllocation(allocation.purchase_order_line_id)"
                                            >
                                                <Plus class="h-4 w-4" />
                                            </button>
                                            <button
                                                type="button"
                                                :disabled="allocationsFor(allocation.purchase_order_line_id).length <= 1"
                                                class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-rose-400 hover:bg-rose-50 disabled:cursor-not-allowed disabled:text-slate-300"
                                                title="Remove staging split"
                                                @click="removeAllocation(allocation.allocation_key)"
                                            >
                                                <Trash2 class="h-4 w-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="form.lines.length === 0">
                                    <td colspan="6" class="px-6 py-16 text-center text-sm text-slate-400">
                                        <PackageOpen class="mx-auto mb-3 size-8 text-slate-300" />
                                        No lines found for this goods receipt.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <p v-if="form.errors.lines" class="text-sm font-medium text-rose-600">
                    {{ form.errors.lines }}
                </p>
            </section>
        </div>
    </div>
</template>
