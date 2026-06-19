<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Boxes, Plus, Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';
import AlertError from '@/components/AlertError.vue';
import InputError from '@/components/InputError.vue';
import SearchDropdown from '@/components/SearchDropdown.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

type AdjustmentType = 'adjustment_in' | 'adjustment_out';

type InventoryBalance = {
    id: number;
    itemId: number;
    unitId: number;
    warehouseId: number;
    stockLocationId: number;
    itemCode: string;
    itemName: string;
    itemType: string;
    branch: string;
    warehouse: string;
    location: string;
    locationCode: string;
    locationType: string;
    unit: string;
    quantityOnHand: string;
    quantityAvailable: string;
    averageCost: string;
    stockValue: string;
};

type StockedLocation = {
    id: number;
    warehouseId: number;
    code: string;
    name: string;
    warehouse: string;
    branch: string;
    type: string;
};

type WarehouseOption = {
    id: number;
    name: string;
    branch: string;
};

type InventoryItem = {
    id: number;
    name: string;
    code: string;
    unitCode: string;
    cost: number;
    hasStock: boolean;
};

type FormLine = {
    item_id: number | '';
    stock_location_id: number | '';
    stock_balance_id: number | '';
    quantity: number;
    unit_cost: number;
    note: string;
};

const props = defineProps<{
    inventory: InventoryBalance[];
    locations: StockedLocation[];
    warehouses: WarehouseOption[];
    items: InventoryItem[];
    nextAdjustmentNo: string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Stock Operations' },
    { title: 'Stock Movements' },
    {
        title: 'Stock Adjustments',
        href: '/stock-movements/stock-adjustments',
    },
    {
        title: 'Create',
        href: '/stock-movements/stock-adjustments/create',
    },
];

const today = () => {
    const date = new Date();
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
};

const makeLine = (): FormLine => ({
    item_id: '',
    stock_location_id: '',
    stock_balance_id: '',
    quantity: 1,
    unit_cost: 0,
    note: '',
});

const form = useForm({
    warehouse_id: (props.warehouses[0]?.id ?? '') as number | '',
    adjustment_type: 'adjustment_in' as AdjustmentType,
    adjustment_date: today(),
    reason: 'Physical count discrepancy',
    note: '',
    lines: [makeLine()],
});

const isAdjustIn = computed(() => form.adjustment_type === 'adjustment_in');

const warehouseOptions = computed(() =>
    props.warehouses.map((warehouse) => ({
        value: warehouse.id,
        label: warehouse.name,
        description: warehouse.branch,
    })),
);

const itemOptions = computed(() =>
    props.items.map((item) => ({
        value: item.id,
        label: item.name,
        description: item.code,
        meta: item.unitCode,
        disabled: !isAdjustIn.value && !item.hasStock,
    })),
);

const warehouseInventory = computed(() =>
    props.inventory.filter(
        (balance) => Number(balance.warehouseId) === Number(form.warehouse_id),
    ),
);

const draftTotal = computed(() =>
    form.lines.reduce((total, line) => total + lineTotal(line), 0),
);

const validationErrors = computed(() =>
    Object.values(form.errors as Record<string, string>).filter(Boolean),
);

const firstLineError = computed(() => {
    const errors = form.errors as Record<string, string>;

    return (
        errors.lines ||
        Object.entries(errors).find(([key]) => key.startsWith('lines.'))?.[1]
    );
});

const canSubmit = computed(() => {
    if (!form.warehouse_id || !form.reason.trim()) {
        return false;
    }

    return form.lines.every((line) => {
        if (!line.item_id || line.quantity <= 0) {
            return false;
        }

        return isAdjustIn.value
            ? line.stock_location_id
            : line.stock_balance_id;
    });
});

function decimalValue(value: number | string | null | undefined) {
    return Number(String(value ?? 0).replaceAll(',', ''));
}

function money(value: number | string | null | undefined) {
    return decimalValue(value).toLocaleString(undefined, {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

function numberValue(value: number | string | null | undefined) {
    return decimalValue(value).toLocaleString(undefined, {
        minimumFractionDigits: 0,
        maximumFractionDigits: 4,
    });
}

function selectedItem(itemId: number | '') {
    return props.items.find((item) => item.id === Number(itemId));
}

function selectedBalance(balanceId: number | '') {
    return props.inventory.find((balance) => balance.id === Number(balanceId));
}

function selectWarehouse() {
    form.lines = [makeLine()];
}

function selectType(type: AdjustmentType) {
    form.adjustment_type = type;
    form.lines = [makeLine()];
    form.clearErrors();
}

function addLine() {
    form.lines.push(makeLine());
}

function removeLine(index: number) {
    if (form.lines.length === 1) {
        form.lines = [makeLine()];
        return;
    }

    form.lines.splice(index, 1);
}

function selectItem(line: FormLine) {
    const item = selectedItem(line.item_id);

    line.stock_location_id = '';
    line.stock_balance_id = '';
    line.quantity = 1;
    line.unit_cost = isAdjustIn.value ? Number(item?.cost ?? 0) : 0;
}

function putawayLocationOptionsFor() {
    return props.locations
        .filter(
            (location) =>
                Number(location.warehouseId) === Number(form.warehouse_id),
        )
        .map((location) => ({
            value: location.id,
            label: `${location.code} - ${location.name}`,
            description: location.warehouse,
            meta: 'Putaway',
        }));
}

function itemHasStock(line: FormLine) {
    return warehouseInventory.value.some(
        (balance) => Number(balance.itemId) === Number(line.item_id),
    );
}

function stockLocationOptionsFor(line: FormLine) {
    if (!line.item_id) {
        return [];
    }

    return warehouseInventory.value
        .filter((balance) => Number(balance.itemId) === Number(line.item_id))
        .map((balance) => ({
            value: balance.id,
            label: `${balance.locationCode} - ${balance.location}`,
            description: `${balance.quantityAvailable} ${balance.unit}`,
            meta: balance.warehouse,
        }));
}

function selectStockBalance(line: FormLine) {
    const balance = selectedBalance(line.stock_balance_id);

    if (!balance) return;

    line.quantity = Math.min(1, decimalValue(balance.quantityAvailable));
    line.unit_cost = decimalValue(balance.averageCost);
}

function availableFor(line: FormLine) {
    return decimalValue(
        selectedBalance(line.stock_balance_id)?.quantityAvailable,
    );
}

function quantityUsedFor(line: FormLine) {
    if (!line.stock_balance_id) {
        return Number(line.quantity || 0);
    }

    return form.lines
        .filter(
            (otherLine) => otherLine.stock_balance_id === line.stock_balance_id,
        )
        .reduce(
            (total, otherLine) => total + Number(otherLine.quantity || 0),
            0,
        );
}

function overLimitLine() {
    if (isAdjustIn.value) {
        return undefined;
    }

    return form.lines.find((line) => {
        if (!line.stock_balance_id) {
            return false;
        }

        return quantityUsedFor(line) > availableFor(line);
    });
}

function lineTotal(line: FormLine) {
    const cost = isAdjustIn.value
        ? Number(line.unit_cost || 0)
        : decimalValue(selectedBalance(line.stock_balance_id)?.averageCost);

    return Number(line.quantity || 0) * cost;
}

function showDashboard() {
    router.visit('/stock-movements/stock-adjustments');
}

function submitAdjustment() {
    form.clearErrors();

    if (!canSubmit.value) {
        if (!form.warehouse_id) {
            form.setError('warehouse_id', 'Warehouse is required.');
        }

        if (!form.reason.trim()) {
            form.setError('reason', 'Reason is required.');
        }

        if (!form.lines.some((line) => line.item_id)) {
            form.setError('lines', 'Add at least one inventory item.');
        }

        if (
            isAdjustIn.value &&
            form.lines.some((line) => line.item_id && !line.stock_location_id)
        ) {
            form.setError(
                'lines',
                'Select a putaway location for each adjust-in item.',
            );
        }

        if (
            !isAdjustIn.value &&
            form.lines.some((line) => line.item_id && !line.stock_balance_id)
        ) {
            form.setError(
                'lines',
                'Select a stocked source location for each adjust-out item.',
            );
        }

        return;
    }

    const invalidLine = overLimitLine();

    if (invalidLine) {
        const balance = selectedBalance(invalidLine.stock_balance_id);
        const itemName = balance?.itemName ?? 'Selected item';
        const available = numberValue(availableFor(invalidLine));
        const requested = numberValue(quantityUsedFor(invalidLine));

        form.setError(
            'lines',
            `${itemName} adjustment-out quantity is ${requested}, but only ${available} ${balance?.unit ?? ''} is available.`,
        );
        return;
    }

    form.post('/stock-movements/stock-adjustments', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            showDashboard();
        },
    });
}
</script>

<template>
    <Head title="Create Stock Adjustment" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #actions>
            <div class="flex gap-2">
                <Button
                    type="button"
                    variant="ghost"
                    class="h-9 font-semibold text-slate-600 hover:text-red-500"
                    :disabled="form.processing"
                    @click="showDashboard"
                >
                    Cancel
                </Button>
                <Button
                    type="button"
                    class="h-9 rounded-lg bg-[#007882] px-4 text-xs font-bold text-white shadow-md hover:bg-[#006773]"
                    :disabled="form.processing"
                    @click="submitAdjustment"
                >
                    Save Draft
                </Button>
            </div>
        </template>

        <main
            class="h-[calc(100dvh-4rem)] w-full [scrollbar-gutter:stable] overflow-y-scroll bg-[#f8fafc] p-4 text-slate-800 md:h-[calc(100dvh-5rem)] md:p-6 xl:p-8 2xl:p-10"
        >
            <section class="w-full">
                <div class="grid gap-6 xl:grid-cols-4">
                    <div class="space-y-6 xl:col-span-1">
                        <div
                            class="overflow-hidden rounded-lg border border-slate-100 bg-white shadow-sm"
                        >
                            <div class="border-b border-slate-100 p-5">
                                <h2
                                    class="text-xs font-bold tracking-wider text-slate-700 uppercase"
                                >
                                    Adjustment Details
                                </h2>
                            </div>
                            <div class="space-y-4 p-5">
                                <div>
                                    <label
                                        class="mb-1.5 block text-xs font-bold text-slate-500 uppercase"
                                    >
                                        Adjustment Number
                                    </label>
                                    <Input
                                        :model-value="nextAdjustmentNo"
                                        readonly
                                        class="h-10 rounded-lg border-slate-200 bg-slate-50 font-mono text-slate-500"
                                    />
                                </div>

                                <div>
                                    <label
                                        class="mb-1.5 block text-xs font-bold text-slate-500 uppercase"
                                    >
                                        Adjustment Type
                                    </label>
                                    <div
                                        class="grid grid-cols-2 overflow-hidden rounded-lg border border-slate-200 bg-white p-1"
                                    >
                                        <button
                                            type="button"
                                            class="rounded-md px-3 py-2 text-sm font-bold"
                                            :class="
                                                isAdjustIn
                                                    ? 'bg-[#007882] text-white shadow-sm'
                                                    : 'text-slate-500 hover:bg-slate-50'
                                            "
                                            @click="selectType('adjustment_in')"
                                        >
                                            Adjust In
                                        </button>
                                        <button
                                            type="button"
                                            class="rounded-md px-3 py-2 text-sm font-bold"
                                            :class="
                                                !isAdjustIn
                                                    ? 'bg-red-600 text-white shadow-sm'
                                                    : 'text-slate-500 hover:bg-slate-50'
                                            "
                                            @click="
                                                selectType('adjustment_out')
                                            "
                                        >
                                            Adjust Out
                                        </button>
                                    </div>
                                </div>

                                <div>
                                    <label
                                        class="mb-1.5 block text-xs font-bold text-slate-500 uppercase"
                                    >
                                        Warehouse
                                    </label>
                                    <SearchDropdown
                                        v-model="form.warehouse_id"
                                        :options="warehouseOptions"
                                        placeholder="Select warehouse"
                                        search-placeholder="Search warehouse..."
                                        empty-text="No warehouse found."
                                        @select="selectWarehouse"
                                    />
                                    <InputError
                                        :message="form.errors.warehouse_id"
                                    />
                                </div>

                                <div>
                                    <label
                                        class="mb-1.5 block text-xs font-bold text-slate-500 uppercase"
                                    >
                                        Adjustment Date
                                    </label>
                                    <Input
                                        v-model="form.adjustment_date"
                                        type="date"
                                        class="h-10 rounded-lg border-slate-200"
                                    />
                                    <InputError
                                        :message="form.errors.adjustment_date"
                                    />
                                </div>

                                <div>
                                    <label
                                        class="mb-1.5 block text-xs font-bold text-slate-500 uppercase"
                                    >
                                        Reason
                                    </label>
                                    <select
                                        v-model="form.reason"
                                        class="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-[#007882] focus:ring-2 focus:ring-[#007882]/20"
                                    >
                                        <option>
                                            Physical count discrepancy
                                        </option>
                                        <option>Found stock</option>
                                        <option>Missing stock</option>
                                        <option>Correction entry</option>
                                        <option>System reconciliation</option>
                                    </select>
                                    <InputError :message="form.errors.reason" />
                                </div>

                                <div>
                                    <label
                                        class="mb-1.5 block text-xs font-bold text-slate-500 uppercase"
                                    >
                                        Note
                                    </label>
                                    <textarea
                                        v-model="form.note"
                                        class="min-h-20 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#007882] focus:ring-2 focus:ring-[#007882]/20"
                                        placeholder="Optional adjustment note"
                                    />
                                </div>
                            </div>
                        </div>

                        <div
                            class="rounded-lg bg-[#2a4858] p-6 text-white shadow-lg"
                        >
                            <div class="space-y-3">
                                <div>
                                    <span class="text-lg font-bold">
                                        Estimated Adjustment Value
                                    </span>
                                    <p class="mt-1 text-sm text-white/60">
                                        Draft status until approved.
                                    </p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex size-11 items-center justify-center rounded-lg bg-white/10"
                                    >
                                        <Boxes class="size-5 text-[#fafa6e]" />
                                    </div>
                                    <span
                                        class="block text-2xl font-bold text-[#fafa6e]"
                                    >
                                        {{ money(draftTotal) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6 xl:col-span-3">
                        <div
                            class="overflow-hidden rounded-lg border border-slate-100 bg-white shadow-sm"
                        >
                            <div
                                class="flex items-center justify-between border-b border-slate-100 bg-slate-50 p-4"
                            >
                                <h3
                                    class="text-xs font-bold tracking-wider text-slate-700 uppercase"
                                >
                                    Inventory Items
                                </h3>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full min-w-[920px] text-sm">
                                    <thead
                                        class="border-b border-slate-100 text-slate-500"
                                    >
                                        <tr>
                                            <th
                                                class="min-w-64 px-4 py-3 text-left font-semibold"
                                            >
                                                Item
                                            </th>
                                            <th
                                                class="min-w-56 px-4 py-3 text-left font-semibold"
                                            >
                                                Location
                                            </th>
                                            <th
                                                v-if="!isAdjustIn"
                                                class="px-4 py-3 text-right font-semibold"
                                            >
                                                Available
                                            </th>
                                            <th
                                                class="px-4 py-3 text-center font-semibold"
                                            >
                                                Qty
                                            </th>
                                            <th
                                                class="px-4 py-3 text-right font-semibold"
                                            >
                                                Unit Cost
                                            </th>
                                            <th
                                                class="px-4 py-3 text-right font-semibold"
                                            >
                                                Total
                                            </th>
                                            <th class="w-12 px-4 py-3"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="(line, index) in form.lines"
                                            :key="index"
                                            class="border-b border-slate-50 last:border-0"
                                        >
                                            <td class="px-4 py-4">
                                                <SearchDropdown
                                                    v-model="line.item_id"
                                                    :options="itemOptions"
                                                    placeholder="Select item"
                                                    search-placeholder="Search item..."
                                                    empty-text="No item found."
                                                    input-class="border-transparent font-medium"
                                                    @select="selectItem(line)"
                                                />
                                            </td>
                                            <td class="px-4 py-4">
                                                <SearchDropdown
                                                    v-if="isAdjustIn"
                                                    v-model="
                                                        line.stock_location_id
                                                    "
                                                    :options="
                                                        putawayLocationOptionsFor()
                                                    "
                                                    :disabled="
                                                        !line.item_id ||
                                                        !form.warehouse_id
                                                    "
                                                    placeholder="Putaway location"
                                                    search-placeholder="Search location..."
                                                    empty-text="No putaway location found."
                                                    input-class="border-transparent font-medium"
                                                />
                                                <SearchDropdown
                                                    v-else
                                                    v-model="
                                                        line.stock_balance_id
                                                    "
                                                    :options="
                                                        stockLocationOptionsFor(
                                                            line,
                                                        )
                                                    "
                                                    :disabled="
                                                        !line.item_id ||
                                                        !itemHasStock(line)
                                                    "
                                                    placeholder="Source location"
                                                    search-placeholder="Search location..."
                                                    empty-text="No stocked location found."
                                                    input-class="border-transparent font-medium"
                                                    @select="
                                                        selectStockBalance(line)
                                                    "
                                                />
                                            </td>
                                            <td
                                                v-if="!isAdjustIn"
                                                class="px-4 py-4 text-right font-bold text-[#2a4858]"
                                            >
                                                {{
                                                    numberValue(
                                                        availableFor(line),
                                                    )
                                                }}
                                                {{
                                                    selectedBalance(
                                                        line.stock_balance_id,
                                                    )?.unit ?? ''
                                                }}
                                            </td>
                                            <td class="px-4 py-4">
                                                <Input
                                                    v-model.number="
                                                        line.quantity
                                                    "
                                                    type="number"
                                                    min="0.0001"
                                                    step="0.0001"
                                                    :max="
                                                        isAdjustIn
                                                            ? undefined
                                                            : availableFor(line)
                                                    "
                                                    class="h-9 w-28 rounded border-slate-200 text-center"
                                                />
                                            </td>
                                            <td class="px-4 py-4">
                                                <Input
                                                    v-if="isAdjustIn"
                                                    v-model.number="
                                                        line.unit_cost
                                                    "
                                                    type="number"
                                                    min="0"
                                                    step="0.0001"
                                                    class="h-9 w-28 rounded border-slate-200 text-right font-mono"
                                                />
                                                <span
                                                    v-else
                                                    class="block text-right font-mono font-bold text-slate-700"
                                                >
                                                    {{
                                                        money(
                                                            selectedBalance(
                                                                line.stock_balance_id,
                                                            )?.averageCost,
                                                        )
                                                    }}
                                                </span>
                                            </td>
                                            <td
                                                class="px-4 py-4 text-right font-mono font-bold"
                                                :class="
                                                    isAdjustIn
                                                        ? 'text-green-600'
                                                        : 'text-red-600'
                                                "
                                            >
                                                {{ money(lineTotal(line)) }}
                                            </td>
                                            <td class="px-4 py-4 text-center">
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    class="h-8 w-8 rounded-lg p-0 text-slate-300 hover:bg-red-50 hover:text-red-500"
                                                    title="Remove item"
                                                    @click="removeLine(index)"
                                                >
                                                    <Trash2 class="size-4" />
                                                </Button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div
                                class="flex flex-col gap-3 border-t border-slate-100 px-4 py-4 sm:flex-row sm:items-center sm:justify-between"
                            >
                                <InputError :message="firstLineError" />
                                <Button
                                    type="button"
                                    variant="outline"
                                    class="h-10 rounded-lg border-[#007882]/20 bg-white font-bold text-[#007882]"
                                    @click="addLine"
                                >
                                    <Plus class="size-4" />
                                    Add Line
                                </Button>
                            </div>
                        </div>

                        <AlertError
                            v-if="validationErrors.length"
                            title="Stock adjustment cannot be saved."
                            :errors="validationErrors"
                        />
                    </div>
                </div>
            </section>
        </main>
    </AppLayout>
</template>
