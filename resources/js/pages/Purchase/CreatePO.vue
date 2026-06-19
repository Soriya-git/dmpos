<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { PackagePlus, Plus, Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import SearchDropdown from '@/components/SearchDropdown.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

type InventoryItem = {
    id: number;
    name: string;
    code?: string | null;
    unit_id: number;
    unit_code?: string | null;
    cost: number;
};

type Unit = {
    id: number;
    name: string;
    code: string;
};

type FormLine = {
    item_id: number | '';
    unit_id: number | '';
    quantity_ordered: number;
    unit_cost: number;
    note: string;
};

type SupplierOption = {
    value: string;
    label: string;
    description?: string | null;
    meta?: string | null;
};

const props = defineProps<{
    items: InventoryItem[];
    units: Unit[];
    nextPoNo: string;
    supplierOptions: SupplierOption[];
}>();

const emit = defineEmits<{
    success: [];
}>();

const today = () => {
    const date = new Date();
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

const makeLine = (): FormLine => ({
    item_id: '',
    unit_id: '',
    quantity_ordered: 1,
    unit_cost: 0,
    note: '',
});

const form = useForm({
    supplier_name: '',
    supplier_phone: '',
    supplier_address: '',
    order_date: today(),
    expected_date: '',
    note: '',
    lines: [makeLine()],
});

const grandTotal = computed(() =>
    form.lines.reduce(
        (total, line) =>
            total + Number(line.quantity_ordered || 0) * Number(line.unit_cost || 0),
        0,
    ),
);

const itemOptions = computed(() =>
    props.items.map((item) => ({
        value: item.id,
        label: item.name,
        description: item.code,
        meta: `${item.unit_code ?? 'Unit'} / ${money(item.cost)}`,
    })),
);

const canSubmit = computed(
    () =>
        form.supplier_name.trim().length > 0 &&
        form.lines.some((line) => line.item_id && line.quantity_ordered > 0),
);

const firstLineError = computed(() => {
    const errors = form.errors as Record<string, string>;
    return (
        errors.lines ||
        Object.entries(errors).find(([key]) => key.startsWith('lines.'))?.[1]
    );
});

function money(value: number | string | null | undefined) {
    return Number(value ?? 0).toLocaleString(undefined, {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
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

function selectedItem(itemId: number | '') {
    return props.items.find((item) => item.id === itemId);
}

function selectItem(line: FormLine) {
    const item = selectedItem(line.item_id);
    if (!item) return;
    line.unit_id = item.unit_id;
    line.unit_cost = Number(item.cost || 0);
}

function selectSupplier(
    option: { description?: string | null; meta?: string | null } | null,
) {
    if (!option) return;
    form.supplier_phone = option.description ?? '';
    form.supplier_address = option.meta ?? '';
}

function lineTotal(line: FormLine) {
    return Number(line.quantity_ordered || 0) * Number(line.unit_cost || 0);
}

function submit() {
    form.clearErrors();

    if (!canSubmit.value) {
        if (!form.supplier_name.trim()) {
            form.setError('supplier_name', 'Supplier is required.');
        }
        if (!form.lines.some((line) => line.item_id)) {
            form.setError('lines', 'Add at least one item.');
        }
        return;
    }

    form.post('/purchase', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            form.order_date = today();
            form.lines = [makeLine()];
            emit('success');
        },
    });
}

defineExpose({ submit, isProcessing: computed(() => form.processing) });
</script>

<template>
    <div class="w-full">
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-4 2xl:gap-8">
            <div class="space-y-6 xl:col-span-1">
                <div
                    class="rounded-lg border border-slate-100 bg-white p-6 shadow-sm"
                >
                    <h3
                        class="mb-4 flex items-center text-sm font-bold text-slate-700 uppercase"
                    >
                        <PackagePlus class="mr-2 size-4 text-[#007882]" />
                        Info
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <label
                                class="mb-1 block text-xs font-bold text-slate-500 uppercase"
                            >
                                PO Number
                            </label>
                            <Input
                                :model-value="nextPoNo"
                                readonly
                                class="h-10 rounded-lg border-slate-200 bg-slate-50 font-mono text-slate-500"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-xs font-bold text-slate-500 uppercase"
                            >
                                Supplier
                            </label>
                            <SearchDropdown
                                v-model="form.supplier_name"
                                :options="supplierOptions"
                                allow-custom
                                placeholder="Supplier name"
                                search-placeholder="Search supplier..."
                                empty-text="No supplier found. Type a new supplier name."
                                @select="selectSupplier"
                            />
                            <InputError :message="form.errors.supplier_name" />
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-xs font-bold text-slate-500 uppercase"
                            >
                                Supplier Phone
                            </label>
                            <Input
                                v-model="form.supplier_phone"
                                class="h-10 rounded-lg border-slate-200"
                                placeholder="Optional phone"
                            />
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label
                                    class="mb-1 block text-xs font-bold text-amber-600 uppercase"
                                >
                                    Expected Date
                                </label>
                                <Input
                                    v-model="form.expected_date"
                                    type="date"
                                    class="h-10 rounded-lg border-amber-200 bg-amber-50 text-xs"
                                />
                            </div>
                            <div>
                                <label
                                    class="mb-1 block text-xs font-bold text-slate-500 uppercase"
                                >
                                    Order Date
                                </label>
                                <Input
                                    v-model="form.order_date"
                                    type="date"
                                    class="h-10 rounded-lg border-slate-200 text-xs"
                                />
                            </div>
                        </div>
                        <InputError :message="form.errors.order_date" />
                        <div>
                            <label
                                class="mb-1 block text-xs font-bold text-slate-500 uppercase"
                            >
                                Note
                            </label>
                            <textarea
                                v-model="form.note"
                                class="min-h-20 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#007882] focus:ring-2 focus:ring-[#007882]/20"
                                placeholder="Optional note"
                            />
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
                            Order Items
                        </h3>
                        <Button
                            type="button"
                            class="h-8 rounded bg-[#23aa8f] px-3 text-xs font-bold text-white hover:bg-[#1e917a]"
                            @click="addLine"
                        >
                            <Plus class="size-3.5" />
                            Add Item
                        </Button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead
                                class="border-b border-slate-100 text-slate-500"
                            >
                                <tr>
                                    <th
                                        class="min-w-64 px-4 py-3 text-left font-semibold"
                                    >
                                        Item Details
                                    </th>
                                    <th
                                        class="px-4 py-3 text-center font-semibold"
                                    >
                                        Qty
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left font-semibold"
                                    >
                                        Unit
                                    </th>
                                    <th
                                        class="px-4 py-3 text-right font-semibold"
                                    >
                                        Cost
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
                                        <Input
                                            v-model.number="line.quantity_ordered"
                                            type="number"
                                            min="0.0001"
                                            step="0.0001"
                                            class="h-9 w-24 rounded border-slate-200 text-center"
                                        />
                                    </td>
                                    <td class="px-4 py-4">
                                        <select
                                            v-model="line.unit_id"
                                            class="h-9 w-24 rounded border border-slate-200 bg-white px-2 text-xs outline-none focus:border-[#007882] focus:ring-2 focus:ring-[#007882]/20"
                                        >
                                            <option value="">Unit</option>
                                            <option
                                                v-for="unit in units"
                                                :key="unit.id"
                                                :value="unit.id"
                                            >
                                                {{ unit.code }}
                                            </option>
                                        </select>
                                    </td>
                                    <td class="px-4 py-4">
                                        <Input
                                            v-model.number="line.unit_cost"
                                            type="number"
                                            min="0"
                                            step="0.0001"
                                            class="h-9 w-28 rounded border-slate-200 text-right font-mono"
                                        />
                                    </td>
                                    <td
                                        class="px-4 py-4 text-right font-mono font-bold text-slate-700"
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
                    <InputError class="px-4 pb-4" :message="firstLineError" />
                </div>

                <div class="rounded-lg bg-[#2a4858] p-6 text-white shadow-lg">
                    <div class="flex items-center justify-between">
                        <span class="text-lg font-bold">
                            Estimated Grand Total
                        </span>
                        <span class="text-2xl font-bold text-[#fafa6e]">
                            {{ money(grandTotal) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
