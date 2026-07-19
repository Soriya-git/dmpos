<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    Camera,
    PackageOpen,
    Plus,
    Trash2,
    TriangleAlert,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

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

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Stock Operations' },
    { title: 'Goods Receipt', href: '/goods-receipts' },
    { title: 'Create' },
];

const form = useForm({
    purchase_order_id: props.purchaseOrder?.id ?? '',
    note: '',
    photos: [] as File[],
    lines: [] as {
        allocation_key: string;
        purchase_order_line_id: number;
        stock_location_id: number | '';
        quantity_received: number;
        unit_cost: number;
        selected: boolean;
    }[],
});

const closeDialogOpen = ref(false);
const closing = ref(false);
const closeReason = ref('');

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
                unit_cost: Number(line.unit_cost || 0),
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

    if (!poLine || remaining <= 0) return;

    form.lines.push({
        allocation_key: `${lineId}-${Date.now()}-${form.lines.length}`,
        purchase_order_line_id: lineId,
        stock_location_id: props.stagingLocations[0]?.id ?? '',
        quantity_received: remaining,
        unit_cost: Number(poLine.unit_cost || 0),
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

    if (!line) return;

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
    form.post('/goods-receipts', { preserveScroll: true, forceFormData: true });
}

function closePurchaseOrder() {
    const purchaseOrderId = Number(form.purchase_order_id);
    if (
        !purchaseOrderId ||
        closeReason.value.trim().length < 5 ||
        closing.value
    )
        return;

    closing.value = true;
    router.patch(
        `/goods-receipts/purchase-orders/${purchaseOrderId}/close`,
        { reason: closeReason.value.trim() },
        {
            preserveScroll: true,
            onSuccess: () => {
                closeDialogOpen.value = false;
            },
            onFinish: () => {
                closing.value = false;
            },
        },
    );
}

function selectPhotos(event: Event) {
    form.photos = Array.from(
        (event.target as HTMLInputElement).files ?? [],
    ).slice(0, 6);
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

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #actions>
            <div class="flex gap-2">
                <Button
                    type="button"
                    variant="ghost"
                    class="h-9 font-semibold text-slate-600 hover:text-red-500"
                    :disabled="form.processing"
                    @click="
                        router.visit('/goods-receipts/approved-purchase-orders')
                    "
                >
                    Cancel
                </Button>
                <Button
                    type="button"
                    class="h-9 rounded-lg bg-[#23aa8f] px-4 text-xs font-bold text-white shadow-md hover:bg-[#1e917a]"
                    :disabled="!canSubmit"
                    @click="submit"
                >
                    {{ form.processing ? 'Saving...' : 'Save GR' }}
                </Button>
                <Button
                    type="button"
                    variant="destructive"
                    class="h-9 rounded-lg px-4 text-xs font-bold shadow-md"
                    :disabled="
                        !form.purchase_order_id || form.processing || closing
                    "
                    @click="closeDialogOpen = true"
                >
                    Cancel GR
                </Button>
            </div>
        </template>

        <Dialog v-model:open="closeDialogOpen">
            <DialogContent class="sm:max-w-md" :show-close-button="!closing">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2 text-rose-700">
                        <TriangleAlert class="size-5" />
                        Cancel this receiving case?
                    </DialogTitle>
                    <DialogDescription>
                        This closes the selected purchase order and cancels
                        every remaining undelivered quantity.
                    </DialogDescription>
                </DialogHeader>

                <Alert variant="destructive">
                    <TriangleAlert />
                    <AlertTitle>This action cannot be undone</AlertTitle>
                    <AlertDescription>
                        Previously received items will be kept, but no more
                        Goods Receipts can be created for this PO. Confirm only
                        when the supplier cannot deliver the remaining items.
                    </AlertDescription>
                </Alert>

                <div class="space-y-2">
                    <label
                        for="gr-cancel-reason"
                        class="text-sm font-bold text-slate-700"
                    >
                        Cancellation reason <span class="text-rose-600">*</span>
                    </label>
                    <textarea
                        id="gr-cancel-reason"
                        v-model="closeReason"
                        rows="4"
                        maxlength="1000"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-rose-400 focus:ring-2 focus:ring-rose-100"
                        placeholder="Explain why the supplier cannot deliver the remaining quantity..."
                    ></textarea>
                    <p class="text-xs text-slate-400">
                        At least 5 characters are required.
                    </p>
                </div>

                <DialogFooter class="gap-2 sm:gap-0">
                    <Button
                        type="button"
                        variant="outline"
                        :disabled="closing"
                        @click="closeDialogOpen = false"
                    >
                        No, Keep Open
                    </Button>
                    <Button
                        type="button"
                        variant="destructive"
                        :disabled="closing || closeReason.trim().length < 5"
                        @click="closePurchaseOrder"
                    >
                        {{ closing ? 'Cancelling...' : 'Yes, Cancel GR' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <main
            class="h-[calc(100dvh-4rem)] w-full scrollbar-gutter-stable overflow-y-scroll bg-[#f8fafc] p-4 text-slate-800 md:h-[calc(100dvh-5rem)] md:p-6 xl:p-8 2xl:p-10"
        >
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-4 2xl:gap-8">
                <!-- Left: Info -->
                <aside class="space-y-6 xl:col-span-1">
                    <div
                        class="rounded-lg border border-slate-100 bg-white p-6 shadow-sm"
                    >
                        <h2
                            class="mb-4 text-sm font-bold text-slate-700 uppercase"
                        >
                            Source PO
                        </h2>
                        <div class="space-y-4">
                            <div>
                                <label
                                    class="mb-1 block text-xs font-bold text-slate-500 uppercase"
                                >
                                    Purchase Order
                                </label>
                                <select
                                    v-model="form.purchase_order_id"
                                    class="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-600 outline-none focus:border-[#007882] focus:ring-2 focus:ring-[#007882]/20"
                                >
                                    <option value="" disabled>
                                        Select approved PO
                                    </option>
                                    <option
                                        v-for="order in purchaseOrders"
                                        :key="order.id"
                                        :value="order.id"
                                    >
                                        {{ order.po_no }} —
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
                                        '-'
                                    }}
                                </p>
                                <p class="text-xs text-slate-500">
                                    {{ selectedPurchaseOrder.po_no }}
                                </p>
                            </div>

                            <p
                                v-if="purchaseOrders.length === 0"
                                class="text-sm text-slate-500"
                            >
                                No approved POs available for receipt.
                            </p>
                        </div>
                    </div>

                    <div
                        class="rounded-lg border border-slate-100 bg-white p-6 shadow-sm"
                    >
                        <h2
                            class="mb-4 text-sm font-bold text-slate-700 uppercase"
                        >
                            Receipt Details
                        </h2>
                        <div class="space-y-4">
                            <div>
                                <label
                                    class="mb-1 block text-xs font-bold text-slate-500 uppercase"
                                >
                                    Receipt Number
                                </label>
                                <input
                                    :value="nextReceiptNo"
                                    readonly
                                    class="h-10 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 font-mono text-sm font-bold text-slate-500"
                                />
                            </div>
                            <div>
                                <label
                                    class="mb-1 block text-xs font-bold text-slate-500 uppercase"
                                >
                                    Note
                                </label>
                                <textarea
                                    v-model="form.note"
                                    rows="3"
                                    class="min-h-20 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#007882] focus:ring-2 focus:ring-[#007882]/20"
                                    placeholder="Optional receipt note"
                                ></textarea>
                            </div>
                            <div>
                                <label
                                    class="mb-1 flex items-center gap-2 text-xs font-bold text-slate-500 uppercase"
                                >
                                    <Camera class="size-4" /> Receipt Pictures
                                </label>
                                <input
                                    type="file"
                                    accept="image/*"
                                    capture="environment"
                                    multiple
                                    class="block w-full text-xs text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-[#007882]/10 file:px-3 file:py-2 file:font-bold file:text-[#007882]"
                                    @change="selectPhotos"
                                />
                                <p class="mt-1 text-xs text-slate-400">
                                    Up to 6 pictures.
                                    {{ form.photos.length }} selected.
                                </p>
                                <p
                                    v-if="form.errors.photos"
                                    class="mt-1 text-xs text-rose-600"
                                >
                                    {{ form.errors.photos }}
                                </p>
                            </div>
                        </div>
                    </div>
                </aside>

                <!-- Right: Items -->
                <section class="space-y-6 xl:col-span-3">
                    <div
                        class="overflow-hidden rounded-lg border border-slate-100 bg-white shadow-sm"
                    >
                        <div
                            class="flex items-center justify-between border-b border-slate-100 bg-slate-50 p-4"
                        >
                            <h2
                                class="text-xs font-bold tracking-wider text-slate-700 uppercase"
                            >
                                Verify Inbound Items
                            </h2>
                            <span
                                v-if="form.lines.length > 0"
                                class="text-xs text-slate-400"
                            >
                                {{
                                    form.lines.filter((l) => l.selected).length
                                }}
                                / {{ form.lines.length }} selected
                            </span>
                        </div>

                        <!-- Phone receiving view: only operational receiving fields. -->
                        <div class="space-y-3 p-4 md:hidden">
                            <div
                                v-for="allocation in form.lines"
                                :key="`mobile-${allocation.allocation_key}`"
                                class="rounded-xl border border-slate-200 bg-white p-4"
                            >
                                <div class="mb-3 font-bold text-[#2a4858]">
                                    {{
                                        poLineFor(
                                            allocation.purchase_order_line_id,
                                        )?.item_name ?? 'Item'
                                    }}
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <p
                                            class="text-xs font-bold text-slate-400 uppercase"
                                        >
                                            Qty in PO
                                        </p>
                                        <p
                                            class="mt-1 text-lg font-bold text-slate-700"
                                        >
                                            {{
                                                poLineFor(
                                                    allocation.purchase_order_line_id,
                                                )?.quantity_ordered ?? 0
                                            }}
                                        </p>
                                    </div>
                                    <div>
                                        <label
                                            class="text-xs font-bold text-[#007882] uppercase"
                                            >Qty Received</label
                                        >
                                        <input
                                            type="number"
                                            min="0"
                                            :max="maxQuantityFor(allocation)"
                                            :value="
                                                allocation.quantity_received
                                            "
                                            class="mt-1 h-11 w-full rounded-lg border-2 border-[#007882] px-3 text-lg font-bold text-[#007882]"
                                            @input="
                                                updateQuantityFromEvent(
                                                    allocation.allocation_key,
                                                    $event,
                                                )
                                            "
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="hidden overflow-x-auto md:block">
                            <table class="w-full text-sm">
                                <thead
                                    class="border-b border-slate-100 text-slate-500"
                                >
                                    <tr>
                                        <th
                                            class="w-12 px-4 py-3 text-center font-semibold"
                                        >
                                            Use
                                        </th>
                                        <th
                                            class="min-w-48 px-4 py-3 text-left font-semibold"
                                        >
                                            Product / SKU
                                        </th>
                                        <th
                                            class="px-4 py-3 text-center font-semibold"
                                        >
                                            Ordered
                                        </th>
                                        <th
                                            class="px-4 py-3 text-center font-semibold"
                                        >
                                            Prev. Received
                                        </th>
                                        <th
                                            class="px-4 py-3 text-center font-semibold text-[#007882]"
                                        >
                                            Received Today
                                        </th>
                                        <th
                                            class="px-4 py-3 text-right font-semibold text-[#007882]"
                                        >
                                            Actual Unit Cost
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left font-semibold text-[#007882]"
                                        >
                                            Staging Zone
                                        </th>
                                        <th
                                            class="w-20 px-4 py-3 text-center font-semibold"
                                        >
                                            Split
                                        </th>
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
                                                @change="
                                                    updateSelected(
                                                        allocation.allocation_key,
                                                        $event,
                                                    )
                                                "
                                            />
                                        </td>
                                        <td class="px-4 py-4">
                                            <div
                                                class="font-bold text-[#2a4858]"
                                            >
                                                {{
                                                    poLineFor(
                                                        allocation.purchase_order_line_id,
                                                    )?.item_name ?? 'Item'
                                                }}
                                            </div>
                                            <p
                                                class="mt-1 font-mono text-xs text-slate-400"
                                            >
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
                                            </p>
                                        </td>
                                        <td
                                            class="px-4 py-4 text-center font-bold text-slate-500"
                                        >
                                            {{
                                                poLineFor(
                                                    allocation.purchase_order_line_id,
                                                )?.quantity_ordered ?? 0
                                            }}
                                        </td>
                                        <td
                                            class="px-4 py-4 text-center font-bold text-slate-400"
                                        >
                                            {{
                                                poLineFor(
                                                    allocation.purchase_order_line_id,
                                                )?.quantity_received ?? 0
                                            }}
                                        </td>
                                        <td class="px-4 py-4 text-center">
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
                                        <td class="px-4 py-4 text-center">
                                            <input
                                                v-model.number="
                                                    allocation.unit_cost
                                                "
                                                type="number"
                                                min="0"
                                                step="0.0001"
                                                class="mx-auto h-9 w-28 rounded-lg border border-slate-200 px-2 text-right font-mono"
                                            />
                                        </td>
                                        <td class="px-4 py-4">
                                            <select
                                                :value="
                                                    allocation.stock_location_id
                                                "
                                                :disabled="!allocation.selected"
                                                class="h-9 w-full min-w-44 rounded-lg border border-slate-200 bg-white px-2 text-sm outline-none focus:border-[#007882] focus:ring-2 focus:ring-[#007882]/20 disabled:bg-slate-50 disabled:text-slate-400"
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
                                                    —
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
                                                class="flex items-center justify-center gap-1"
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
                                                    class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-rose-400 hover:bg-rose-50 disabled:cursor-not-allowed disabled:text-slate-300"
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
                                            colspan="8"
                                            class="px-6 py-16 text-center text-sm text-slate-400"
                                        >
                                            <PackageOpen
                                                class="mx-auto mb-3 size-8 text-slate-300"
                                            />
                                            Select an approved PO above to load
                                            items for receipt.
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
        </main>
    </AppLayout>
</template>
