<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, PackageOpen, Plus, Trash2 } from 'lucide-vue-next';
import { computed, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';

type PurchaseOrderLine = {
    id: number;
    item_name?: string | null;
    item_code?: string | null;
    unit_code?: string | null;
    quantity_ordered: number;
    quantity_received: number;
    quantity_remaining: number;
    unit_cost: number;
};

type PurchaseOrder = {
    id: number;
    po_no: string;
    supplier_name?: string | null;
    receipt_status: string;
    lines: PurchaseOrderLine[];
};

type StagingLocation = {
    id: number;
    name: string;
    code?: string | null;
    location_type: string;
    warehouse_name?: string | null;
};

const props = defineProps<{
    nextReceiptNo: string;
    purchaseOrder?: PurchaseOrder | null;
    purchaseOrders: PurchaseOrder[];
    stagingLocations: StagingLocation[];
}>();

const form = useForm({
    purchase_order_id: props.purchaseOrder?.id ?? '',
    note: '',
    lines: [] as {
        allocation_key: string;
        purchase_order_line_id: number;
        stock_location_id: number | '';
        quantity_received: number;
        selected: boolean;
    }[],
});

const selectedPurchaseOrder = computed(() => {
    return (
        props.purchaseOrders.find(
            (order) => order.id === Number(form.purchase_order_id),
        ) ??
        props.purchaseOrder ??
        null
    );
});

const receiptLines = computed(() => selectedPurchaseOrder.value?.lines ?? []);

watch(
    selectedPurchaseOrder,
    (order) => {
        form.lines =
            order?.lines.map((line) => ({
                allocation_key: `${line.id}-${Date.now()}`,
                purchase_order_line_id: line.id,
                stock_location_id: props.stagingLocations[0]?.id ?? '',
                quantity_received: Number(line.quantity_remaining || 0),
                selected: Number(line.quantity_remaining || 0) > 0,
            })) ?? [];
    },
    { immediate: true },
);

function poLineFor(lineId: number) {
    return receiptLines.value.find((line) => line.id === lineId);
}

function allocationsFor(lineId: number) {
    return form.lines.filter((line) => line.purchase_order_line_id === lineId);
}

function allocatedQuantity(lineId: number, exceptKey?: string) {
    return form.lines
        .filter(
            (line) =>
                line.purchase_order_line_id === lineId &&
                line.allocation_key !== exceptKey,
        )
        .reduce(
            (total, line) => total + Number(line.quantity_received || 0),
            0,
        );
}

function maxQuantityFor(allocation: {
    purchase_order_line_id: number;
    allocation_key: string;
}) {
    return Math.max(
        0,
        Number(
            poLineFor(allocation.purchase_order_line_id)?.quantity_remaining ??
                0,
        ) -
            allocatedQuantity(
                allocation.purchase_order_line_id,
                allocation.allocation_key,
            ),
    );
}

function canAddAllocation(lineId: number) {
    return (
        Number(poLineFor(lineId)?.quantity_remaining ?? 0) -
            allocatedQuantity(lineId) >
        0
    );
}

function addAllocation(lineId: number) {
    const poLine = poLineFor(lineId);
    const remaining =
        Number(poLine?.quantity_remaining ?? 0) - allocatedQuantity(lineId);

    if (!poLine || remaining <= 0) {
        return;
    }

    form.lines.push({
        allocation_key: `${lineId}-${Date.now()}-${form.lines.length}`,
        purchase_order_line_id: lineId,
        stock_location_id: props.stagingLocations[0]?.id ?? '',
        quantity_received: remaining,
        selected: true,
    });
}

function removeAllocation(allocationKey: string) {
    const allocation = form.lines.find(
        (line) => line.allocation_key === allocationKey,
    );

    if (
        !allocation ||
        allocationsFor(allocation.purchase_order_line_id).length <= 1
    ) {
        return;
    }

    form.lines = form.lines.filter(
        (line) => line.allocation_key !== allocationKey,
    );
}

function updateQuantity(allocationKey: string, value: string) {
    const line = form.lines.find(
        (line) => line.allocation_key === allocationKey,
    );

    if (line) {
        const nextValue = Number(value || 0);
        line.quantity_received = Math.min(
            Math.max(0, nextValue),
            maxQuantityFor(line),
        );
    }
}

function updateSelected(allocationKey: string, event: Event) {
    const line = form.lines.find(
        (line) => line.allocation_key === allocationKey,
    );

    if (!line) {
        return;
    }

    line.selected = (event.target as HTMLInputElement).checked;

    if (!line.selected) {
        line.quantity_received = 0;
    } else if (line.quantity_received <= 0) {
        line.quantity_received = maxQuantityFor(line);
    }
}

function updateQuantityFromEvent(allocationKey: string, event: Event) {
    updateQuantity(allocationKey, (event.target as HTMLInputElement).value);
}

function updateStagingZone(allocationKey: string, event: Event) {
    const line = form.lines.find(
        (line) => line.allocation_key === allocationKey,
    );

    if (line) {
        const value = (event.target as HTMLSelectElement).value;
        line.stock_location_id = value ? Number(value) : '';
    }
}

function submit() {
    form.post('/goods-receipts', {
        preserveScroll: true,
    });
}

function fieldError(name: string) {
    return (form.errors as Record<string, string | undefined>)[name];
}

const canSubmit = computed(() => {
    return (
        Boolean(form.purchase_order_id) &&
        form.lines.some(
            (line) => line.selected && Number(line.quantity_received) > 0,
        ) &&
        form.lines
            .filter(
                (line) => line.selected && Number(line.quantity_received) > 0,
            )
            .every((line) => line.stock_location_id) &&
        !form.processing
    );
});
</script>

<template>
    <Head title="Create Goods Receipt" />

    <AppLayout>
        <form
            class="w-full bg-slate-100 p-4 text-slate-800 md:p-8"
            @submit.prevent="submit"
        >
            <div
                class="mb-8 flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-4">
                    <Link
                        href="/goods-receipts/approved-purchase-orders"
                        class="flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-400 transition hover:text-[#007882]"
                    >
                        <ArrowLeft class="h-5 w-5" />
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-[#2a4858]">
                            New Goods Receipt
                        </h1>
                        <p class="text-slate-500">
                            {{
                                selectedPurchaseOrder
                                    ? `Inbound staging area entry for ${selectedPurchaseOrder.po_no}`
                                    : 'Create a receipt from an approved purchase order'
                            }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <Link
                        href="/goods-receipts"
                        class="px-6 py-2 font-bold text-slate-500"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="!canSubmit"
                        class="rounded-lg bg-[#23aa8f] px-8 py-2 font-bold text-white shadow-md hover:opacity-90"
                        :class="{
                            'cursor-not-allowed opacity-60': !canSubmit,
                        }"
                    >
                        {{ form.processing ? 'Saving...' : 'Save GR' }}
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 xl:grid-cols-4">
                <aside class="space-y-6 xl:col-span-1">
                    <div
                        class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm"
                    >
                        <h2
                            class="mb-4 text-xs font-black tracking-widest text-slate-400 uppercase"
                        >
                            Source PO Reference
                        </h2>
                        <div class="space-y-3">
                            <div>
                                <label
                                    class="mb-1 block text-xs font-bold text-slate-500"
                                >
                                    Source Purchase Order
                                </label>
                                <select
                                    v-model="form.purchase_order_id"
                                    class="w-full rounded-lg border border-slate-200 bg-slate-50 p-2 text-sm"
                                >
                                    <option value="" disabled>
                                        Select approved PO
                                    </option>
                                    <option
                                        v-for="order in purchaseOrders"
                                        :key="order.id"
                                        :value="order.id"
                                    >
                                        {{ order.po_no }} -
                                        {{ order.supplier_name ?? 'Supplier' }}
                                    </option>
                                </select>
                                <p
                                    v-if="fieldError('purchase_order_id')"
                                    class="mt-1 text-xs font-medium text-rose-600"
                                >
                                    {{ fieldError('purchase_order_id') }}
                                </p>
                            </div>

                            <div
                                v-if="selectedPurchaseOrder"
                                class="rounded-lg border border-[#007882]/10 bg-[#007882]/5 p-3"
                            >
                                <p class="text-xs font-bold text-[#007882]">
                                    PO Info
                                </p>
                                <p
                                    class="mt-1 text-sm font-bold text-slate-700"
                                >
                                    {{
                                        selectedPurchaseOrder.supplier_name ??
                                        'Supplier'
                                    }}
                                </p>
                                <p class="text-xs text-slate-500">
                                    {{ selectedPurchaseOrder.po_no }}
                                </p>
                            </div>
                        </div>
                        <div
                            v-if="purchaseOrders.length === 0"
                            class="mt-3 text-sm text-slate-500"
                        >
                            Choose an approved PO before saving a receipt.
                        </div>
                    </div>

                    <div
                        class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm"
                    >
                        <h2
                            class="mb-4 text-xs font-black tracking-widest text-slate-400 uppercase"
                        >
                            Receipt Details
                        </h2>
                        <div class="space-y-4">
                            <div>
                                <label
                                    class="mb-1 block text-xs font-bold text-slate-500"
                                >
                                    Receipt Number
                                </label>
                                <input
                                    :value="nextReceiptNo"
                                    readonly
                                    class="w-full rounded-lg border border-slate-200 bg-slate-50 p-2 text-sm font-bold text-slate-600"
                                />
                            </div>
                            <div>
                                <label
                                    class="mb-1 block text-xs font-bold text-slate-500"
                                >
                                    Note
                                </label>
                                <textarea
                                    v-model="form.note"
                                    rows="3"
                                    class="w-full rounded-lg border border-slate-200 bg-slate-50 p-2 text-sm"
                                    placeholder="Optional receipt note"
                                ></textarea>
                            </div>
                        </div>
                    </div>
                </aside>

                <section class="space-y-6 xl:col-span-3">
                    <div
                        class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
                    >
                        <div
                            class="flex items-center justify-between border-b bg-slate-50 p-4"
                        >
                            <h2
                                class="text-xs font-black tracking-widest text-slate-500 uppercase"
                            >
                                Verify Inbound Items
                            </h2>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[980px] text-sm">
                                <thead>
                                    <tr
                                        class="border-b border-slate-100 text-slate-400"
                                    >
                                        <th
                                            class="w-12 px-4 py-3 text-center text-[10px] font-bold uppercase"
                                        >
                                            Use
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-[10px] font-bold uppercase"
                                        >
                                            Product / SKU
                                        </th>
                                        <th
                                            class="px-6 py-3 text-center text-[10px] font-bold uppercase"
                                        >
                                            Ordered
                                        </th>
                                        <th
                                            class="px-6 py-3 text-center text-[10px] font-bold uppercase"
                                        >
                                            Prev. Received
                                        </th>
                                        <th
                                            class="px-6 py-3 text-center text-[10px] font-bold text-[#007882] uppercase"
                                        >
                                            Received Today
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-[10px] font-bold text-[#007882] uppercase"
                                        >
                                            Staging Zone
                                        </th>
                                        <th
                                            class="px-4 py-3 text-center text-[10px] font-bold uppercase"
                                        >
                                            Split
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    <tr
                                        v-for="allocation in form.lines"
                                        :key="allocation.allocation_key"
                                    >
                                        <td class="px-4 py-4 text-center">
                                            <input
                                                type="checkbox"
                                                :checked="allocation.selected"
                                                class="h-4 w-4 rounded border-slate-300 text-[#007882] focus:ring-[#007882]"
                                                @change="
                                                    updateSelected(
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
                                                    poLineFor(
                                                        allocation.purchase_order_line_id,
                                                    )?.item_name ??
                                                    'Inventory item'
                                                }}
                                            </div>
                                            <div
                                                class="font-mono text-[10px] text-slate-400"
                                            >
                                                SKU:
                                                {{
                                                    poLineFor(
                                                        allocation.purchase_order_line_id,
                                                    )?.item_code ?? '-'
                                                }}
                                                <span
                                                    v-if="
                                                        poLineFor(
                                                            allocation.purchase_order_line_id,
                                                        )?.unit_code
                                                    "
                                                >
                                                    /
                                                    {{
                                                        poLineFor(
                                                            allocation.purchase_order_line_id,
                                                        )?.unit_code
                                                    }}
                                                </span>
                                            </div>
                                        </td>
                                        <td
                                            class="px-6 py-4 text-center font-bold text-slate-500"
                                        >
                                            {{
                                                poLineFor(
                                                    allocation.purchase_order_line_id,
                                                )?.quantity_ordered ?? 0
                                            }}
                                        </td>
                                        <td
                                            class="px-6 py-4 text-center font-bold text-slate-400"
                                        >
                                            {{
                                                poLineFor(
                                                    allocation.purchase_order_line_id,
                                                )?.quantity_received ?? 0
                                            }}
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <input
                                                type="number"
                                                min="0"
                                                :max="
                                                    maxQuantityFor(allocation)
                                                "
                                                :value="
                                                    allocation.quantity_received
                                                "
                                                :disabled="!allocation.selected"
                                                class="w-24 rounded-lg border-2 border-[#007882] py-1 text-center font-bold text-[#007882]"
                                                :class="{
                                                    'border-slate-200 bg-slate-50 text-slate-400':
                                                        !allocation.selected,
                                                }"
                                                @input="
                                                    updateQuantityFromEvent(
                                                        allocation.allocation_key,
                                                        $event,
                                                    )
                                                "
                                            />
                                        </td>
                                        <td class="px-6 py-4">
                                            <select
                                                :value="
                                                    allocation.stock_location_id
                                                "
                                                :disabled="!allocation.selected"
                                                class="w-full min-w-48 rounded-lg border border-slate-200 bg-slate-50 p-2 text-sm"
                                                @change="
                                                    updateStagingZone(
                                                        allocation.allocation_key,
                                                        $event,
                                                    )
                                                "
                                            >
                                                <option value="" disabled>
                                                    Select staging zone
                                                </option>
                                                <option
                                                    v-for="location in stagingLocations"
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
                                            <p
                                                v-if="
                                                    fieldError(
                                                        `lines.${form.lines.indexOf(allocation)}.stock_location_id`,
                                                    )
                                                "
                                                class="mt-1 text-xs font-medium text-rose-600"
                                            >
                                                {{
                                                    fieldError(
                                                        `lines.${form.lines.indexOf(allocation)}.stock_location_id`,
                                                    )
                                                }}
                                            </p>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div
                                                class="flex items-center justify-center gap-2"
                                            >
                                                <button
                                                    type="button"
                                                    :disabled="
                                                        !canAddAllocation(
                                                            allocation.purchase_order_line_id,
                                                        )
                                                    "
                                                    class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-[#007882] hover:bg-[#007882]/10 disabled:cursor-not-allowed disabled:text-slate-300"
                                                    title="Add staging split"
                                                    @click="
                                                        addAllocation(
                                                            allocation.purchase_order_line_id,
                                                        )
                                                    "
                                                >
                                                    <Plus class="h-4 w-4" />
                                                </button>
                                                <button
                                                    type="button"
                                                    :disabled="
                                                        allocationsFor(
                                                            allocation.purchase_order_line_id,
                                                        ).length <= 1
                                                    "
                                                    class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-rose-500 hover:bg-rose-50 disabled:cursor-not-allowed disabled:text-slate-300"
                                                    title="Remove staging split"
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
                                    <tr v-if="form.lines.length === 0">
                                        <td
                                            colspan="7"
                                            class="px-6 py-12 text-center text-sm text-slate-500"
                                        >
                                            <PackageOpen
                                                class="mx-auto mb-3 h-8 w-8 text-slate-300"
                                            />
                                            Open the waiting PO list and start a
                                            receipt from an approved PO.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <p
                        v-if="form.errors.lines"
                        class="text-sm font-medium text-rose-600"
                    >
                        {{ form.errors.lines }}
                    </p>
                </section>
            </div>
        </form>
    </AppLayout>
</template>
