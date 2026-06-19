<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ChevronDown, FileText, Plus, Trash2, UserRound } from 'lucide-vue-next';
import { computed } from 'vue';

type ReceiptLine = {
    item_id: number;
    unit_id: number;
    item_name?: string | null;
    item_code?: string | null;
    unit_code?: string | null;
    quantity_received: number;
    quantity_putaway: number;
    quantity_remaining: number;
};

type Receipt = {
    id: number;
    receipt_no: string;
    purchase_order_no?: string | null;
    staging_area?: string | null;
    total_remaining: number;
    lines: ReceiptLine[];
};

type PutawayLine = {
    id: number;
    item_id: number;
    unit_id: number;
    to_location_id?: number | null;
    quantity: number;
};

type Putaway = {
    id: number;
    transfer_no: string;
    goods_receipt_id?: number | null;
    goods_receipt_no?: string | null;
    priority: string;
    assigned_to_id?: number | null;
    note?: string | null;
    receipt?: Receipt | null;
    lines: PutawayLine[];
};

type StorageLocation = {
    id: number;
    name: string;
    code?: string | null;
    warehouse_name?: string | null;
};

type Staff = {
    id: number;
    name: string;
};

const props = defineProps<{
    putaway: Putaway;
    storageLocations: StorageLocation[];
    staff: Staff[];
}>();

const form = useForm({
    goods_receipt_id: props.putaway.goods_receipt_id ?? '',
    assigned_to: props.putaway.assigned_to_id ?? '',
    priority: props.putaway.priority ?? 'normal',
    note: props.putaway.note ?? '',
    lines: props.putaway.lines.map((l, i) => ({
        allocation_key: `existing-${l.id}-${i}`,
        item_id: l.item_id,
        unit_id: l.unit_id,
        to_location_id: l.to_location_id ?? (props.storageLocations[0]?.id ?? ''),
        quantity: l.quantity,
        selected: l.quantity > 0,
    })) as {
        allocation_key: string;
        item_id: number;
        unit_id: number;
        to_location_id: number | '';
        quantity: number;
        selected: boolean;
    }[],
});

function receiptLineFor(itemId: number) {
    return props.putaway.receipt?.lines.find((l) => l.item_id === itemId);
}

function allocationsFor(itemId: number) {
    return form.lines.filter((l) => l.item_id === itemId);
}

function allocatedQuantity(itemId: number, exceptKey?: string) {
    return form.lines
        .filter((l) => l.item_id === itemId && l.selected && l.allocation_key !== exceptKey)
        .reduce((sum, l) => sum + Number(l.quantity || 0), 0);
}

function maxQuantityFor(allocation: { item_id: number; allocation_key: string }) {
    const receiptLine = receiptLineFor(allocation.item_id);
    return Math.max(
        0,
        Number(receiptLine?.quantity_remaining ?? 0) - allocatedQuantity(allocation.item_id, allocation.allocation_key),
    );
}

function canAddAllocation(itemId: number) {
    const receiptLine = receiptLineFor(itemId);
    return receiptLine ? Number(receiptLine.quantity_remaining) - allocatedQuantity(itemId) > 0 : false;
}

function addAllocation(itemId: number) {
    const receiptLine = receiptLineFor(itemId);
    const remaining = Number(receiptLine?.quantity_remaining ?? 0) - allocatedQuantity(itemId);
    if (!receiptLine || remaining <= 0) return;

    form.lines.push({
        allocation_key: `${itemId}-new-${Date.now()}`,
        item_id: itemId,
        unit_id: receiptLine.unit_id,
        to_location_id: props.storageLocations[0]?.id ?? '',
        quantity: remaining,
        selected: true,
    });
}

function removeAllocation(allocationKey: string) {
    const allocation = form.lines.find((l) => l.allocation_key === allocationKey);
    if (!allocation || allocationsFor(allocation.item_id).length <= 1) return;
    form.lines = form.lines.filter((l) => l.allocation_key !== allocationKey);
}

function setSelected(allocationKey: string, event: Event) {
    const line = form.lines.find((l) => l.allocation_key === allocationKey);
    if (line) line.selected = (event.target as HTMLInputElement).checked;
}

function setQuantity(allocationKey: string, event: Event) {
    const line = form.lines.find((l) => l.allocation_key === allocationKey);
    if (line) {
        const v = Number((event.target as HTMLInputElement).value || 0);
        line.quantity = Math.min(Math.max(0, v), maxQuantityFor(line));
    }
}

function setDestination(allocationKey: string, event: Event) {
    const line = form.lines.find((l) => l.allocation_key === allocationKey);
    if (line) {
        const v = (event.target as HTMLSelectElement).value;
        line.to_location_id = v ? Number(v) : '';
    }
}

const selectedLines = computed(() => form.lines.filter((l) => l.selected && Number(l.quantity) > 0));

const canSubmit = computed(() =>
    Boolean(form.goods_receipt_id) &&
    selectedLines.value.length > 0 &&
    selectedLines.value.every((l) => l.to_location_id) &&
    !form.processing,
);

function submit() {
    form.patch(`/putaway/${props.putaway.id}`, { preserveScroll: true });
}

defineExpose({ submit, isProcessing: computed(() => form.processing), canSubmit });
</script>

<template>
    <div class="w-full">
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-4 2xl:gap-8">

            <!-- Left: Config -->
            <aside class="space-y-6 xl:col-span-1">
                <!-- GR Info (fixed) -->
                <div class="rounded-lg border border-slate-100 bg-white p-6 shadow-sm">
                    <h2 class="mb-4 text-sm font-bold text-slate-700 uppercase">Source GR</h2>
                    <div class="space-y-4">
                        <div class="flex items-center gap-2 rounded-lg border border-[#007882]/10 bg-[#007882]/5 p-3">
                            <FileText class="size-4 shrink-0 text-[#007882]" />
                            <div>
                                <p class="text-xs font-bold text-[#007882]">Goods Receipt</p>
                                <p class="font-bold text-slate-800">{{ putaway.goods_receipt_no ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Task Config -->
                <div class="rounded-lg border border-slate-100 bg-white p-6 shadow-sm">
                    <h2 class="mb-4 text-sm font-bold text-slate-700 uppercase">Task Configuration</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="mb-1.5 block text-xs font-bold text-slate-500 uppercase">
                                Assign Putaway Staff
                            </label>
                            <div class="relative">
                                <UserRound class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400" />
                                <select
                                    v-model="form.assigned_to"
                                    class="h-10 w-full appearance-none rounded-lg border border-slate-200 bg-white pr-8 pl-9 text-sm outline-none focus:border-[#007882] focus:ring-2 focus:ring-[#007882]/20"
                                >
                                    <option value="">Select Warehouse Operator...</option>
                                    <option v-for="user in staff" :key="user.id" :value="user.id">
                                        {{ user.name }}
                                    </option>
                                </select>
                                <ChevronDown class="pointer-events-none absolute top-1/2 right-3 size-3.5 -translate-y-1/2 text-slate-300" />
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-bold text-slate-500 uppercase">Task Priority</label>
                            <div class="flex gap-2">
                                <button
                                    v-for="p in ['low', 'normal', 'urgent']"
                                    :key="p"
                                    type="button"
                                    class="flex-1 rounded-lg border py-2 text-xs font-bold uppercase transition"
                                    :class="form.priority === p
                                        ? 'border-[#007882] bg-[#007882] text-white shadow-sm'
                                        : 'border-slate-100 text-slate-600 hover:border-slate-200'"
                                    @click="form.priority = p"
                                >
                                    {{ p }}
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-xs font-bold text-slate-500 uppercase">
                                Instruction / Note
                            </label>
                            <textarea
                                v-model="form.note"
                                rows="3"
                                placeholder="Handling instructions..."
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#007882] focus:ring-2 focus:ring-[#007882]/20"
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
                            Inventory to Allocate
                        </h2>
                        <span v-if="form.lines.length > 0" class="text-xs text-slate-400">
                            {{ selectedLines.length }} / {{ form.lines.length }} selected
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="border-b border-slate-100 text-slate-500">
                                <tr>
                                    <th class="w-12 px-4 py-3 text-center font-semibold"></th>
                                    <th class="min-w-48 px-4 py-3 text-left font-semibold">Product Details</th>
                                    <th class="px-4 py-3 text-center font-semibold">Remaining</th>
                                    <th class="px-4 py-3 text-center font-semibold text-[#007882]">Qty to Move</th>
                                    <th class="px-4 py-3 text-left font-semibold text-[#007882]">Destination Bin</th>
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
                                            @change="setSelected(allocation.allocation_key, $event)"
                                        />
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="font-bold text-[#2a4858]">
                                            {{ receiptLineFor(allocation.item_id)?.item_name ?? 'Item' }}
                                        </div>
                                        <p class="mt-1 font-mono text-xs text-slate-400">
                                            SKU: {{ receiptLineFor(allocation.item_id)?.item_code ?? '-' }}
                                            <span class="text-[#007882]">/ {{ receiptLineFor(allocation.item_id)?.unit_code ?? 'UNIT' }}</span>
                                        </p>
                                    </td>
                                    <td class="px-4 py-4 text-center font-bold italic text-slate-400">
                                        {{ receiptLineFor(allocation.item_id)?.quantity_remaining ?? 0 }} units
                                        <div v-if="allocationsFor(allocation.item_id).length > 1" class="mt-0.5 text-xs font-normal text-slate-400">
                                            Split
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <input
                                            type="number"
                                            min="0"
                                            :max="maxQuantityFor(allocation)"
                                            :value="allocation.quantity"
                                            :disabled="!allocation.selected"
                                            class="w-24 rounded-lg border-2 border-[#007882] py-1 text-center font-bold text-[#007882]"
                                            :class="{ 'border-slate-200 bg-slate-50 text-slate-400': !allocation.selected }"
                                            @input="setQuantity(allocation.allocation_key, $event)"
                                        />
                                    </td>
                                    <td class="px-4 py-4">
                                        <select
                                            :value="allocation.to_location_id"
                                            :disabled="!allocation.selected"
                                            class="h-9 w-full min-w-44 rounded-lg border border-slate-200 bg-white px-2 text-sm outline-none focus:border-[#007882] focus:ring-2 focus:ring-[#007882]/20 disabled:bg-slate-50 disabled:text-slate-400"
                                            @change="setDestination(allocation.allocation_key, $event)"
                                        >
                                            <option value="" disabled>Select bin</option>
                                            <option
                                                v-for="location in storageLocations"
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
                                                :disabled="!canAddAllocation(allocation.item_id)"
                                                class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-[#007882] hover:bg-[#007882]/10 disabled:cursor-not-allowed disabled:text-slate-300"
                                                title="Add destination split"
                                                @click="addAllocation(allocation.item_id)"
                                            >
                                                <Plus class="h-4 w-4" />
                                            </button>
                                            <button
                                                type="button"
                                                :disabled="allocationsFor(allocation.item_id).length <= 1"
                                                class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-rose-400 hover:bg-rose-50 disabled:cursor-not-allowed disabled:text-slate-300"
                                                title="Remove split"
                                                @click="removeAllocation(allocation.allocation_key)"
                                            >
                                                <Trash2 class="h-4 w-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="form.lines.length === 0">
                                    <td colspan="6" class="px-6 py-16 text-center text-sm text-slate-400">
                                        No items found for this putaway task.
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
