<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import {
    Box,
    FlaskConical,
    PackagePlus,
    Plus,
    Trash2,
    Ruler,
    Search,
    X,
} from 'lucide-vue-next';
import { computed } from 'vue';
import ApprovalActionMenu from '@/components/master-data/ApprovalActionMenu.vue';
import MasterDataTable from '@/components/master-data/MasterDataTable.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Sheet,
    SheetContent,
    SheetFooter,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import {
    type ApprovalStatus,
    type BomRecord,
    type ItemRecord,
    type MasterDataView,
    type UnitRecord,
    useItemsBomMaster,
} from '@/composables/master-data/useItemsBomMaster';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Master Data' },
    { title: 'Items & BOM', href: '/master-data/products' },
];

type OptionRecord = {
    id: number;
    name: string;
    code: string | null;
};

type UnitOption = OptionRecord;

const props = defineProps<{
    items: ItemRecord[];
    bom: BomRecord[];
    units: UnitRecord[];
    itemOptions: (OptionRecord & { unit_id: number })[];
    unitOptions: UnitOption[];
    branchOptions: OptionRecord[];
}>();

const {
    activeView,
    search,
    panelOpen,
    panelKind,
    selectedRecord,
    items,
    bom,
    units,
    panelTitle,
    panelSubtitle,
    switchView,
    openPanel,
    closePanel,
    setStatus,
} = useItemsBomMaster(props.items, props.bom, props.units);

const itemForm = useForm({
    name: '',
    code: '',
    branch_id: '',
    unit_id: '',
    item_type: 'raw_material',
    cost: '0',
    minimum_stock_qty: '0',
    is_stockable: true,
    description: '',
});

const bomForm = useForm({
    name: '',
    bom_no: '',
    branch_id: '',
    output_item_id: '',
    output_quantity: '1',
    status: 'active',
    note: '',
    lines: [
        {
            component_item_id: '',
            unit_id: '',
            quantity: '1',
            wastage_percent: '0',
            estimated_cost: '0',
            note: '',
        },
    ],
});

const tabs: {
    key: MasterDataView;
    label: string;
    icon: typeof Box;
}[] = [
    { key: 'items', label: 'Item Registry', icon: Box },
    { key: 'bom', label: 'Recipe Master (BOM)', icon: FlaskConical },
    { key: 'units', label: 'Unit & Package', icon: Ruler },
];

const normalizedSearch = computed(() => search.value.trim().toLowerCase());

const filteredItems = computed(() => filterRows(items));
const filteredBom = computed(() => filterRows(bom));
const filteredUnits = computed(() => filterRows(units));

const panelName = computed(() => {
    const record = selectedRecord.value;

    if (!record) {
        return '';
    }

    if ('targetProduct' in record) {
        return record.targetProduct;
    }

    return record.name;
});

const showRatioFields = computed(() => panelKind.value === 'units');

function filterRows<T extends Record<string, unknown>>(rows: T[]) {
    if (!normalizedSearch.value) {
        return rows;
    }

    return rows.filter((row) =>
        Object.values(row).some((value) =>
            String(value).toLowerCase().includes(normalizedSearch.value),
        ),
    );
}

function statusLabel(status: ApprovalStatus) {
    return status === 'cancelled'
        ? 'Cancel'
        : status.charAt(0).toUpperCase() + status.slice(1);
}

function statusClass(status: ApprovalStatus) {
    const classes: Record<ApprovalStatus, string> = {
        draft: 'border-slate-200 bg-slate-100 text-slate-600',
        pending: 'border-amber-200 bg-amber-50 text-amber-700',
        approved: 'border-[#23AA8F]/20 bg-[#23AA8F]/10 text-[#16836f]',
        rejected: 'border-rose-200 bg-rose-50 text-rose-700',
        cancelled: 'border-slate-300 bg-slate-100 text-slate-500',
    };

    return classes[status];
}

function viewItem(record: ItemRecord) {
    openPanel('items', record);
}

function viewBom(record: BomRecord) {
    openPanel('bom', record);
}

function viewUnit(record: UnitRecord) {
    openPanel('units', record);
}

function openCreatePanel(kind: MasterDataView = activeView.value) {
    itemForm.reset();
    bomForm.reset();
    itemForm.clearErrors();
    bomForm.clearErrors();
    openPanel(kind);
}

function addBomLine() {
    bomForm.lines.push({
        component_item_id: '',
        unit_id: '',
        quantity: '1',
        wastage_percent: '0',
        estimated_cost: '0',
        note: '',
    });
}

function removeBomLine(index: number) {
    if (bomForm.lines.length <= 1) {
        return;
    }

    bomForm.lines.splice(index, 1);
}

function syncLineUnit(index: number) {
    const line = bomForm.lines[index];
    const item = props.itemOptions.find(
        (option) => option.id === Number(line.component_item_id),
    );

    if (item) {
        line.unit_id = String(item.unit_id);
    }
}

function submitPanel() {
    if (selectedRecord.value || panelKind.value === 'units') {
        closePanel();

        return;
    }

    if (panelKind.value === 'items') {
        itemForm.post('/master-data/products/items', {
            preserveScroll: true,
            onSuccess: closePanel,
        });

        return;
    }

    bomForm.post('/master-data/products/bom', {
        preserveScroll: true,
        onSuccess: closePanel,
    });
}

function approveItem(record: ItemRecord) {
    setStatus(items, record.id, 'approved');
}

function rejectItem(record: ItemRecord) {
    setStatus(items, record.id, 'rejected');
}

function cancelItem(record: ItemRecord) {
    setStatus(items, record.id, 'cancelled');
}

function approveBom(record: BomRecord) {
    setStatus(bom, record.id, 'approved');
}

function rejectBom(record: BomRecord) {
    setStatus(bom, record.id, 'rejected');
}

function cancelBom(record: BomRecord) {
    setStatus(bom, record.id, 'cancelled');
}

function approveUnit(record: UnitRecord) {
    setStatus(units, record.id, 'approved');
}

function rejectUnit(record: UnitRecord) {
    setStatus(units, record.id, 'rejected');
}

function cancelUnit(record: UnitRecord) {
    setStatus(units, record.id, 'cancelled');
}
</script>

<template>
    <Head title="Items & BOM" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 lg:p-6">
            <div
                class="flex flex-col justify-between gap-4 lg:flex-row lg:items-center"
            >
                <div>
                    <h1
                        class="text-2xl font-semibold tracking-tight text-[#2A4858]"
                    >
                        Items & BOM
                    </h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Product registry, recipes, and unit conversion master
                        data.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <div class="relative">
                        <Search
                            class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"
                        />
                        <Input
                            v-model="search"
                            placeholder="Search data..."
                            class="h-9 w-52 rounded-lg border-slate-200 bg-white pl-9 text-xs focus-visible:ring-[#007882] lg:w-64"
                        />
                    </div>
                    <Button
                        class="h-9 rounded-lg bg-[#007882] px-4 text-xs font-bold text-white hover:bg-[#006871]"
                        @click="openCreatePanel()"
                    >
                        <Plus class="size-4" />
                        New
                    </Button>
                </div>
            </div>

            <div
                class="flex gap-4 overflow-x-auto border-b border-slate-200 pb-1 whitespace-nowrap"
            >
                <button
                    v-for="tab in tabs"
                    :key="tab.key"
                    class="flex items-center gap-2 border-b-3 px-2 py-2 text-xs tracking-wider uppercase transition-colors"
                    :class="
                        activeView === tab.key
                            ? 'border-[#23AA8F] font-extrabold text-[#007882]'
                            : 'border-transparent text-slate-500 hover:text-[#007882]'
                    "
                    @click="switchView(tab.key)"
                >
                    <component :is="tab.icon" class="size-4" />
                    {{ tab.label }}
                </button>
            </div>

            <MasterDataTable
                v-if="activeView === 'items'"
                :rows="filteredItems"
                empty-text="No item registry data found."
            >
                <template #head>
                    <th
                        class="w-12 px-4 py-3 text-center text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        #
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        SKU / Item Code
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Item Description
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Category
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Primary Unit
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Status
                    </th>
                    <th
                        class="w-16 px-4 py-3 text-center text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Action
                    </th>
                </template>
                <template #row="{ row, index }">
                    <td
                        class="px-4 py-3 text-center text-[10px] text-slate-400"
                    >
                        {{ String(index + 1).padStart(2, '0') }}
                    </td>
                    <td
                        class="px-4 py-3 font-mono text-xs font-bold text-[#007882]"
                    >
                        {{ row.code }}
                    </td>
                    <td class="px-4 py-3 text-sm font-bold text-slate-700">
                        {{ row.name }}
                    </td>
                    <td class="px-4 py-3">
                        <span
                            class="rounded bg-blue-50 px-2 py-0.5 text-[10px] font-bold text-blue-600"
                        >
                            {{ row.category }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-500">
                        {{ row.primaryUnit }}
                    </td>
                    <td class="px-4 py-3">
                        <span
                            class="rounded border px-2 py-0.5 text-[10px] font-bold"
                            :class="statusClass(row.status)"
                        >
                            {{ statusLabel(row.status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <ApprovalActionMenu
                            :status="row.status"
                            @view="viewItem(row)"
                            @approve="approveItem(row)"
                            @reject="rejectItem(row)"
                            @cancel="cancelItem(row)"
                        />
                    </td>
                </template>
            </MasterDataTable>

            <MasterDataTable
                v-if="activeView === 'bom'"
                :rows="filteredBom"
                empty-text="No recipe master data found."
            >
                <template #head>
                    <th
                        class="w-12 px-4 py-3 text-center text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        #
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Recipe Code
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Target Product
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Primary Components
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Status
                    </th>
                    <th
                        class="w-16 px-4 py-3 text-center text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Action
                    </th>
                </template>
                <template #row="{ row, index }">
                    <td
                        class="px-4 py-3 text-center text-[10px] text-slate-400"
                    >
                        {{ String(index + 1).padStart(2, '0') }}
                    </td>
                    <td
                        class="px-4 py-3 font-mono text-xs font-bold text-[#007882]"
                    >
                        {{ row.code }}
                    </td>
                    <td class="px-4 py-3 text-sm font-bold text-slate-700">
                        {{ row.targetProduct }}
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-500 italic">
                        {{ row.components }}
                    </td>
                    <td class="px-4 py-3">
                        <span
                            class="rounded border px-2 py-0.5 text-[10px] font-bold"
                            :class="statusClass(row.status)"
                        >
                            {{ statusLabel(row.status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <ApprovalActionMenu
                            :status="row.status"
                            @view="viewBom(row)"
                            @approve="approveBom(row)"
                            @reject="rejectBom(row)"
                            @cancel="cancelBom(row)"
                        />
                    </td>
                </template>
            </MasterDataTable>

            <MasterDataTable
                v-if="activeView === 'units'"
                :rows="filteredUnits"
                empty-text="No unit and package data found."
            >
                <template #head>
                    <th
                        class="w-12 px-4 py-3 text-center text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        #
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Code
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Unit/Package Name
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Category
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Type
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Ratio (Qty)
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Status
                    </th>
                    <th
                        class="w-16 px-4 py-3 text-center text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Action
                    </th>
                </template>
                <template #row="{ row, index }">
                    <td
                        class="px-4 py-3 text-center text-[10px] text-slate-400"
                    >
                        {{ String(index + 1).padStart(2, '0') }}
                    </td>
                    <td
                        class="px-4 py-3 font-mono text-xs font-bold text-[#007882]"
                    >
                        {{ row.code }}
                    </td>
                    <td class="px-4 py-3 text-sm font-bold text-slate-700">
                        {{ row.name }}
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-500">
                        {{ row.category }}
                    </td>
                    <td class="px-4 py-3">
                        <span
                            class="rounded border border-slate-200 bg-slate-100 px-2 py-0.5 text-[10px] text-slate-600"
                        >
                            {{ row.type }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-xs font-bold text-amber-700">
                        {{ row.ratio }}
                    </td>
                    <td class="px-4 py-3">
                        <span
                            class="rounded border px-2 py-0.5 text-[10px] font-bold"
                            :class="statusClass(row.status)"
                        >
                            {{ statusLabel(row.status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <ApprovalActionMenu
                            :status="row.status"
                            @view="viewUnit(row)"
                            @approve="approveUnit(row)"
                            @reject="rejectUnit(row)"
                            @cancel="cancelUnit(row)"
                        />
                    </td>
                </template>
            </MasterDataTable>
        </div>

        <Sheet :open="panelOpen" @update:open="(open) => !open && closePanel()">
            <SheetContent class="w-full gap-0 p-0 sm:max-w-[450px]">
                <SheetHeader class="bg-[#2A4858] p-6 text-white">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <SheetTitle class="text-lg font-bold text-white">
                                {{ panelTitle }}
                            </SheetTitle>
                            <p
                                class="mt-1 text-[10px] tracking-widest text-white/50 uppercase"
                            >
                                {{ panelSubtitle }}
                            </p>
                        </div>
                        <Button
                            variant="ghost"
                            size="icon"
                            class="size-7 rounded-full bg-white/10 text-white hover:bg-white/20 hover:text-white"
                            @click="closePanel"
                        >
                            <X class="size-4" />
                            <span class="sr-only">Close panel</span>
                        </Button>
                    </div>
                </SheetHeader>

                <div class="flex-1 space-y-4 overflow-y-auto p-6">
                    <div
                        v-if="selectedRecord"
                        class="space-y-3 rounded-xl bg-slate-50 p-4"
                    >
                        <label class="block">
                            <span
                                class="text-[10px] font-bold text-slate-400 uppercase"
                            >
                                Code / Identifier
                            </span>
                            <Input
                                :model-value="selectedRecord?.code ?? ''"
                                class="mt-1 font-mono text-sm focus-visible:ring-[#007882]"
                                placeholder="Ex: CS-12"
                            />
                        </label>

                        <label class="block">
                            <span
                                class="text-[10px] font-bold text-slate-400 uppercase"
                            >
                                Name / Description
                            </span>
                            <Input
                                :model-value="panelName"
                                class="mt-1 text-sm focus-visible:ring-[#007882]"
                                placeholder="Enter name"
                            />
                        </label>

                        <div
                            v-if="showRatioFields"
                            class="mt-2 space-y-3 border-t border-slate-200 pt-2"
                        >
                            <label class="block">
                                <span
                                    class="text-[10px] font-bold text-slate-400 uppercase"
                                >
                                    UoM Category
                                </span>
                                <select
                                    class="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus:border-[#007882]"
                                >
                                    <option>Unit / Count</option>
                                    <option>Volume</option>
                                    <option>Weight</option>
                                </select>
                            </label>
                            <label class="block">
                                <span
                                    class="text-[10px] font-bold text-slate-400 uppercase"
                                >
                                    Ratio (Quantity Key)
                                </span>
                                <div class="relative mt-1">
                                    <Input
                                        type="number"
                                        step="0.00001"
                                        class="pr-20 font-bold text-[#007882] focus-visible:ring-[#007882]"
                                        :model-value="
                                            selectedRecord &&
                                            'ratio' in selectedRecord
                                                ? selectedRecord.ratio
                                                : ''
                                        "
                                        placeholder="12.00000"
                                    />
                                    <span
                                        class="absolute top-1/2 right-3 -translate-y-1/2 text-[10px] font-bold text-slate-400"
                                    >
                                        RATIO
                                    </span>
                                </div>
                                <p
                                    class="mt-1 text-[10px] leading-tight text-slate-400 italic"
                                >
                                    Use 12.00000 for a pack of 12, or 1000.00000
                                    for ml-to-liter style conversion.
                                </p>
                            </label>
                        </div>

                        <label class="block">
                            <span
                                class="text-[10px] font-bold text-slate-400 uppercase"
                            >
                                Status
                            </span>
                            <select
                                class="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus:border-[#007882]"
                            >
                                <option>Active</option>
                                <option>Inactive</option>
                            </select>
                        </label>
                    </div>

                    <div
                        v-else-if="panelKind === 'items'"
                        class="space-y-3 rounded-xl bg-slate-50 p-4"
                    >
                        <label class="block">
                            <span
                                class="text-[10px] font-bold text-slate-400 uppercase"
                            >
                                Item Code
                            </span>
                            <Input
                                v-model="itemForm.code"
                                class="mt-1 font-mono text-sm focus-visible:ring-[#007882]"
                                placeholder="Ex: RM-RICE"
                            />
                        </label>

                        <label class="block">
                            <span
                                class="text-[10px] font-bold text-slate-400 uppercase"
                            >
                                Item Name
                            </span>
                            <Input
                                v-model="itemForm.name"
                                class="mt-1 text-sm focus-visible:ring-[#007882]"
                                placeholder="Enter item name"
                            />
                            <p
                                v-if="itemForm.errors.name"
                                class="mt-1 text-[10px] font-bold text-rose-600"
                            >
                                {{ itemForm.errors.name }}
                            </p>
                        </label>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <label class="block">
                                <span
                                    class="text-[10px] font-bold text-slate-400 uppercase"
                                >
                                    Type
                                </span>
                                <select
                                    v-model="itemForm.item_type"
                                    class="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus:border-[#007882]"
                                >
                                    <option value="raw_material">
                                        Raw Material
                                    </option>
                                    <option value="ingredient">
                                        Ingredient
                                    </option>
                                    <option value="drink">Drink</option>
                                    <option value="finished_product">
                                        Finished Product
                                    </option>
                                    <option value="packaging">Packaging</option>
                                    <option value="service_material">
                                        Service Material
                                    </option>
                                    <option value="other">Other</option>
                                </select>
                            </label>

                            <label class="block">
                                <span
                                    class="text-[10px] font-bold text-slate-400 uppercase"
                                >
                                    Unit
                                </span>
                                <select
                                    v-model="itemForm.unit_id"
                                    class="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus:border-[#007882]"
                                >
                                    <option value="">Select unit</option>
                                    <option
                                        v-for="unit in props.unitOptions"
                                        :key="unit.id"
                                        :value="unit.id"
                                    >
                                        {{ unit.code }} - {{ unit.name }}
                                    </option>
                                </select>
                            </label>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <label class="block">
                                <span
                                    class="text-[10px] font-bold text-slate-400 uppercase"
                                >
                                    Cost
                                </span>
                                <Input
                                    v-model="itemForm.cost"
                                    type="number"
                                    step="0.0001"
                                    class="mt-1 font-bold text-[#007882] focus-visible:ring-[#007882]"
                                />
                            </label>

                            <label class="block">
                                <span
                                    class="text-[10px] font-bold text-slate-400 uppercase"
                                >
                                    Minimum Stock
                                </span>
                                <Input
                                    v-model="itemForm.minimum_stock_qty"
                                    type="number"
                                    step="0.0001"
                                    class="mt-1 font-bold text-[#007882] focus-visible:ring-[#007882]"
                                />
                            </label>
                        </div>

                        <label class="block">
                            <span
                                class="text-[10px] font-bold text-slate-400 uppercase"
                            >
                                Branch
                            </span>
                            <select
                                v-model="itemForm.branch_id"
                                class="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus:border-[#007882]"
                            >
                                <option value="">Default branch</option>
                                <option
                                    v-for="branch in props.branchOptions"
                                    :key="branch.id"
                                    :value="branch.id"
                                >
                                    {{ branch.name }}
                                </option>
                            </select>
                        </label>

                        <label class="flex items-center gap-2">
                            <input
                                v-model="itemForm.is_stockable"
                                type="checkbox"
                                class="size-4 rounded border-slate-300 text-[#007882]"
                            />
                            <span class="text-xs font-bold text-slate-500">
                                Track this item in inventory
                            </span>
                        </label>
                    </div>

                    <div
                        v-else-if="panelKind === 'bom'"
                        class="space-y-4 rounded-xl bg-slate-50 p-4"
                    >
                        <div class="grid gap-3 sm:grid-cols-2">
                            <label class="block">
                                <span
                                    class="text-[10px] font-bold text-slate-400 uppercase"
                                >
                                    BOM Code
                                </span>
                                <Input
                                    v-model="bomForm.bom_no"
                                    class="mt-1 font-mono text-sm focus-visible:ring-[#007882]"
                                    placeholder="Auto if blank"
                                />
                            </label>

                            <label class="block">
                                <span
                                    class="text-[10px] font-bold text-slate-400 uppercase"
                                >
                                    Status
                                </span>
                                <select
                                    v-model="bomForm.status"
                                    class="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus:border-[#007882]"
                                >
                                    <option value="draft">Draft</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </label>
                        </div>

                        <label class="block">
                            <span
                                class="text-[10px] font-bold text-slate-400 uppercase"
                            >
                                BOM Name
                            </span>
                            <Input
                                v-model="bomForm.name"
                                class="mt-1 text-sm focus-visible:ring-[#007882]"
                                placeholder="Ex: Fried Rice Standard BOM"
                            />
                        </label>

                        <label class="block">
                            <span
                                class="text-[10px] font-bold text-slate-400 uppercase"
                            >
                                Output Item
                            </span>
                            <select
                                v-model="bomForm.output_item_id"
                                class="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus:border-[#007882]"
                            >
                                <option value="">Select produced item</option>
                                <option
                                    v-for="item in props.itemOptions"
                                    :key="item.id"
                                    :value="item.id"
                                >
                                    {{ item.code }} - {{ item.name }}
                                </option>
                            </select>
                        </label>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <label class="block">
                                <span
                                    class="text-[10px] font-bold text-slate-400 uppercase"
                                >
                                    Output Quantity
                                </span>
                                <Input
                                    v-model="bomForm.output_quantity"
                                    type="number"
                                    step="0.0001"
                                    class="mt-1 font-bold text-[#007882] focus-visible:ring-[#007882]"
                                />
                            </label>

                            <label class="block">
                                <span
                                    class="text-[10px] font-bold text-slate-400 uppercase"
                                >
                                    Branch
                                </span>
                                <select
                                    v-model="bomForm.branch_id"
                                    class="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus:border-[#007882]"
                                >
                                    <option value="">Default branch</option>
                                    <option
                                        v-for="branch in props.branchOptions"
                                        :key="branch.id"
                                        :value="branch.id"
                                    >
                                        {{ branch.name }}
                                    </option>
                                </select>
                            </label>
                        </div>

                        <div class="space-y-3 border-t border-slate-200 pt-3">
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-[10px] font-bold text-slate-400 uppercase"
                                >
                                    BOM Lines
                                </span>
                                <Button
                                    type="button"
                                    variant="outline"
                                    class="h-8 rounded-lg text-xs font-bold"
                                    @click="addBomLine"
                                >
                                    <Plus class="size-4" />
                                    Line
                                </Button>
                            </div>

                            <div
                                v-for="(line, index) in bomForm.lines"
                                :key="index"
                                class="space-y-2 rounded-lg border border-slate-200 bg-white p-3"
                            >
                                <div class="flex items-center gap-2">
                                    <select
                                        v-model="line.component_item_id"
                                        class="h-9 min-w-0 flex-1 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus:border-[#007882]"
                                        @change="syncLineUnit(index)"
                                    >
                                        <option value="">
                                            Select component
                                        </option>
                                        <option
                                            v-for="item in props.itemOptions"
                                            :key="item.id"
                                            :value="item.id"
                                        >
                                            {{ item.code }} - {{ item.name }}
                                        </option>
                                    </select>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="icon"
                                        class="size-9 text-slate-400 hover:text-rose-600"
                                        @click="removeBomLine(index)"
                                    >
                                        <Trash2 class="size-4" />
                                        <span class="sr-only">Remove line</span>
                                    </Button>
                                </div>

                                <div class="grid grid-cols-3 gap-2">
                                    <Input
                                        v-model="line.quantity"
                                        type="number"
                                        step="0.0001"
                                        placeholder="Qty"
                                        class="text-xs"
                                    />
                                    <select
                                        v-model="line.unit_id"
                                        class="h-9 w-full rounded-md border border-input bg-transparent px-2 py-1 text-xs shadow-xs outline-none focus:border-[#007882]"
                                    >
                                        <option value="">Unit</option>
                                        <option
                                            v-for="unit in props.unitOptions"
                                            :key="unit.id"
                                            :value="unit.id"
                                        >
                                            {{ unit.code }}
                                        </option>
                                    </select>
                                    <Input
                                        v-model="line.wastage_percent"
                                        type="number"
                                        step="0.0001"
                                        placeholder="Waste %"
                                        class="text-xs"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <SheetFooter class="flex-row gap-3 border-t p-6">
                    <Button
                        variant="outline"
                        class="flex-1 rounded-lg text-xs font-bold text-slate-500"
                        @click="closePanel"
                    >
                        Cancel
                    </Button>
                    <Button
                        class="flex-1 rounded-lg bg-[#007882] text-xs font-bold text-white hover:bg-[#006871]"
                        :disabled="itemForm.processing || bomForm.processing"
                        @click="submitPanel"
                    >
                        <PackagePlus class="size-4" />
                        Save Changes
                    </Button>
                </SheetFooter>
            </SheetContent>
        </Sheet>
    </AppLayout>
</template>
