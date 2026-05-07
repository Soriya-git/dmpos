<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    Layers3,
    Plus,
    Save,
    Search,
    Tags,
    Utensils,
    X,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
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
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

type ApprovalStatus =
    | 'draft'
    | 'pending'
    | 'approved'
    | 'rejected'
    | 'cancelled';

type MenuView = 'menus' | 'categories' | 'prices';

type MenuRecord = {
    id: number;
    code: string;
    name: string;
    category: string;
    branch: string;
    type: 'product' | 'service';
    basePrice: string;
    defaultPrice: string;
    description: string | null;
    available: boolean;
    status: ApprovalStatus;
};

type CategoryRecord = {
    id: number;
    code: string;
    name: string;
    branch: string;
    description: string | null;
    menusCount: number;
    status: ApprovalStatus;
};

type PriceRecord = {
    id: number;
    code: string;
    name: string;
    menu: string;
    branch: string;
    price: string;
    effectiveFrom: string | null;
    effectiveTo: string | null;
    isDefault: boolean;
    status: ApprovalStatus;
};

type PanelRecord = MenuRecord | CategoryRecord | PriceRecord;

const props = defineProps<{
    menus: MenuRecord[];
    categories: CategoryRecord[];
    prices: PriceRecord[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Master Data' },
    { title: 'Menu', href: '/master-data/menu' },
];

const activeView = ref<MenuView>('menus');
const search = ref('');
const panelOpen = ref(false);
const panelKind = ref<MenuView>('menus');
const selectedRecord = ref<PanelRecord | null>(null);
const menus = ref<MenuRecord[]>([...props.menus]);
const categories = ref<CategoryRecord[]>([...props.categories]);
const prices = ref<PriceRecord[]>([...props.prices]);

const tabs: {
    key: MenuView;
    label: string;
    icon: typeof Utensils;
}[] = [
    { key: 'menus', label: 'Menu Registry', icon: Utensils },
    { key: 'categories', label: 'Categories', icon: Tags },
    { key: 'prices', label: 'Price List', icon: Layers3 },
];

const normalizedSearch = computed(() => search.value.trim().toLowerCase());
const filteredMenus = computed(() => filterRows(menus.value));
const filteredCategories = computed(() => filterRows(categories.value));
const filteredPrices = computed(() => filterRows(prices.value));

const panelTitle = computed(() =>
    selectedRecord.value ? `Edit ${panelLabel()}` : `New ${panelLabel()}`,
);

const panelSubtitle = computed(() => {
    const subtitles: Record<MenuView, string> = {
        menus: 'Menu item and service master data',
        categories: 'Menu category and grouping master data',
        prices: 'Branch menu price master data',
    };

    return subtitles[panelKind.value];
});

function panelLabel() {
    const labels: Record<MenuView, string> = {
        menus: 'Menu',
        categories: 'Category',
        prices: 'Price',
    };

    return labels[panelKind.value];
}

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

function switchView(view: MenuView) {
    activeView.value = view;
    panelKind.value = view;
}

function openPanel(
    kind: MenuView = activeView.value,
    record: PanelRecord | null = null,
) {
    panelKind.value = kind;
    selectedRecord.value = record;
    panelOpen.value = true;
}

function closePanel() {
    panelOpen.value = false;
    selectedRecord.value = null;
}

function statusLabel(status: ApprovalStatus) {
    return status === 'cancelled'
        ? 'Inactive'
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

function setMenuStatus(record: MenuRecord, status: ApprovalStatus) {
    record.status = status;
}

function setCategoryStatus(record: CategoryRecord, status: ApprovalStatus) {
    record.status = status;
}

function setPriceStatus(record: PriceRecord, status: ApprovalStatus) {
    record.status = status;
}
</script>

<template>
    <Head title="Menu" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 lg:p-6">
            <div
                class="flex flex-col justify-between gap-4 lg:flex-row lg:items-center"
            >
                <div>
                    <h1
                        class="text-2xl font-semibold tracking-tight text-[#2A4858]"
                    >
                        Menu
                    </h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Menu items, categories, default prices, and
                        availability.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <div class="relative">
                        <Search
                            class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"
                        />
                        <Input
                            v-model="search"
                            placeholder="Search menu data..."
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
                v-if="activeView === 'menus'"
                :rows="filteredMenus"
                empty-text="No menu data found."
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
                        Menu Code
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Menu Name
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Category
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Branch
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Type
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Price
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
                    <td class="px-4 py-3 text-xs text-slate-500">
                        {{ row.branch }}
                    </td>
                    <td class="px-4 py-3">
                        <span
                            class="rounded border border-slate-200 bg-slate-100 px-2 py-0.5 text-[10px] text-slate-600"
                        >
                            {{ row.type }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-xs font-bold text-amber-700">
                        {{ row.defaultPrice }}
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
                            @view="openPanel('menus', row)"
                            @approve="setMenuStatus(row, 'approved')"
                            @reject="setMenuStatus(row, 'rejected')"
                            @cancel="setMenuStatus(row, 'cancelled')"
                        />
                    </td>
                </template>
            </MasterDataTable>

            <MasterDataTable
                v-if="activeView === 'categories'"
                :rows="filteredCategories"
                empty-text="No menu category data found."
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
                        Category Code
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Category Name
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Branch
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Menus
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
                        {{ row.branch }}
                    </td>
                    <td class="px-4 py-3 text-xs font-bold text-amber-700">
                        {{ row.menusCount }}
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
                            @view="openPanel('categories', row)"
                            @approve="setCategoryStatus(row, 'approved')"
                            @reject="setCategoryStatus(row, 'rejected')"
                            @cancel="setCategoryStatus(row, 'cancelled')"
                        />
                    </td>
                </template>
            </MasterDataTable>

            <MasterDataTable
                v-if="activeView === 'prices'"
                :rows="filteredPrices"
                empty-text="No menu price data found."
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
                        Price Code
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Price Name
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Menu
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Branch
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Price
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Default
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
                        {{ row.menu }}
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-500">
                        {{ row.branch }}
                    </td>
                    <td class="px-4 py-3 text-xs font-bold text-amber-700">
                        {{ row.price }}
                    </td>
                    <td class="px-4 py-3">
                        <span
                            class="rounded bg-blue-50 px-2 py-0.5 text-[10px] font-bold text-blue-600"
                        >
                            {{ row.isDefault ? 'Default' : 'Custom' }}
                        </span>
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
                            @view="openPanel('prices', row)"
                            @approve="setPriceStatus(row, 'approved')"
                            @reject="setPriceStatus(row, 'rejected')"
                            @cancel="setPriceStatus(row, 'cancelled')"
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
                                placeholder="Ex: M-FRIED-RICE"
                            />
                        </label>

                        <label class="block">
                            <span
                                class="text-[10px] font-bold text-slate-400 uppercase"
                            >
                                Name / Description
                            </span>
                            <Input
                                :model-value="selectedRecord?.name ?? ''"
                                class="mt-1 text-sm focus-visible:ring-[#007882]"
                                placeholder="Enter name"
                            />
                        </label>

                        <div
                            class="mt-2 space-y-3 border-t border-slate-200 pt-2"
                        >
                            <label v-if="panelKind === 'menus'" class="block">
                                <span
                                    class="text-[10px] font-bold text-slate-400 uppercase"
                                >
                                    Menu Type
                                </span>
                                <select
                                    class="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus:border-[#007882]"
                                >
                                    <option>Product</option>
                                    <option>Service</option>
                                </select>
                            </label>
                            <label v-if="panelKind === 'menus'" class="block">
                                <span
                                    class="text-[10px] font-bold text-slate-400 uppercase"
                                >
                                    Category
                                </span>
                                <select
                                    class="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus:border-[#007882]"
                                >
                                    <option
                                        v-for="category in categories"
                                        :key="category.id"
                                    >
                                        {{ category.name }}
                                    </option>
                                </select>
                            </label>
                            <label v-if="panelKind === 'prices'" class="block">
                                <span
                                    class="text-[10px] font-bold text-slate-400 uppercase"
                                >
                                    Menu
                                </span>
                                <select
                                    class="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus:border-[#007882]"
                                >
                                    <option
                                        v-for="menu in menus"
                                        :key="menu.id"
                                    >
                                        {{ menu.name }}
                                    </option>
                                </select>
                            </label>
                            <label
                                v-if="panelKind !== 'categories'"
                                class="block"
                            >
                                <span
                                    class="text-[10px] font-bold text-slate-400 uppercase"
                                >
                                    Price
                                </span>
                                <Input
                                    :model-value="
                                        selectedRecord &&
                                        'price' in selectedRecord
                                            ? selectedRecord.price
                                            : selectedRecord &&
                                                'defaultPrice' in selectedRecord
                                              ? selectedRecord.defaultPrice
                                              : ''
                                    "
                                    class="mt-1 font-bold text-[#007882] focus-visible:ring-[#007882]"
                                    placeholder="0.00"
                                />
                            </label>
                            <label class="block">
                                <span
                                    class="text-[10px] font-bold text-slate-400 uppercase"
                                >
                                    Branch
                                </span>
                                <Input
                                    :model-value="
                                        selectedRecord &&
                                        'branch' in selectedRecord
                                            ? selectedRecord.branch
                                            : ''
                                    "
                                    class="mt-1 text-sm focus-visible:ring-[#007882]"
                                    placeholder="Branch"
                                />
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
                        <Save class="size-4" />
                        Save Changes
                    </Button>
                </SheetFooter>
            </SheetContent>
        </Sheet>
    </AppLayout>
</template>
