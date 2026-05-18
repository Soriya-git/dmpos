<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    CheckCircle2,
    EllipsisVertical,
    ListPlus,
    Pencil,
    Plus,
    Power,
    Save,
    Search,
    Tags,
    X,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import MasterDataTable from '@/components/master-data/MasterDataTable.vue';
import SearchDropdown, {
    type SearchDropdownOption,
} from '@/components/SearchDropdown.vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
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

type PriceListRecord = {
    id: number;
    code: string;
    name: string;
    branchId: number | null;
    branch: string;
    description: string | null;
    isDefault: boolean;
    isActive: boolean;
    pricesCount: number;
};

type PriceRecord = {
    id: number;
    priceListId: number | null;
    priceList: string;
    menuId: number;
    menuCode: string | null;
    menuName: string;
    category: string;
    branchId: number | null;
    branch: string;
    priceName: string;
    price: string;
    isDefault: boolean;
    isActive: boolean;
};

type MenuOption = {
    id: number;
    code: string | null;
    name: string;
    category: string;
    branch: string;
    basePrice: string;
};

type BranchOption = {
    id: number;
    name: string;
    code: string | null;
};

const props = defineProps<{
    priceLists: PriceListRecord[];
    prices: PriceRecord[];
    menus: MenuOption[];
    branchOptions: BranchOption[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Master Data' },
    { title: 'Menu Price Lists', href: '/master-data/menu-price-lists' },
];

const search = ref('');
const selectedPriceListId = ref<number | null>(
    props.priceLists.find((priceList) => priceList.isDefault)?.id ??
        props.priceLists[0]?.id ??
        null,
);
const listPanelOpen = ref(false);
const pricePanelOpen = ref(false);
const editingPriceList = ref<PriceListRecord | null>(null);
const editingPrice = ref<PriceRecord | null>(null);

const priceLists = ref<PriceListRecord[]>([...props.priceLists]);
const prices = ref<PriceRecord[]>([...props.prices]);

const listForm = useForm({
    name: '',
    code: '',
    branch_id: '',
    description: '',
    is_default: false,
    is_active: true,
});

const priceForm = useForm({
    menu_price_list_id: '',
    menu_id: '',
    branch_id: '',
    price: '0',
    is_default: false,
    is_active: true,
});

watch(
    () => props.priceLists,
    (next) => {
        priceLists.value = [...next];

        if (
            selectedPriceListId.value &&
            next.some((priceList) => priceList.id === selectedPriceListId.value)
        ) {
            return;
        }

        selectedPriceListId.value =
            next.find((priceList) => priceList.isDefault)?.id ??
            next[0]?.id ??
            null;
    },
);

watch(
    () => props.prices,
    (next) => {
        prices.value = [...next];
    },
);

const selectedPriceList = computed(() =>
    priceLists.value.find(
        (priceList) => priceList.id === selectedPriceListId.value,
    ),
);

const normalizedSearch = computed(() => search.value.trim().toLowerCase());

const filteredPrices = computed(() => {
    return prices.value.filter((price) => {
        const matchesList =
            !selectedPriceListId.value ||
            price.priceListId === selectedPriceListId.value;
        const matchesSearch =
            !normalizedSearch.value ||
            [
                price.menuCode,
                price.menuName,
                price.category,
                price.branch,
                price.priceList,
                price.price,
            ].some((value) =>
                String(value ?? '')
                    .toLowerCase()
                    .includes(normalizedSearch.value),
            );

        return matchesList && matchesSearch;
    });
});

const missingMenusCount = computed(() => {
    if (!selectedPriceListId.value) return 0;

    const pricedMenuIds = new Set(
        prices.value
            .filter((price) => price.priceListId === selectedPriceListId.value)
            .map((price) => price.menuId),
    );

    return props.menus.filter((menu) => !pricedMenuIds.has(menu.id)).length;
});

const menuDropdownOptions = computed<SearchDropdownOption[]>(() =>
    props.menus.map((menu) => ({
        value: menu.id,
        label: `${menu.code ?? `MENU-${menu.id}`} - ${menu.name}`,
        description: menu.category,
        meta: `${menu.branch} / ${money(menu.basePrice)}`,
    })),
);

function money(value: string | number) {
    return Number(value || 0).toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

function openListPanel(priceList: PriceListRecord | null = null) {
    listForm.reset();
    listForm.clearErrors();
    editingPriceList.value = priceList;

    if (priceList) {
        listForm.name = priceList.name;
        listForm.code = priceList.code;
        listForm.branch_id = priceList.branchId
            ? String(priceList.branchId)
            : '';
        listForm.description = priceList.description ?? '';
        listForm.is_default = priceList.isDefault;
        listForm.is_active = priceList.isActive;
    }

    listPanelOpen.value = true;
}

function openPricePanel(menu: MenuOption | null = null) {
    priceForm.reset();
    priceForm.clearErrors();
    editingPrice.value = null;
    priceForm.menu_price_list_id = selectedPriceListId.value
        ? String(selectedPriceListId.value)
        : '';

    if (menu) {
        priceForm.menu_id = String(menu.id);
        priceForm.price = menu.basePrice;
    }

    pricePanelOpen.value = true;
}

function openPriceEditPanel(price: PriceRecord) {
    priceForm.reset();
    priceForm.clearErrors();
    editingPrice.value = price;
    priceForm.menu_price_list_id = price.priceListId
        ? String(price.priceListId)
        : '';
    priceForm.menu_id = String(price.menuId);
    priceForm.branch_id = price.branchId ? String(price.branchId) : '';
    priceForm.price = price.price;
    priceForm.is_default = price.isDefault;
    priceForm.is_active = price.isActive;
    pricePanelOpen.value = true;
}

function submitList() {
    if (editingPriceList.value) {
        listForm.patch(
            `/master-data/menu-price-lists/${editingPriceList.value.id}`,
            {
                preserveScroll: true,
                onSuccess: () => {
                    listPanelOpen.value = false;
                    editingPriceList.value = null;
                },
            },
        );

        return;
    }

    listForm.post('/master-data/menu-price-lists', {
        preserveScroll: true,
        onSuccess: () => {
            listPanelOpen.value = false;
            editingPriceList.value = null;
        },
    });
}

function selectMenuOption(option: SearchDropdownOption | null) {
    if (!option) {
        priceForm.menu_id = '';
        return;
    }

    priceForm.menu_id = String(option.value);

    const menu = props.menus.find(
        (item) => String(item.id) === String(option.value),
    );

    if (menu) {
        priceForm.price = menu.basePrice;
    }
}

function submitPrice() {
    if (editingPrice.value) {
        priceForm.patch(
            `/master-data/menu-price-lists/prices/${editingPrice.value.id}`,
            {
                preserveScroll: true,
                onSuccess: () => {
                    pricePanelOpen.value = false;
                    editingPrice.value = null;
                },
            },
        );

        return;
    }

    priceForm.post('/master-data/menu-price-lists/prices', {
        preserveScroll: true,
        onSuccess: () => {
            pricePanelOpen.value = false;
        },
    });
}

function updateMenuPriceStatus(price: PriceRecord, isActive: boolean) {
    router.patch(
        `/master-data/menu-price-lists/prices/${price.id}`,
        {
            menu_price_list_id: price.priceListId,
            menu_id: price.menuId,
            branch_id: price.branchId,
            price: price.price,
            is_default: price.isDefault,
            is_active: isActive,
        },
        {
            preserveScroll: true,
        },
    );
}

function selectList(priceList: PriceListRecord) {
    selectedPriceListId.value = priceList.id;
}
</script>

<template>
    <Head title="Menu Price Lists" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-5 p-4 lg:p-6">
            <header
                class="flex flex-col justify-between gap-4 lg:flex-row lg:items-center"
            >
                <div>
                    <h1
                        class="text-2xl font-semibold tracking-tight text-[#2A4858]"
                    >
                        Menu Price Lists
                    </h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Manage selectable menu price lists for new orders.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <div class="relative">
                        <Search
                            class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"
                        />
                        <Input
                            v-model="search"
                            placeholder="Search menu prices..."
                            class="h-9 w-56 rounded-lg border-slate-200 bg-white pl-9 text-xs focus-visible:ring-[#007882] lg:w-72"
                        />
                    </div>
                    <Button
                        variant="outline"
                        class="h-9 rounded-lg text-xs font-bold text-[#2A4858]"
                        @click="openListPanel"
                    >
                        <ListPlus class="size-4" />
                        New List
                    </Button>
                    <Button
                        class="h-9 rounded-lg bg-[#007882] px-4 text-xs font-bold text-white hover:bg-[#006871]"
                        @click="openPricePanel()"
                    >
                        <Plus class="size-4" />
                        Add Price
                    </Button>
                </div>
            </header>

            <section class="grid gap-4 lg:grid-cols-[300px_1fr]">
                <aside
                    class="rounded-lg border border-slate-200 bg-white p-3 shadow-sm"
                >
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <div>
                            <p
                                class="text-[10px] font-extrabold tracking-wider text-slate-400 uppercase"
                            >
                                Price Lists
                            </p>
                            <p class="text-xs text-slate-500">
                                {{ priceLists.length }} active option(s)
                            </p>
                        </div>
                        <Tags class="size-5 text-[#23AA8F]" />
                    </div>

                    <div v-if="priceLists.length" class="space-y-2">
                        <button
                            v-for="priceList in priceLists"
                            :key="priceList.id"
                            type="button"
                            class="w-full rounded-lg border p-3 text-left transition"
                            :class="
                                selectedPriceListId === priceList.id
                                    ? 'border-[#007882] bg-[#007882]/5 shadow-sm'
                                    : 'border-slate-200 hover:border-[#23AA8F] hover:bg-slate-50'
                            "
                            @click="selectList(priceList)"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p
                                        class="truncate text-sm font-extrabold text-[#2A4858]"
                                    >
                                        {{ priceList.name }}
                                    </p>
                                    <p
                                        class="mt-0.5 truncate text-[10px] text-slate-400"
                                    >
                                        {{ priceList.code }} /
                                        {{ priceList.branch }}
                                    </p>
                                </div>
                                <CheckCircle2
                                    v-if="priceList.isDefault"
                                    class="size-4 shrink-0 text-[#23AA8F]"
                                />
                            </div>

                            <div class="mt-3 flex items-center gap-2">
                                <span
                                    class="rounded bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-500"
                                >
                                    {{ priceList.pricesCount }} prices
                                </span>
                                <span
                                    class="rounded px-2 py-0.5 text-[10px] font-bold"
                                    :class="
                                        priceList.isActive
                                            ? 'bg-emerald-50 text-emerald-700'
                                            : 'bg-slate-100 text-slate-400'
                                    "
                                >
                                    {{
                                        priceList.isActive
                                            ? 'Active'
                                            : 'Inactive'
                                    }}
                                </span>
                            </div>
                        </button>
                    </div>

                    <div
                        v-else
                        class="rounded-lg border border-dashed border-slate-200 p-6 text-center text-xs font-medium text-slate-400"
                    >
                        No price lists found.
                    </div>
                </aside>

                <div class="min-w-0 space-y-4">
                    <div
                        class="flex flex-col justify-between gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm md:flex-row md:items-center"
                    >
                        <div>
                            <p
                                class="text-[10px] font-extrabold tracking-wider text-slate-400 uppercase"
                            >
                                Selected List
                            </p>
                            <h2 class="text-lg font-black text-[#2A4858]">
                                {{ selectedPriceList?.name ?? 'No price list' }}
                            </h2>
                            <p class="text-xs text-slate-500">
                                {{ missingMenusCount }} menu(s) still need a
                                price in this list.
                            </p>
                        </div>
                        <Button
                            class="h-9 rounded-lg bg-[#23AA8F] px-4 text-xs font-bold text-white hover:bg-[#007882]"
                            :disabled="!selectedPriceListId"
                            @click="openPricePanel()"
                        >
                            <Plus class="size-4" />
                            Price Menu
                        </Button>
                    </div>

                    <MasterDataTable
                        :rows="filteredPrices"
                        empty-text="No prices found for this price list."
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
                                Menu
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
                                class="px-4 py-3 text-right text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
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
                                <p class="text-sm font-bold text-slate-700">
                                    {{ row.menuName }}
                                </p>
                                <p class="font-mono text-[10px] text-[#007882]">
                                    {{ row.menuCode ?? 'MENU-' + row.menuId }}
                                </p>
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-500">
                                {{ row.category }}
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-500">
                                {{ row.branch }}
                            </td>
                            <td
                                class="px-4 py-3 text-right text-sm font-black text-amber-700"
                            >
                                {{ money(row.price) }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="rounded px-2 py-0.5 text-[10px] font-bold"
                                    :class="
                                        row.isActive
                                            ? 'bg-emerald-50 text-emerald-700'
                                            : 'bg-slate-100 text-slate-400'
                                    "
                                >
                                    {{ row.isActive ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="h-8 w-8 rounded-lg text-slate-500 hover:bg-slate-100"
                                        >
                                            <EllipsisVertical class="size-4" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuItem
                                            @click="openPriceEditPanel(row)"
                                        >
                                            <Pencil class="mr-2 size-4" />
                                            Edit
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            v-if="row.isActive"
                                            class="text-rose-600 focus:text-rose-600"
                                            @click="
                                                updateMenuPriceStatus(
                                                    row,
                                                    false,
                                                )
                                            "
                                        >
                                            <Power class="mr-2 size-4" />
                                            Deactivate
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            v-else
                                            class="text-emerald-700 focus:text-emerald-700"
                                            @click="
                                                updateMenuPriceStatus(row, true)
                                            "
                                        >
                                            <Power class="mr-2 size-4" />
                                            Activate
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </td>
                        </template>
                    </MasterDataTable>
                </div>
            </section>
        </div>

        <Sheet v-model:open="listPanelOpen">
            <SheetContent class="w-full overflow-y-auto sm:max-w-md">
                <SheetHeader class="border-b p-6">
                    <div class="flex items-center justify-between">
                        <SheetTitle class="text-[#2A4858]">
                            {{
                                editingPriceList
                                    ? 'Update Price List'
                                    : 'New Price List'
                            }}
                        </SheetTitle>
                        <button
                            class="rounded-full p-1 text-slate-400 hover:bg-slate-100"
                            @click="listPanelOpen = false"
                        >
                            <X class="size-4" />
                        </button>
                    </div>
                </SheetHeader>

                <div class="space-y-4 p-6">
                    <label class="block">
                        <span
                            class="text-[10px] font-bold text-slate-400 uppercase"
                        >
                            Name
                        </span>
                        <Input
                            v-model="listForm.name"
                            class="mt-1 focus-visible:ring-[#007882]"
                            placeholder="Local PriceList"
                        />
                        <p
                            v-if="listForm.errors.name"
                            class="mt-1 text-xs text-red-500"
                        >
                            {{ listForm.errors.name }}
                        </p>
                    </label>

                    <label class="block">
                        <span
                            class="text-[10px] font-bold text-slate-400 uppercase"
                        >
                            Code
                        </span>
                        <Input
                            v-model="listForm.code"
                            class="mt-1 font-mono uppercase focus-visible:ring-[#007882]"
                            placeholder="LOCAL"
                        />
                        <p
                            v-if="listForm.errors.code"
                            class="mt-1 text-xs text-red-500"
                        >
                            {{ listForm.errors.code }}
                        </p>
                    </label>

                    <label class="block">
                        <span
                            class="text-[10px] font-bold text-slate-400 uppercase"
                        >
                            Branch
                        </span>
                        <select
                            v-model="listForm.branch_id"
                            class="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus:border-[#007882]"
                        >
                            <option value="">Default branch</option>
                            <option
                                v-for="branch in branchOptions"
                                :key="branch.id"
                                :value="branch.id"
                            >
                                {{ branch.name }}
                            </option>
                        </select>
                    </label>

                    <label class="block">
                        <span
                            class="text-[10px] font-bold text-slate-400 uppercase"
                        >
                            Description
                        </span>
                        <Input
                            v-model="listForm.description"
                            class="mt-1 focus-visible:ring-[#007882]"
                            placeholder="Optional note"
                        />
                    </label>

                    <label class="flex items-center gap-2">
                        <input
                            v-model="listForm.is_default"
                            type="checkbox"
                            class="size-4 rounded border-slate-300 text-[#007882]"
                        />
                        <span class="text-xs font-bold text-slate-500">
                            Use as default when opening orders
                        </span>
                    </label>

                    <label class="flex items-center gap-2">
                        <input
                            v-model="listForm.is_active"
                            type="checkbox"
                            class="size-4 rounded border-slate-300 text-[#007882]"
                        />
                        <span class="text-xs font-bold text-slate-500">
                            Active price list
                        </span>
                    </label>
                </div>

                <SheetFooter class="flex-row gap-3 border-t p-6">
                    <Button
                        variant="outline"
                        class="flex-1 rounded-lg text-xs font-bold"
                        @click="listPanelOpen = false"
                    >
                        Cancel
                    </Button>
                    <Button
                        class="flex-1 rounded-lg bg-[#007882] text-xs font-bold text-white hover:bg-[#006871]"
                        :disabled="listForm.processing"
                        @click="submitList"
                    >
                        <Save class="size-4" />
                        Save
                    </Button>
                </SheetFooter>
            </SheetContent>
        </Sheet>

        <Sheet v-model:open="pricePanelOpen">
            <SheetContent class="w-full overflow-y-auto sm:max-w-md">
                <SheetHeader class="border-b p-6">
                    <div class="flex items-center justify-between">
                        <SheetTitle class="text-[#2A4858]">
                            {{
                                editingPrice
                                    ? 'Update Menu Price'
                                    : 'Menu Price'
                            }}
                        </SheetTitle>
                        <button
                            class="rounded-full p-1 text-slate-400 hover:bg-slate-100"
                            @click="pricePanelOpen = false"
                        >
                            <X class="size-4" />
                        </button>
                    </div>
                </SheetHeader>

                <div class="space-y-4 p-6">
                    <label class="block">
                        <span
                            class="text-[10px] font-bold text-slate-400 uppercase"
                        >
                            Price List
                        </span>
                        <select
                            v-model="priceForm.menu_price_list_id"
                            class="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus:border-[#007882]"
                        >
                            <option value="">Select price list</option>
                            <option
                                v-for="priceList in priceLists"
                                :key="priceList.id"
                                :value="priceList.id"
                            >
                                {{ priceList.name }}
                            </option>
                        </select>
                        <p
                            v-if="priceForm.errors.menu_price_list_id"
                            class="mt-1 text-xs text-red-500"
                        >
                            {{ priceForm.errors.menu_price_list_id }}
                        </p>
                    </label>

                    <label class="block">
                        <span
                            class="text-[10px] font-bold text-slate-400 uppercase"
                        >
                            Menu
                        </span>
                        <SearchDropdown
                            v-model="priceForm.menu_id"
                            :options="menuDropdownOptions"
                            placeholder="Search menu..."
                            search-placeholder="Search by code, name, category..."
                            empty-text="No menu found."
                            input-class="mt-1 h-9 rounded-md text-sm"
                            @select="selectMenuOption"
                        />
                        <p
                            v-if="priceForm.errors.menu_id"
                            class="mt-1 text-xs text-red-500"
                        >
                            {{ priceForm.errors.menu_id }}
                        </p>
                    </label>

                    <label class="block">
                        <span
                            class="text-[10px] font-bold text-slate-400 uppercase"
                        >
                            Price
                        </span>
                        <Input
                            v-model="priceForm.price"
                            type="number"
                            step="0.01"
                            class="mt-1 font-bold text-[#007882] focus-visible:ring-[#007882]"
                            placeholder="0.00"
                        />
                        <p
                            v-if="priceForm.errors.price"
                            class="mt-1 text-xs text-red-500"
                        >
                            {{ priceForm.errors.price }}
                        </p>
                    </label>

                    <label class="block">
                        <span
                            class="text-[10px] font-bold text-slate-400 uppercase"
                        >
                            Branch
                        </span>
                        <select
                            v-model="priceForm.branch_id"
                            class="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus:border-[#007882]"
                        >
                            <option value="">Price list branch</option>
                            <option
                                v-for="branch in branchOptions"
                                :key="branch.id"
                                :value="branch.id"
                            >
                                {{ branch.name }}
                            </option>
                        </select>
                    </label>

                    <label class="flex items-center gap-2">
                        <input
                            v-model="priceForm.is_active"
                            type="checkbox"
                            class="size-4 rounded border-slate-300 text-[#007882]"
                        />
                        <span class="text-xs font-bold text-slate-500">
                            Active menu price
                        </span>
                    </label>
                </div>

                <SheetFooter class="flex-row gap-3 border-t p-6">
                    <Button
                        variant="outline"
                        class="flex-1 rounded-lg text-xs font-bold"
                        @click="pricePanelOpen = false"
                    >
                        Cancel
                    </Button>
                    <Button
                        class="flex-1 rounded-lg bg-[#007882] text-xs font-bold text-white hover:bg-[#006871]"
                        :disabled="priceForm.processing"
                        @click="submitPrice"
                    >
                        <Save class="size-4" />
                        Save
                    </Button>
                </SheetFooter>
            </SheetContent>
        </Sheet>
    </AppLayout>
</template>
