<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    ChevronDown,
    FileText,
    Info,
    Plus,
    Trash2,
    UserRound,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

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

type EditablePutaway = {
    id: number;
    transfer_no: string;
    goods_receipt_id: number;
    priority: string;
    note: string;
    lines: {
        allocation_key: string;
        item_id: number;
        unit_id: number;
        to_location_id: number | '';
        quantity: number;
        selected: boolean;
    }[];
};

const props = defineProps<{
    nextTransferNo: string;
    putaway?: EditablePutaway | null;
    receipt?: Receipt | null;
    receipts: Receipt[];
    storageLocations: StorageLocation[];
    staff: Staff[];
}>();

const breadcrumbs = computed<BreadcrumbItem[]>(() => {
    if (props.putaway) {
        return [
            { title: 'Stock Operations' },
            { title: 'Putaway', href: '/putaway' },
            { title: props.putaway.transfer_no },
            { title: 'Edit' },
        ];
    }
    return [
        { title: 'Stock Operations' },
        { title: 'Putaway', href: '/putaway' },
        { title: 'Create' },
    ];
});

const form = useForm({
    goods_receipt_id:
        props.putaway?.goods_receipt_id ?? props.receipt?.id ?? '',
    assigned_to: '',
    priority: props.putaway?.priority ?? 'normal',
    note: props.putaway?.note ?? '',
    lines: (props.putaway?.lines ?? []) as {
        allocation_key: string;
        item_id: number;
        unit_id: number;
        to_location_id: number | '';
        quantity: number;
        selected: boolean;
    }[],
});

const hydratedDraft = ref(Boolean(props.putaway));

const selectedReceipt = computed(
    () =>
        props.receipts.find((r) => r.id === Number(form.goods_receipt_id)) ??
        props.receipt ??
        null,
);

watch(
    selectedReceipt,
    (receipt) => {
        if (hydratedDraft.value) {
            hydratedDraft.value = false;
            return;
        }
        form.lines =
            receipt?.lines
                .filter((line) => Number(line.quantity_remaining) > 0)
                .map((line) => ({
                    allocation_key: `${line.item_id}-${Date.now()}`,
                    item_id: line.item_id,
                    unit_id: line.unit_id,
                    to_location_id: props.storageLocations[0]?.id ?? '',
                    quantity: Number(line.quantity_remaining),
                    selected: true,
                })) ?? [];
    },
    { immediate: true },
);

const selectedLines = computed(() =>
    form.lines.filter((l) => l.selected && Number(l.quantity) > 0),
);
const totalQuantity = computed(() =>
    selectedLines.value.reduce((t, l) => t + Number(l.quantity || 0), 0),
);
const assignedStaffName = computed(
    () =>
        props.staff.find((u) => u.id === Number(form.assigned_to))?.name ??
        'Unassigned',
);

const canSubmit = computed(
    () =>
        Boolean(form.goods_receipt_id) &&
        selectedLines.value.length > 0 &&
        selectedLines.value.every((l) => l.to_location_id) &&
        !form.processing,
);

function receiptLineFor(itemId: number) {
    return selectedReceipt.value?.lines.find((l) => l.item_id === itemId);
}

function allocationsFor(itemId: number) {
    return form.lines.filter((l) => l.item_id === itemId);
}

function allocatedQuantity(itemId: number, exceptKey?: string) {
    return form.lines
        .filter(
            (l) =>
                l.item_id === itemId &&
                l.selected &&
                l.allocation_key !== exceptKey,
        )
        .reduce((t, l) => t + Number(l.quantity || 0), 0);
}

function maxQuantityFor(allocation: {
    item_id: number;
    allocation_key: string;
}) {
    const receiptLine = receiptLineFor(allocation.item_id);
    return Math.max(
        0,
        Number(receiptLine?.quantity_remaining ?? 0) -
            allocatedQuantity(allocation.item_id, allocation.allocation_key),
    );
}

function canAddAllocation(itemId: number) {
    const receiptLine = receiptLineFor(itemId);
    return receiptLine
        ? Number(receiptLine.quantity_remaining) - allocatedQuantity(itemId) > 0
        : false;
}

function addAllocation(itemId: number) {
    const receiptLine = receiptLineFor(itemId);
    const remaining =
        Number(receiptLine?.quantity_remaining ?? 0) -
        allocatedQuantity(itemId);
    if (!receiptLine || remaining <= 0) return;
    form.lines.push({
        allocation_key: `${itemId}-${Date.now()}-${form.lines.length}`,
        item_id: itemId,
        unit_id: receiptLine.unit_id,
        to_location_id: props.storageLocations[0]?.id ?? '',
        quantity: remaining,
        selected: true,
    });
}

function removeAllocation(allocationKey: string) {
    const allocation = form.lines.find(
        (l) => l.allocation_key === allocationKey,
    );
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

function submit() {
    if (props.putaway) {
        form.patch(`/putaway/${props.putaway.id}`, { preserveScroll: true });
        return;
    }
    form.post('/putaway', { preserveScroll: true });
}

function cancel() {
    if (props.putaway) {
        router.visit('/putaway');
    } else {
        router.visit('/putaway/completed-goods-receipts');
    }
}
</script>

<template>
    <Head title="Create Putaway" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #actions>
            <div class="flex gap-2">
                <Button
                    type="button"
                    variant="ghost"
                    class="h-9 font-semibold text-slate-600 hover:text-red-500"
                    :disabled="form.processing"
                    @click="cancel"
                >
                    Cancel
                </Button>
                <Button
                    type="button"
                    class="h-9 rounded-lg bg-[#007882] px-4 text-xs font-bold text-white shadow-md hover:bg-[#006873]"
                    :disabled="!canSubmit"
                    @click="submit"
                >
                    {{
                        form.processing
                            ? 'Saving...'
                            : putaway
                              ? 'Update Draft'
                              : 'Save Draft'
                    }}
                </Button>
            </div>
        </template>

        <main
            class="h-[calc(100dvh-4rem)] w-full scrollbar-gutter-stable overflow-y-scroll bg-[#f8fafc] p-4 text-slate-800 md:h-[calc(100dvh-5rem)] md:p-6 xl:p-8 2xl:p-10"
        >
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
                <!-- Items table (8 cols) -->
                <section class="xl:col-span-8">
                    <div
                        class="overflow-hidden rounded-lg border border-slate-100 bg-white shadow-sm"
                    >
                        <div
                            class="flex items-center justify-between border-b border-slate-100 bg-slate-50 p-4"
                        >
                            <h2
                                class="text-xs font-bold tracking-wider text-slate-700 uppercase"
                            >
                                Inventory to Allocate
                            </h2>
                            <div
                                class="flex items-center rounded-full border border-teal-100 bg-teal-50 px-3 py-1 text-xs font-bold text-[#007882]"
                            >
                                <Info class="mr-1.5 h-3.5 w-3.5" />
                                Items loaded from selected GR
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-200 text-left text-sm">
                                <thead
                                    class="border-b border-slate-100 text-slate-500"
                                >
                                    <tr>
                                        <th
                                            class="w-10 px-4 py-3 font-semibold"
                                        ></th>
                                        <th
                                            class="px-4 py-3 text-left font-semibold"
                                        >
                                            Product Details
                                        </th>
                                        <th
                                            class="px-4 py-3 text-center font-semibold"
                                        >
                                            Remaining
                                        </th>
                                        <th
                                            class="px-4 py-3 text-center font-semibold text-[#007882]"
                                        >
                                            Qty to Move
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left font-semibold text-[#007882]"
                                        >
                                            Destination Bin
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    <tr
                                        v-for="allocation in form.lines"
                                        :key="allocation.allocation_key"
                                        class="transition hover:bg-slate-50/30"
                                    >
                                        <td class="px-4 py-4 text-center">
                                            <input
                                                type="checkbox"
                                                :checked="allocation.selected"
                                                class="h-4 w-4 rounded border-slate-300 text-[#007882] focus:ring-[#007882]"
                                                @change="
                                                    setSelected(
                                                        allocation.allocation_key,
                                                        $event,
                                                    )
                                                "
                                            />
                                        </td>
                                        <td class="px-4 py-4">
                                            <div
                                                class="font-bold text-slate-800"
                                            >
                                                {{
                                                    receiptLineFor(
                                                        allocation.item_id,
                                                    )?.item_name ??
                                                    'Inventory item'
                                                }}
                                            </div>
                                            <div
                                                class="mt-0.5 font-mono text-xs text-slate-400 uppercase"
                                            >
                                                SKU:
                                                {{
                                                    receiptLineFor(
                                                        allocation.item_id,
                                                    )?.item_code ?? '-'
                                                }}
                                                <span class="text-[#007882]"
                                                    >/
                                                    {{
                                                        receiptLineFor(
                                                            allocation.item_id,
                                                        )?.unit_code ?? 'UNIT'
                                                    }}</span
                                                >
                                            </div>
                                        </td>
                                        <td
                                            class="px-4 py-4 text-center font-bold text-slate-400 italic"
                                        >
                                            {{
                                                receiptLineFor(
                                                    allocation.item_id,
                                                )?.quantity_remaining ?? 0
                                            }}
                                            units
                                            <div
                                                v-if="
                                                    allocationsFor(
                                                        allocation.item_id,
                                                    ).length > 1
                                                "
                                                class="mt-0.5 text-xs font-normal text-slate-400"
                                            >
                                                Split allocation
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <input
                                                type="number"
                                                min="0"
                                                :max="
                                                    maxQuantityFor(allocation)
                                                "
                                                :value="allocation.quantity"
                                                :disabled="!allocation.selected"
                                                class="w-24 rounded-lg border-2 border-[#007882] py-1 text-center font-bold text-[#007882]"
                                                :class="{
                                                    'border-slate-200 bg-slate-50 text-slate-400':
                                                        !allocation.selected,
                                                }"
                                                @input="
                                                    setQuantity(
                                                        allocation.allocation_key,
                                                        $event,
                                                    )
                                                "
                                            />
                                        </td>
                                        <td class="px-4 py-4">
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <select
                                                    :value="
                                                        allocation.to_location_id
                                                    "
                                                    :disabled="
                                                        !allocation.selected
                                                    "
                                                    class="w-full rounded-lg border border-slate-200 bg-slate-50 px-2 py-2 text-xs font-bold text-slate-600 outline-none focus:border-[#007882] focus:ring-2 focus:ring-[#007882]/20 disabled:text-slate-400"
                                                    @change="
                                                        setDestination(
                                                            allocation.allocation_key,
                                                            $event,
                                                        )
                                                    "
                                                >
                                                    <option value="" disabled>
                                                        Select bin
                                                    </option>
                                                    <option
                                                        v-for="location in storageLocations"
                                                        :key="location.id"
                                                        :value="location.id"
                                                    >
                                                        {{
                                                            location.code ??
                                                            location.name
                                                        }}
                                                        -
                                                        {{
                                                            location.warehouse_name
                                                        }}
                                                    </option>
                                                </select>
                                                <button
                                                    v-if="
                                                        canAddAllocation(
                                                            allocation.item_id,
                                                        )
                                                    "
                                                    type="button"
                                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-teal-100 bg-teal-50 text-[#007882] hover:bg-teal-100"
                                                    title="Add another destination"
                                                    @click="
                                                        addAllocation(
                                                            allocation.item_id,
                                                        )
                                                    "
                                                >
                                                    <Plus class="h-4 w-4" />
                                                </button>
                                                <button
                                                    v-if="
                                                        allocationsFor(
                                                            allocation.item_id,
                                                        ).length > 1
                                                    "
                                                    type="button"
                                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-rose-100 bg-rose-50 text-rose-600 hover:bg-rose-100"
                                                    title="Remove split row"
                                                    @click="
                                                        removeAllocation(
                                                            allocation.allocation_key,
                                                        )
                                                    "
                                                >
                                                    <Trash2 class="h-4 w-4" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr
                                        v-if="
                                            !selectedReceipt ||
                                            form.lines.length === 0
                                        "
                                    >
                                        <td
                                            colspan="5"
                                            class="px-6 py-16 text-center text-sm text-slate-400"
                                        >
                                            Select a completed goods receipt to
                                            load inventory for putaway.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <!-- Sidebar (4 cols) -->
                <aside class="space-y-6 xl:col-span-4">
                    <div
                        class="rounded-lg border border-slate-100 bg-white p-6 shadow-sm"
                    >
                        <h2
                            class="mb-5 text-xs font-bold tracking-wider text-slate-700 uppercase"
                        >
                            Task Configuration
                        </h2>
                        <div class="space-y-5">
                            <!-- GR selector -->
                            <div>
                                <label
                                    class="mb-1.5 block text-xs font-bold text-slate-500 uppercase"
                                >
                                    Select GR Number
                                </label>
                                <div class="relative">
                                    <FileText
                                        class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400"
                                    />
                                    <select
                                        v-model="form.goods_receipt_id"
                                        class="h-10 w-full appearance-none rounded-lg border border-slate-200 bg-white pr-8 pl-9 text-sm font-bold text-[#007882] outline-none focus:border-[#007882] focus:ring-2 focus:ring-[#007882]/20"
                                    >
                                        <option value="" disabled>
                                            Select completed GR
                                        </option>
                                        <option
                                            v-for="r in receipts"
                                            :key="r.id"
                                            :value="r.id"
                                        >
                                            {{ r.receipt_no }} ({{
                                                r.total_remaining
                                            }}
                                            remaining)
                                        </option>
                                    </select>
                                    <ChevronDown
                                        class="pointer-events-none absolute top-1/2 right-3 h-3.5 w-3.5 -translate-y-1/2 text-slate-300"
                                    />
                                </div>
                            </div>

                            <!-- Staff -->
                            <div>
                                <label
                                    class="mb-1.5 block text-xs font-bold text-slate-500 uppercase"
                                >
                                    Assign Putaway Staff
                                </label>
                                <div class="relative">
                                    <UserRound
                                        class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400"
                                    />
                                    <select
                                        v-model="form.assigned_to"
                                        class="h-10 w-full appearance-none rounded-lg border border-slate-200 bg-white pr-8 pl-9 text-sm outline-none focus:border-[#007882] focus:ring-2 focus:ring-[#007882]/20"
                                    >
                                        <option value="">
                                            Select Warehouse Operator...
                                        </option>
                                        <option
                                            v-for="user in staff"
                                            :key="user.id"
                                            :value="user.id"
                                        >
                                            {{ user.name }}
                                        </option>
                                    </select>
                                    <ChevronDown
                                        class="pointer-events-none absolute top-1/2 right-3 h-3.5 w-3.5 -translate-y-1/2 text-slate-300"
                                    />
                                </div>
                            </div>

                            <!-- Priority -->
                            <div>
                                <label
                                    class="mb-2 block text-xs font-bold text-slate-500 uppercase"
                                    >Task Priority</label
                                >
                                <div class="flex gap-2">
                                    <button
                                        v-for="p in ['low', 'normal', 'urgent']"
                                        :key="p"
                                        type="button"
                                        class="flex-1 rounded-lg border py-2 text-xs font-bold uppercase transition"
                                        :class="
                                            form.priority === p
                                                ? 'border-[#007882] bg-[#007882] text-white shadow-sm'
                                                : 'border-slate-100 text-slate-600 hover:border-slate-200'
                                        "
                                        @click="form.priority = p"
                                    >
                                        {{ p }}
                                    </button>
                                </div>
                            </div>

                            <!-- Note -->
                            <div>
                                <label
                                    class="mb-1.5 block text-xs font-bold text-slate-500 uppercase"
                                >
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

                    <!-- Movement Summary -->
                    <div
                        class="overflow-hidden rounded-lg bg-[#2a4858] p-6 text-white shadow-xl"
                    >
                        <h2
                            class="mb-5 text-xs font-bold tracking-wider uppercase opacity-60"
                        >
                            Movement Summary
                        </h2>
                        <div class="space-y-3 text-sm">
                            <div
                                class="flex items-center justify-between border-b border-white/10 pb-3"
                            >
                                <span class="opacity-70">Putaway No:</span>
                                <span class="font-bold">{{
                                    putaway?.transfer_no ?? nextTransferNo
                                }}</span>
                            </div>
                            <div
                                class="flex items-center justify-between border-b border-white/10 pb-3"
                            >
                                <span class="opacity-70">Selected GR:</span>
                                <span class="font-bold">{{
                                    selectedReceipt?.receipt_no ?? '-'
                                }}</span>
                            </div>
                            <div
                                class="flex items-center justify-between border-b border-white/10 pb-3"
                            >
                                <span class="opacity-70">Staff Assigned:</span>
                                <span class="font-bold">{{
                                    assignedStaffName
                                }}</span>
                            </div>
                            <div
                                class="flex items-center justify-between border-b border-white/10 pb-3"
                            >
                                <span class="opacity-70">Total Units:</span>
                                <span
                                    class="text-lg font-bold text-[#fafa6e]"
                                    >{{ totalQuantity }}</span
                                >
                            </div>
                            <div class="flex items-center justify-between pb-1">
                                <span class="opacity-70">Priority:</span>
                                <span class="font-bold capitalize">{{
                                    form.priority
                                }}</span>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </main>
    </AppLayout>
</template>
