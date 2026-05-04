<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CheckCircle,
    ChevronDown,
    FileText,
    Info,
    Plus,
    Trash2,
    UserRound,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';

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

const selectedReceipt = computed(() => {
    return (
        props.receipts.find(
            (receipt) => receipt.id === Number(form.goods_receipt_id),
        ) ??
        props.receipt ??
        null
    );
});

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
    form.lines.filter((line) => line.selected && Number(line.quantity) > 0),
);
const totalQuantity = computed(() =>
    selectedLines.value.reduce(
        (total, line) => total + Number(line.quantity || 0),
        0,
    ),
);
const assignedStaffName = computed(
    () =>
        props.staff.find((user) => user.id === Number(form.assigned_to))
            ?.name ?? 'Unassigned',
);

const canSubmit = computed(() => {
    return (
        Boolean(form.goods_receipt_id) &&
        selectedLines.value.length > 0 &&
        selectedLines.value.every((line) => line.to_location_id) &&
        !form.processing
    );
});

function formLine(itemId: number) {
    return form.lines.find((line) => line.item_id === itemId);
}

function receiptLineFor(itemId: number) {
    return selectedReceipt.value?.lines.find((line) => line.item_id === itemId);
}

function allocationsFor(itemId: number) {
    return form.lines.filter((line) => line.item_id === itemId);
}

function allocatedQuantity(itemId: number, exceptKey?: string) {
    return form.lines
        .filter(
            (line) =>
                line.item_id === itemId &&
                line.selected &&
                line.allocation_key !== exceptKey,
        )
        .reduce((total, line) => total + Number(line.quantity || 0), 0);
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

    if (!receiptLine) {
        return false;
    }

    return (
        Number(receiptLine.quantity_remaining) - allocatedQuantity(itemId) > 0
    );
}

function addAllocation(itemId: number) {
    const receiptLine = receiptLineFor(itemId);
    const remaining =
        Number(receiptLine?.quantity_remaining ?? 0) -
        allocatedQuantity(itemId);

    if (!receiptLine || remaining <= 0) {
        return;
    }

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
        (line) => line.allocation_key === allocationKey,
    );

    if (!allocation || allocationsFor(allocation.item_id).length <= 1) {
        return;
    }

    form.lines = form.lines.filter(
        (line) => line.allocation_key !== allocationKey,
    );
}

function setSelected(allocationKey: string, event: Event) {
    const line = form.lines.find(
        (line) => line.allocation_key === allocationKey,
    );

    if (line) {
        line.selected = (event.target as HTMLInputElement).checked;
    }
}

function setQuantity(allocationKey: string, event: Event) {
    const line = form.lines.find(
        (line) => line.allocation_key === allocationKey,
    );

    if (line) {
        const value = Number((event.target as HTMLInputElement).value || 0);
        line.quantity = Math.min(Math.max(0, value), maxQuantityFor(line));
    }
}

function setDestination(allocationKey: string, event: Event) {
    const line = form.lines.find(
        (line) => line.allocation_key === allocationKey,
    );

    if (line) {
        const value = (event.target as HTMLSelectElement).value;
        line.to_location_id = value ? Number(value) : '';
    }
}

function submit() {
    if (props.putaway) {
        form.patch(`/putaway/${props.putaway.id}`, { preserveScroll: true });
        return;
    }

    form.post('/putaway', { preserveScroll: true });
}
</script>

<template>
    <Head title="Create Putaway" />

    <AppLayout>
        <form
            class="w-full bg-slate-100 p-4 text-slate-800 md:p-8"
            @submit.prevent="submit"
        >
            <header
                class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-4">
                    <Link
                        href="/putaway"
                        class="flex h-10 w-10 items-center justify-center rounded-full border bg-white text-slate-400 transition hover:text-[#007882]"
                    >
                        <ArrowLeft class="h-5 w-5" />
                    </Link>
                    <div>
                        <h1
                            class="text-2xl leading-tight font-black text-[#2a4858]"
                        >
                            {{
                                putaway
                                    ? 'Edit Putaway Movement'
                                    : 'Create Putaway Movement'
                            }}
                        </h1>
                        <p class="text-sm font-medium text-slate-500">
                            New stock movement assignment
                        </p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <Link
                        href="/putaway"
                        class="px-6 py-2.5 font-bold text-slate-500 transition hover:text-slate-800"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="!canSubmit"
                        class="rounded-lg bg-[#007882] px-8 py-2.5 font-black text-white shadow-lg transition hover:brightness-110"
                        :class="{ 'cursor-not-allowed opacity-60': !canSubmit }"
                    >
                        {{
                            form.processing
                                ? 'Saving...'
                                : putaway
                                  ? 'Update Draft'
                                  : 'Save Draft'
                        }}
                    </button>
                </div>
            </header>

            <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
                <section class="xl:col-span-8">
                    <div
                        class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
                    >
                        <div
                            class="flex flex-col gap-3 border-b bg-slate-50 p-4 md:flex-row md:items-center md:justify-between"
                        >
                            <h2
                                class="text-xs font-black tracking-widest text-slate-500 uppercase"
                            >
                                Inventory to Allocate
                            </h2>
                            <div
                                class="flex items-center rounded-full border border-teal-100 bg-teal-50 px-3 py-1 text-[10px] font-bold text-[#007882]"
                            >
                                <Info class="mr-1.5 h-3.5 w-3.5" />
                                Items loaded from selected GR
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table
                                class="w-full min-w-[900px] text-left text-sm"
                            >
                                <thead
                                    class="border-b bg-slate-50/50 text-[10px] font-black tracking-widest text-slate-400 uppercase"
                                >
                                    <tr>
                                        <th class="w-10 px-6 py-4"></th>
                                        <th class="px-6 py-4">
                                            Product Details
                                        </th>
                                        <th class="px-6 py-4 text-center">
                                            Remaining
                                        </th>
                                        <th class="px-6 py-4 text-center">
                                            Quantity to Move
                                        </th>
                                        <th class="px-6 py-4">
                                            Destination Bin
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr
                                        v-for="allocation in form.lines"
                                        :key="allocation.allocation_key"
                                        class="transition hover:bg-slate-50/30"
                                    >
                                        <td class="px-6 py-4">
                                            <input
                                                type="checkbox"
                                                :checked="allocation.selected"
                                                class="rounded border-slate-300 text-[#007882]"
                                                @change="
                                                    setSelected(
                                                        allocation.allocation_key,
                                                        $event,
                                                    )
                                                "
                                            />
                                        </td>
                                        <td class="px-6 py-4">
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
                                                class="text-[10px] font-bold text-slate-400 uppercase"
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
                                            class="px-6 py-4 text-center font-bold text-slate-400 italic"
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
                                                class="text-[10px] font-medium text-slate-400"
                                            >
                                                Split allocation
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <input
                                                type="number"
                                                min="0"
                                                :max="
                                                    maxQuantityFor(allocation)
                                                "
                                                :value="allocation.quantity"
                                                class="w-24 rounded-lg border border-slate-200 px-3 py-1.5 text-center font-black text-[#007882]"
                                                @input="
                                                    setQuantity(
                                                        allocation.allocation_key,
                                                        $event,
                                                    )
                                                "
                                            />
                                        </td>
                                        <td class="px-6 py-4">
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <select
                                                    :value="
                                                        allocation.to_location_id
                                                    "
                                                    class="w-full rounded-lg border border-slate-200 bg-slate-50 p-2 text-xs font-bold text-slate-600"
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
                                            class="px-6 py-12 text-center text-sm text-slate-500"
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

                <aside class="space-y-6 xl:col-span-4">
                    <div
                        class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm"
                    >
                        <h2
                            class="mb-5 text-xs font-black tracking-widest text-slate-400 uppercase"
                        >
                            Task Configuration
                        </h2>

                        <div class="space-y-5">
                            <div>
                                <label
                                    class="mb-1.5 block text-[10px] font-black tracking-wider text-slate-500 uppercase"
                                >
                                    Select GR Number
                                </label>
                                <div class="relative">
                                    <FileText
                                        class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400"
                                    />
                                    <select
                                        v-model="form.goods_receipt_id"
                                        class="w-full appearance-none rounded-lg border border-slate-200 bg-white py-2.5 pr-4 pl-9 text-sm font-bold text-[#007882]"
                                    >
                                        <option value="" disabled>
                                            Select completed GR
                                        </option>
                                        <option
                                            v-for="receiptOption in receipts"
                                            :key="receiptOption.id"
                                            :value="receiptOption.id"
                                        >
                                            {{ receiptOption.receipt_no }} ({{
                                                receiptOption.total_remaining
                                            }}
                                            remaining)
                                        </option>
                                    </select>
                                    <ChevronDown
                                        class="absolute top-1/2 right-3 h-3.5 w-3.5 -translate-y-1/2 text-slate-300"
                                    />
                                </div>
                            </div>

                            <div>
                                <label
                                    class="mb-1.5 block text-[10px] font-black tracking-wider text-slate-500 uppercase"
                                >
                                    Assign Putaway Staff
                                </label>
                                <div class="relative">
                                    <UserRound
                                        class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400"
                                    />
                                    <select
                                        v-model="form.assigned_to"
                                        class="w-full appearance-none rounded-lg border border-slate-200 bg-white py-2.5 pr-4 pl-9 text-sm font-medium"
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
                                        class="absolute top-1/2 right-3 h-3.5 w-3.5 -translate-y-1/2 text-slate-300"
                                    />
                                </div>
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-[10px] font-black tracking-wider text-slate-500 uppercase"
                                    >Task Priority</label
                                >
                                <div class="flex gap-2">
                                    <button
                                        v-for="priority in [
                                            'low',
                                            'normal',
                                            'urgent',
                                        ]"
                                        :key="priority"
                                        type="button"
                                        class="flex-1 rounded-lg border py-2 text-[10px] font-black uppercase transition"
                                        :class="
                                            form.priority === priority
                                                ? 'border-[#007882] bg-[#007882] text-white shadow-sm'
                                                : 'border-slate-100 hover:border-slate-200'
                                        "
                                        @click="form.priority = priority"
                                    >
                                        {{ priority }}
                                    </button>
                                </div>
                            </div>

                            <div>
                                <label
                                    class="mb-1.5 block text-[10px] font-black tracking-wider text-slate-500 uppercase"
                                >
                                    Instruction / Note
                                </label>
                                <textarea
                                    v-model="form.note"
                                    rows="3"
                                    placeholder="Handling instructions..."
                                    class="w-full resize-none rounded-lg border border-slate-200 bg-slate-50/50 p-3 text-sm"
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <div
                        class="relative overflow-hidden rounded-xl bg-[#2a4858] p-6 text-white shadow-xl"
                    >
                        <div class="relative z-10">
                            <h2
                                class="mb-5 text-[10px] font-black tracking-widest uppercase opacity-60"
                            >
                                Movement Summary
                            </h2>
                            <div class="mb-8 space-y-4">
                                <div
                                    class="flex items-center justify-between border-b border-white/10 pb-3"
                                >
                                    <span class="text-xs font-medium opacity-80"
                                        >Putaway No:</span
                                    >
                                    <span class="text-sm font-black">{{
                                        putaway?.transfer_no ?? nextTransferNo
                                    }}</span>
                                </div>
                                <div
                                    class="flex items-center justify-between border-b border-white/10 pb-3"
                                >
                                    <span class="text-xs font-medium opacity-80"
                                        >Selected GR:</span
                                    >
                                    <span class="text-sm font-black">{{
                                        selectedReceipt?.receipt_no ?? '-'
                                    }}</span>
                                </div>
                                <div
                                    class="flex items-center justify-between border-b border-white/10 pb-3"
                                >
                                    <span class="text-xs font-medium opacity-80"
                                        >Staff Assigned:</span
                                    >
                                    <span class="text-sm font-black">{{
                                        assignedStaffName
                                    }}</span>
                                </div>
                                <div
                                    class="flex items-center justify-between border-b border-white/10 pb-3"
                                >
                                    <span class="text-xs font-medium opacity-80"
                                        >Total Units:</span
                                    >
                                    <span class="text-sm font-black">{{
                                        totalQuantity
                                    }}</span>
                                </div>
                            </div>
                            <button
                                type="submit"
                                :disabled="!canSubmit"
                                class="flex w-full items-center justify-center rounded-lg bg-[#23aa8f] py-3.5 text-sm font-black shadow-2xl transition hover:brightness-110"
                                :class="{
                                    'cursor-not-allowed opacity-60': !canSubmit,
                                }"
                            >
                                <CheckCircle class="mr-2 h-4 w-4" />
                                AUTHORIZE MOVEMENT
                            </button>
                        </div>
                    </div>
                </aside>
            </div>
        </form>
    </AppLayout>
</template>
