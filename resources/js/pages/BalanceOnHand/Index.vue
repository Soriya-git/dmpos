<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    AlertTriangle,
    Boxes,
    CheckCircle2,
    Eye,
    PackageX,
    Search,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import MasterDataTable from '@/components/master-data/MasterDataTable.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

type BalanceView = 'all' | 'good' | 'low' | 'none';

type BalanceItem = {
    id: number;
    code: string;
    name: string;
    itemType: string;
    unit: string;
    minimumStockQty: number;
    quantityOnHand: number;
    quantityAvailable: number;
    quantityReserved: number;
    stockValue: number;
    locationsCount: number;
    status: 'good' | 'low' | 'none';
};

const props = defineProps<{
    items: BalanceItem[];
    stats: {
        allItems: number;
        goodStock: number;
        lowStock: number;
        noStock: number;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Stock Operations' },
    { title: 'Stock Movements' },
    { title: 'Balance On Hand', href: '/balance-on-hand' },
];

const activeView = ref<BalanceView>('all');
const search = ref('');

const tabs: {
    key: BalanceView;
    label: string;
    icon: typeof Boxes;
}[] = [
    { key: 'all', label: 'All Item', icon: Boxes },
    { key: 'good', label: 'Good Stock', icon: CheckCircle2 },
    { key: 'low', label: 'Low Stock', icon: AlertTriangle },
    { key: 'none', label: 'No Stock', icon: PackageX },
];

const normalizedSearch = computed(() => search.value.trim().toLowerCase());
const visibleItems = computed(() => {
    const rows =
        activeView.value === 'all'
            ? props.items
            : props.items.filter((item) => item.status === activeView.value);

    if (!normalizedSearch.value) {
        return rows;
    }

    return rows.filter((item) =>
        [
            item.code,
            item.name,
            item.itemType,
            item.unit,
            item.status,
            item.quantityAvailable,
            item.minimumStockQty,
        ].some((value) =>
            String(value).toLowerCase().includes(normalizedSearch.value),
        ),
    );
});

function switchView(view: BalanceView) {
    activeView.value = view;
}

function numberValue(value: number | string | null | undefined) {
    return Number(value ?? 0).toLocaleString(undefined, {
        minimumFractionDigits: 0,
        maximumFractionDigits: 4,
    });
}

function money(value: number | string | null | undefined) {
    return Number(value ?? 0).toLocaleString(undefined, {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

function typeLabel(value: string) {
    return value.replace(/_/g, ' ');
}

function statusLabel(value: BalanceItem['status']) {
    const labels: Record<BalanceItem['status'], string> = {
        good: 'Good Stock',
        low: 'Low Stock',
        none: 'No Stock',
    };

    return labels[value];
}

function statusClass(value: BalanceItem['status']) {
    const classes: Record<BalanceItem['status'], string> = {
        good: 'border-[#23AA8F]/20 bg-[#23AA8F]/10 text-[#16836f]',
        low: 'border-amber-200 bg-amber-50 text-amber-700',
        none: 'border-rose-200 bg-rose-50 text-rose-700',
    };

    return classes[value];
}

function viewItem(item: BalanceItem) {
    router.visit(`/balance-on-hand/${item.id}`);
}
</script>

<template>
    <Head title="Balance On Hand" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 lg:p-6">
            <div
                class="flex flex-col justify-between gap-4 lg:flex-row lg:items-center"
            >
                <div>
                    <h1
                        class="text-2xl font-semibold tracking-tight text-[#2A4858]"
                    >
                        Balance On Hand
                    </h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Current inventory on hand, available stock, minimum
                        stock levels, and stock value.
                    </p>
                </div>

                <div class="relative">
                    <Search
                        class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"
                    />
                    <Input
                        v-model="search"
                        placeholder="Search item balance..."
                        class="h-9 w-56 rounded-lg border-slate-200 bg-white pl-9 text-xs focus-visible:ring-[#007882] lg:w-72"
                    />
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div
                    class="rounded-lg border-l-4 border-[#007882] bg-white p-4 shadow-sm"
                >
                    <p class="text-xs font-bold text-slate-500 uppercase">
                        All Items
                    </p>
                    <h3 class="mt-1 text-2xl font-bold">
                        {{ stats.allItems }}
                    </h3>
                </div>
                <div
                    class="rounded-lg border-l-4 border-[#23AA8F] bg-white p-4 shadow-sm"
                >
                    <p class="text-xs font-bold text-slate-500 uppercase">
                        Good Stock
                    </p>
                    <h3 class="mt-1 text-2xl font-bold text-[#16836f]">
                        {{ stats.goodStock }}
                    </h3>
                </div>
                <div
                    class="rounded-lg border-l-4 border-amber-400 bg-white p-4 shadow-sm"
                >
                    <p class="text-xs font-bold text-slate-500 uppercase">
                        Low Stock
                    </p>
                    <h3 class="mt-1 text-2xl font-bold text-amber-600">
                        {{ stats.lowStock }}
                    </h3>
                </div>
                <div
                    class="rounded-lg border-l-4 border-rose-400 bg-white p-4 shadow-sm"
                >
                    <p class="text-xs font-bold text-slate-500 uppercase">
                        No Stock
                    </p>
                    <h3 class="mt-1 text-2xl font-bold text-rose-600">
                        {{ stats.noStock }}
                    </h3>
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
                :rows="visibleItems"
                empty-text="No balance on hand data found."
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
                        Item Code
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Item Name
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Available
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        On Hand
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Minimum
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Value
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
                    <td class="px-4 py-3">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-bold text-slate-700">
                                {{ row.name }}
                            </span>
                            <span
                                class="w-fit rounded border border-slate-200 bg-slate-50 px-2 py-0.5 text-[10px] text-slate-600 capitalize"
                            >
                                {{ typeLabel(row.itemType) }}
                            </span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-xs font-bold text-[#16836f]">
                        {{ numberValue(row.quantityAvailable) }}
                        {{ row.unit }}
                    </td>
                    <td class="px-4 py-3 text-xs font-bold text-slate-700">
                        {{ numberValue(row.quantityOnHand) }} {{ row.unit }}
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-500">
                        {{ numberValue(row.minimumStockQty) }} {{ row.unit }}
                    </td>
                    <td class="px-4 py-3 text-xs font-bold text-amber-700">
                        {{ money(row.stockValue) }}
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
                        <Button
                            variant="ghost"
                            size="icon"
                            class="size-8 text-slate-500 hover:text-[#007882]"
                            @click="viewItem(row)"
                        >
                            <Eye class="size-4" />
                            <span class="sr-only">View balance detail</span>
                        </Button>
                    </td>
                </template>
            </MasterDataTable>
        </div>
    </AppLayout>
</template>
