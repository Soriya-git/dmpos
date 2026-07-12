<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    Layers3,
    Plus,
    Save,
    Search,
    Tags,
    Utensils,
    X,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
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
    nameKh: string | null;
    nameOther: string | null;
    nickname: string | null;
    category: string;
    branch: string;
    branches: string[];
    branchIds: number[];
    branchNicknames: Record<number, string | null>;
    type: 'product' | 'service';
    item: string | null;
    bom: string | null;
    printRoute: 'none' | 'stock' | 'kitchen' | 'bar' | 'cashier' | 'custom';
    printerId: number | null;
    printer: string | null;
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

type OptionRecord = {
    id: number;
    name: string;
    code: string | null;
};

type BomOption = OptionRecord & {
    outputItemId: number | null;
    outputItemName: string | null;
};

type PrinterOption = OptionRecord & {
    role: string;
    connectionType: string;
    ipAddress: string | null;
    port: number | null;
};

const props = defineProps<{
    menus: MenuRecord[];
    categories: CategoryRecord[];
    prices: PriceRecord[];
    itemOptions: OptionRecord[];
    bomOptions: BomOption[];
    branchOptions: OptionRecord[];
    printerOptions: PrinterOption[];
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
const sourceMode = ref<'item' | 'bom'>('item');

const menuForm = useForm({
    name: '',
    name_kh: '',
    name_other: '',
    nickname: '',
    code: '',
    menu_category_id: '',
    branch_ids: [] as number[],
    branch_nicknames: {} as Record<number, string>,
    menu_type: 'product',
    base_price: '0',
    item_id: '',
    bom_header_id: '',
    printer_id: '',
    print_route: 'kitchen',
    description: '',
    is_available: true,
});

const categoryForm = useForm({
    name: '',
    code: '',
    branch_id: '',
    description: '',
});

const priceForm = useForm({
    menu_id: '',
    branch_id: '',
    price_name: 'Default Price',
    price: '0',
    is_default: true,
});

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

watch(
    () => props.menus,
    (nextMenus) => {
        menus.value = [...nextMenus];
    },
);

watch(
    () => props.categories,
    (nextCategories) => {
        categories.value = [...nextCategories];
    },
);

watch(
    () => props.prices,
    (nextPrices) => {
        prices.value = [...nextPrices];
    },
);

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
        JSON.stringify(row).toLowerCase().includes(normalizedSearch.value),
    );
}

function branchNicknameLabel(row: MenuRecord): string {
    const nicknames = Object.values(row.branchNicknames).filter(
        (nickname): nickname is string => Boolean(nickname?.trim()),
    );

    return nicknames.join(', ') || row.nickname || '-';
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
    resetForms(record);
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

watch(
    () => menuForm.bom_header_id,
    (bomId) => {
        if (!bomId) {
            return;
        }

        const bom = props.bomOptions.find(
            (option) => option.id === Number(bomId),
        );

        if (bom?.outputItemId) {
            menuForm.item_id = String(bom.outputItemId);
        }
    },
);

function resetForms(record: PanelRecord | null) {
    menuForm.reset();
    categoryForm.reset();
    priceForm.reset();
    menuForm.clearErrors();
    categoryForm.clearErrors();
    priceForm.clearErrors();
    sourceMode.value = 'item';

    if (!record) {
        return;
    }

    if (panelKind.value === 'menus' && 'basePrice' in record) {
        menuForm.name = record.name;
        menuForm.name_kh = record.nameKh ?? '';
        menuForm.name_other = record.nameOther ?? '';
        menuForm.nickname = record.nickname ?? '';
        menuForm.code = record.code;
        menuForm.menu_type = record.type;
        menuForm.base_price = record.basePrice;
        menuForm.description = record.description ?? '';
        menuForm.is_available = record.available;
        menuForm.print_route = record.printRoute;
        menuForm.printer_id = record.printerId ? String(record.printerId) : '';
        menuForm.branch_ids = [...record.branchIds];
        menuForm.branch_nicknames = Object.fromEntries(
            Object.entries(record.branchNicknames).map(([id, nickname]) => [id, nickname ?? '']),
        );
    }

    if (panelKind.value === 'categories' && 'menusCount' in record) {
        categoryForm.name = record.name;
        categoryForm.code = record.code;
        categoryForm.description = record.description ?? '';
    }

    if (panelKind.value === 'prices' && 'price' in record) {
        priceForm.price_name = record.name;
        priceForm.price = record.price;
        priceForm.is_default = record.isDefault;
    }
}

function submitPanel() {
    if (selectedRecord.value) {
        if (
            panelKind.value === 'menus' &&
            'basePrice' in selectedRecord.value
        ) {
            router.patch(
                `/master-data/menu/menus/${selectedRecord.value.id}`,
                {
                    name_kh: menuForm.name_kh,
                    name_other: menuForm.name_other,
                    nickname: menuForm.nickname,
                    branch_ids: menuForm.branch_ids,
                    branch_nicknames: menuForm.branch_nicknames,
                    printer_id: menuForm.printer_id || null,
                    print_route: menuForm.print_route,
                },
                {
                    preserveScroll: true,
                    onSuccess: closePanel,
                },
            );
        } else {
            closePanel();
        }

        return;
    }

    if (panelKind.value === 'menus') {
        if (sourceMode.value === 'item') {
            menuForm.bom_header_id = '';
        }

        menuForm.post('/master-data/menu/menus', {
            preserveScroll: true,
            onSuccess: closePanel,
        });

        return;
    }

    if (panelKind.value === 'categories') {
        categoryForm.post('/master-data/menu/categories', {
            preserveScroll: true,
            onSuccess: closePanel,
        });

        return;
    }

    priceForm.post('/master-data/menu/prices', {
        preserveScroll: true,
        onSuccess: closePanel,
    });
}
</script>

<template>
    <Head title="Menu" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #actions>
            <Button
                class="h-9 rounded-lg bg-[#007882] px-4 text-xs font-bold text-white hover:bg-[#006871]"
                @click="openPanel()"
            >
                <Plus class="size-4" />
                New
            </Button>
        </template>

        <div
            class="flex h-[calc(100dvh-4rem)] w-full [scrollbar-gutter:stable] flex-col gap-6 overflow-y-scroll bg-[#f8fafc] p-4 text-slate-800 md:h-[calc(100dvh-5rem)] md:p-6 xl:p-8 2xl:p-10"
        >
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

            <div class="relative w-full max-w-sm">
                <Search
                    class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"
                />
                <Input
                    v-model="search"
                    placeholder="Search menu data..."
                    class="h-9 rounded-lg border-slate-200 bg-white pl-9 text-xs focus-visible:ring-[#007882]"
                />
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
                        Menu Name
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Nickname
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
                        Source
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Print To
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
                    <td class="px-4 py-3">
                        <div class="text-sm font-bold text-slate-700">
                            {{ row.name }}
                        </div>
                        <div class="mt-0.5 font-mono text-[10px] font-bold text-[#007882]">
                            {{ row.code }}
                        </div>
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-500">
                        {{ branchNicknameLabel(row) }}
                    </td>
                    <td class="px-4 py-3">
                        <span
                            class="rounded bg-blue-50 px-2 py-0.5 text-[10px] font-bold text-blue-600"
                        >
                            {{ row.category }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex flex-wrap items-center gap-1">
                            <span
                                v-for="branch in row.branches.slice(0, 2)"
                                :key="branch"
                                class="rounded bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-600"
                            >
                                {{ branch }}
                            </span>
                            <span
                                v-if="row.branches.length > 2"
                                class="rounded bg-[#23AA8F]/10 px-2 py-0.5 text-[10px] font-bold text-[#007882]"
                            >
                                2+
                            </span>
                            <span
                                v-if="row.branches.length === 0"
                                class="text-xs text-slate-400"
                            >
                                No branches
                            </span>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <span
                            class="rounded border border-slate-200 bg-slate-100 px-2 py-0.5 text-[10px] text-slate-600"
                        >
                            {{ row.type }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-500">
                        {{ row.bom || row.item || '-' }}
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex flex-col gap-1">
                            <span
                                class="w-fit rounded bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700"
                            >
                                {{ row.printRoute }}
                            </span>
                            <span class="text-[10px] text-slate-400">
                                {{ row.printer || 'Default printer' }}
                            </span>
                        </div>
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
                    <div class="space-y-3 rounded-lg bg-slate-50 p-4">
                        <label class="block">
                            <span
                                class="text-[10px] font-bold text-slate-400 uppercase"
                            >
                                Code / Identifier
                            </span>
                            <Input
                                v-if="panelKind === 'menus'"
                                v-model="menuForm.code"
                                :disabled="!!selectedRecord"
                                class="mt-1 font-mono text-sm focus-visible:ring-[#007882]"
                                placeholder="Ex: M-FRIED-RICE"
                            />
                            <Input
                                v-else-if="panelKind === 'categories'"
                                v-model="categoryForm.code"
                                :disabled="!!selectedRecord"
                                class="mt-1 font-mono text-sm focus-visible:ring-[#007882]"
                                placeholder="Ex: CAT-FOOD"
                            />
                            <Input
                                v-else
                                v-model="priceForm.price_name"
                                :disabled="!!selectedRecord"
                                class="mt-1 font-mono text-sm focus-visible:ring-[#007882]"
                                placeholder="Default Price"
                            />
                        </label>

                        <label class="block">
                            <span
                                class="text-[10px] font-bold text-slate-400 uppercase"
                            >
                                Name / Description
                            </span>
                            <Input
                                v-if="panelKind === 'menus'"
                                v-model="menuForm.name"
                                :disabled="!!selectedRecord"
                                class="mt-1 text-sm focus-visible:ring-[#007882]"
                                placeholder="Enter menu name"
                            />
                            <Input
                                v-else-if="panelKind === 'categories'"
                                v-model="categoryForm.name"
                                :disabled="!!selectedRecord"
                                class="mt-1 text-sm focus-visible:ring-[#007882]"
                                placeholder="Enter category name"
                            />
                            <select
                                v-else
                                v-model="priceForm.menu_id"
                                :disabled="!!selectedRecord"
                                class="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus:border-[#007882]"
                            >
                                <option value="">Select menu</option>
                                <option
                                    v-for="menu in menus"
                                    :key="menu.id"
                                    :value="menu.id"
                                >
                                    {{ menu.name }}
                                </option>
                            </select>
                            <p
                                v-if="
                                    menuForm.errors.name ||
                                    categoryForm.errors.name ||
                                    priceForm.errors.menu_id
                                "
                                class="mt-1 text-[10px] font-bold text-rose-600"
                            >
                                {{
                                    menuForm.errors.name ||
                                    categoryForm.errors.name ||
                                    priceForm.errors.menu_id
                                }}
                            </p>
                        </label>

                        <template v-if="panelKind === 'menus'">
                            <label class="block">
                                <span
                                    class="text-[10px] font-bold text-slate-400 uppercase"
                                    >Khmer Name</span
                                >
                                <Input
                                    v-model="menuForm.name_kh"
                                    class="mt-1 text-sm focus-visible:ring-[#007882]"
                                    placeholder="Enter Khmer name"
                                />
                            </label>
                            <label class="block">
                                <span
                                    class="text-[10px] font-bold text-slate-400 uppercase"
                                    >Other Name</span
                                >
                                <Input
                                    v-model="menuForm.name_other"
                                    class="mt-1 text-sm focus-visible:ring-[#007882]"
                                    placeholder="Enter another foreign name"
                                />
                            </label>
<<<<<<< HEAD
=======
                            <label class="block">
                                <span
                                    class="text-[10px] font-bold text-slate-400 uppercase"
                                    >Nickname</span
                                >
                                <Input
                                    v-model="menuForm.nickname"
                                    class="mt-1 text-sm focus-visible:ring-[#007882]"
                                    placeholder="Enter a quick-search nickname"
                                />
                            </label>
>>>>>>> 5261e5d225e0dd5e882aec18358b989cb2251b46
                        </template>

                        <label v-if="panelKind === 'menus'" class="block">
                            <span
                                class="text-[10px] font-bold text-slate-400 uppercase"
                            >
                                Create From
                            </span>
                            <div
                                class="mt-1 grid grid-cols-2 overflow-hidden rounded-md border border-slate-200 bg-white text-xs font-bold"
                            >
                                <button
                                    type="button"
                                    class="px-3 py-2"
                                    :class="
                                        sourceMode === 'item'
                                            ? 'bg-[#007882] text-white'
                                            : 'text-slate-500'
                                    "
                                    @click="sourceMode = 'item'"
                                >
                                    Item
                                </button>
                                <button
                                    type="button"
                                    class="px-3 py-2"
                                    :class="
                                        sourceMode === 'bom'
                                            ? 'bg-[#007882] text-white'
                                            : 'text-slate-500'
                                    "
                                    @click="sourceMode = 'bom'"
                                >
                                    BOM
                                </button>
                            </div>
                        </label>

                        <label
                            v-if="
                                panelKind === 'menus' && sourceMode === 'item'
                            "
                            class="block"
                        >
                            <span
                                class="text-[10px] font-bold text-slate-400 uppercase"
                            >
                                Inventory Item
                            </span>
                            <select
                                v-model="menuForm.item_id"
                                :disabled="!!selectedRecord"
                                class="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus:border-[#007882]"
                            >
                                <option value="">
                                    No direct inventory item
                                </option>
                                <option
                                    v-for="item in props.itemOptions"
                                    :key="item.id"
                                    :value="item.id"
                                >
                                    {{ item.code }} - {{ item.name }}
                                </option>
                            </select>
                        </label>

                        <label
                            v-if="panelKind === 'menus' && sourceMode === 'bom'"
                            class="block"
                        >
                            <span
                                class="text-[10px] font-bold text-slate-400 uppercase"
                            >
                                BOM Header
                            </span>
                            <select
                                v-model="menuForm.bom_header_id"
                                :disabled="!!selectedRecord"
                                class="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus:border-[#007882]"
                            >
                                <option value="">Select BOM</option>
                                <option
                                    v-for="bom in props.bomOptions"
                                    :key="bom.id"
                                    :value="bom.id"
                                >
                                    {{ bom.code }} - {{ bom.name }}
                                </option>
                            </select>
                        </label>

                        <label
                            v-if="panelKind === 'menus' && sourceMode === 'bom'"
                            class="block"
                        >
                            <span
                                class="text-[10px] font-bold text-slate-400 uppercase"
                            >
                                Output Item
                            </span>
                            <select
                                v-model="menuForm.item_id"
                                disabled
                                class="mt-1 text-sm focus-visible:ring-[#007882]"
                            >
                                <option value="">
                                    Selected from BOM output
                                </option>
                                <option
                                    v-for="item in props.itemOptions"
                                    :key="item.id"
                                    :value="item.id"
                                >
                                    {{ item.code }} - {{ item.name }}
                                </option>
                            </select>
                        </label>

                        <div
                            v-if="panelKind === 'menus'"
                            class="grid gap-3 border-t border-slate-200 pt-3 sm:grid-cols-2"
                        >
                            <label class="block">
                                <span
                                    class="text-[10px] font-bold text-slate-400 uppercase"
                                >
                                    Print Route
                                </span>
                                <select
                                    v-model="menuForm.print_route"
                                    class="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus:border-[#007882]"
                                >
                                    <option value="none">No Print</option>
                                    <option value="stock">Stock</option>
                                    <option value="kitchen">Kitchen</option>
                                    <option value="bar">Bar</option>
                                    <option value="cashier">Cashier</option>
                                    <option value="custom">Custom</option>
                                </select>
                            </label>

                            <label class="block">
                                <span
                                    class="text-[10px] font-bold text-slate-400 uppercase"
                                >
                                    Printer
                                </span>
                                <select
                                    v-model="menuForm.printer_id"
                                    class="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus:border-[#007882]"
                                >
                                    <option value="">Default by route</option>
                                    <option
                                        v-for="printer in props.printerOptions"
                                        :key="printer.id"
                                        :value="printer.id"
                                    >
                                        {{ printer.name }} - {{ printer.role }}
                                        {{
                                            printer.ipAddress
                                                ? `(${printer.ipAddress}:${printer.port ?? 9100})`
                                                : ''
                                        }}
                                    </option>
                                </select>
                            </label>
                        </div>

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
                                    v-model="menuForm.menu_type"
                                    :disabled="!!selectedRecord"
                                    class="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus:border-[#007882]"
                                >
                                    <option value="product">Product</option>
                                    <option value="service">Service</option>
                                </select>
                            </label>
                            <label v-if="panelKind === 'menus'" class="block">
                                <span
                                    class="text-[10px] font-bold text-slate-400 uppercase"
                                >
                                    Category
                                </span>
                                <select
                                    v-model="menuForm.menu_category_id"
                                    :disabled="!!selectedRecord"
                                    class="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus:border-[#007882]"
                                >
                                    <option value="">Uncategorized</option>
                                    <option
                                        v-for="category in categories"
                                        :key="category.id"
                                        :value="category.id"
                                    >
                                        {{ category.name }}
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
                                    v-if="panelKind === 'menus'"
                                    v-model="menuForm.base_price"
                                    :disabled="!!selectedRecord"
                                    type="number"
                                    step="0.01"
                                    class="mt-1 font-bold text-[#007882] focus-visible:ring-[#007882]"
                                    placeholder="0.00"
                                />
                                <Input
                                    v-else
                                    v-model="priceForm.price"
                                    :disabled="!!selectedRecord"
                                    type="number"
                                    step="0.01"
                                    class="mt-1 font-bold text-[#007882] focus-visible:ring-[#007882]"
                                    placeholder="0.00"
                                />
                            </label>
                            <div v-if="panelKind === 'menus'" class="block">
                                <span
                                    class="text-[10px] font-bold text-slate-400 uppercase"
                                >
                                    Visible Branches & Nicknames
                                </span>
                                <div class="mt-2 space-y-2 rounded-md border border-slate-200 bg-white p-3"
                                >
                                    <div
                                        v-for="branch in props.branchOptions"
                                        :key="branch.id"
                                        class="grid items-center gap-2 sm:grid-cols-[auto_1fr_1fr]"
                                    >
                                        <input v-model="menuForm.branch_ids" type="checkbox" :value="branch.id" class="size-4 rounded border-slate-300 text-[#007882]" />
                                        <span class="text-xs font-semibold text-slate-600">{{ branch.name }}</span>
                                        <Input v-model="menuForm.branch_nicknames[branch.id]" :disabled="!menuForm.branch_ids.includes(branch.id)" class="h-8 text-xs" placeholder="Menu nickname" />
                                    </div>
                                </div>
                            </div>
                            <label v-else class="block">
                                <span class="text-[10px] font-bold text-slate-400 uppercase">Branch</span>
                                <select
                                    v-if="panelKind === 'categories'"
                                    v-model="categoryForm.branch_id"
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
                                <select
                                    v-else
                                    v-model="priceForm.branch_id"
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

                        <label v-if="panelKind === 'menus'" class="block">
                            <span
                                class="text-[10px] font-bold text-slate-400 uppercase"
                            >
                                Description
                            </span>
                            <Input
                                v-model="menuForm.description"
                                :disabled="!!selectedRecord"
                                class="mt-1 text-sm focus-visible:ring-[#007882]"
                                placeholder="Optional description"
                            />
                        </label>

                        <label v-if="panelKind === 'categories'" class="block">
                            <span
                                class="text-[10px] font-bold text-slate-400 uppercase"
                            >
                                Description
                            </span>
                            <Input
                                v-model="categoryForm.description"
                                :disabled="!!selectedRecord"
                                class="mt-1 text-sm focus-visible:ring-[#007882]"
                                placeholder="Optional description"
                            />
                        </label>

                        <label
                            v-if="panelKind === 'prices'"
                            class="flex items-center gap-2"
                        >
                            <input
                                v-model="priceForm.is_default"
                                :disabled="!!selectedRecord"
                                type="checkbox"
                                class="size-4 rounded border-slate-300 text-[#007882]"
                            />
                            <span class="text-xs font-bold text-slate-500">
                                Default price for this branch
                            </span>
                        </label>

                        <label v-if="selectedRecord" class="block">
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
                        :disabled="
                            menuForm.processing ||
                            categoryForm.processing ||
                            priceForm.processing
                        "
                        @click="submitPanel"
                    >
                        <Save class="size-4" />
                        Save Changes
                    </Button>
                </SheetFooter>
            </SheetContent>
        </Sheet>
    </AppLayout>
</template>
