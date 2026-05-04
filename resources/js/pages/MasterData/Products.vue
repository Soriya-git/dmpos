<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    Box,
    FlaskConical,
    PackagePlus,
    Plus,
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
} = useItemsBomMaster();

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
                        @click="openPanel()"
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
                    <div class="space-y-3 rounded-xl bg-slate-50 p-4">
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
                        @click="closePanel"
                    >
                        <PackagePlus class="size-4" />
                        Save Changes
                    </Button>
                </SheetFooter>
            </SheetContent>
        </Sheet>
    </AppLayout>
</template>
