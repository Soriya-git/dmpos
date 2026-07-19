<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Boxes, MapPinned, Search } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import MasterDataTable from '@/components/master-data/MasterDataTable.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

type BalanceItem = {
    id: number;
    code: string;
    name: string;
    itemType: string;
    unit: string;
    minimumStockQty: number;
    quantityOnHand: number;
    goodsReceiptQuantity: number;
    quantityAvailable: number;
    quantityReserved: number;
    stockValue: number;
    status: 'good' | 'low' | 'none';
};

type BalanceLocation = {
    id: number;
    branch: string;
    warehouse: string;
    warehouseCode: string;
    location: string;
    locationCode: string;
    locationType: string;
    isSaleable: boolean;
    unit: string;
    quantityOnHand: number;
    quantityReserved: number;
    quantityAvailable: number;
    averageCost: number;
    stockValue: number;
};

const props = defineProps<{
    item: BalanceItem;
    balances: BalanceLocation[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Stock Operations' },
    { title: 'Stock Movements' },
    { title: 'Balance On Hand', href: '/balance-on-hand' },
    { title: props.item.code },
];

const search = ref('');
const normalizedSearch = computed(() => search.value.trim().toLowerCase());
const visibleBalances = computed(() => {
    if (!normalizedSearch.value) {
        return props.balances;
    }

    return props.balances.filter((balance) =>
        [
            balance.branch,
            balance.warehouse,
            balance.warehouseCode,
            balance.location,
            balance.locationCode,
            balance.locationType,
            balance.quantityAvailable,
            balance.quantityOnHand,
        ].some((value) =>
            String(value).toLowerCase().includes(normalizedSearch.value),
        ),
    );
});

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

function locationTypeLabel(value: string) {
    return value.replace(/_/g, ' ');
}

function locationTypeClass(value: string) {
    const classes: Record<string, string> = {
        inbound_staging: 'border-blue-200 bg-blue-50 text-blue-700',
        putaway: 'border-[#23AA8F]/20 bg-[#23AA8F]/10 text-[#16836f]',
        damage: 'border-rose-200 bg-rose-50 text-rose-700',
        obsolete: 'border-orange-200 bg-orange-50 text-orange-700',
        scrap: 'border-slate-300 bg-slate-100 text-slate-500',
        general: 'border-slate-200 bg-slate-50 text-slate-600',
    };

    return classes[value] ?? classes.general;
}

function viewGoodsReceipts() {
    router.visit(`/putaway/completed-goods-receipts?item_id=${props.item.id}`);
}
</script>

<template>
    <Head :title="`${item.code} Balance`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #actions>
            <Button
                as-child
                variant="outline"
                class="h-9 rounded-lg border-slate-200 bg-white px-4 text-xs font-bold text-slate-600 hover:text-[#007882]"
            >
                <Link href="/balance-on-hand">
                    <ArrowLeft class="size-4" />
                    Back
                </Link>
            </Button>
        </template>

        <div
            class="flex h-[calc(100dvh-4rem)] w-full [scrollbar-gutter:stable] flex-col gap-6 overflow-y-scroll bg-[#f8fafc] p-4 text-slate-800 md:h-[calc(100dvh-5rem)] md:p-6 xl:p-8 2xl:p-10"
        >
            <div
                class="flex flex-col justify-between gap-4 lg:flex-row lg:items-center"
            >
                <div class="flex items-start gap-3">
                    <div>
                        <h1
                            class="text-2xl font-semibold tracking-tight text-[#2A4858]"
                        >
                            {{ item.name }}
                        </h1>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ item.code }} / {{ typeLabel(item.itemType) }} /
                            {{ item.unit }}
                        </p>
                    </div>
                </div>

                <div class="relative">
                    <Search
                        class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"
                    />
                    <Input
                        v-model="search"
                        placeholder="Search warehouse or location..."
                        class="h-9 w-56 rounded-lg border-slate-200 bg-white pl-9 text-xs focus-visible:ring-[#007882] lg:w-72"
                    />
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div
                    class="rounded-lg border-l-4 border-[#007882] bg-white p-4 shadow-sm"
                >
                    <p class="text-xs font-bold text-slate-500 uppercase">
                        Available
                    </p>
                    <h3 class="mt-1 text-2xl font-bold text-[#16836f]">
                        {{ numberValue(item.quantityAvailable) }}
                    </h3>
                </div>
                <div
                    class="rounded-lg border-l-4 border-blue-400 bg-white p-4 shadow-sm"
                >
                    <p class="text-xs font-bold text-slate-500 uppercase">
                        On Hand
                    </p>
                    <h3 class="mt-1 text-2xl font-bold text-blue-600">
                        {{ numberValue(item.quantityOnHand) }}
                        <button
                            v-if="Number(item.goodsReceiptQuantity) > 0"
                            type="button"
                            class="cursor-pointer rounded text-base font-medium underline decoration-blue-300 underline-offset-2 hover:text-blue-800 focus-visible:ring-2 focus-visible:ring-blue-400 focus-visible:outline-none"
                            title="View goods receipts waiting for putaway"
                            @click="viewGoodsReceipts"
                        >
                            (GR: {{ numberValue(item.goodsReceiptQuantity) }})
                        </button>
                    </h3>
                </div>
                <div
                    class="rounded-lg border-l-4 border-amber-400 bg-white p-4 shadow-sm"
                >
                    <p class="text-xs font-bold text-slate-500 uppercase">
                        Minimum
                    </p>
                    <h3 class="mt-1 text-2xl font-bold text-amber-600">
                        {{ numberValue(item.minimumStockQty) }}
                    </h3>
                </div>
                <div
                    class="rounded-lg border-l-4 border-[#23AA8F] bg-white p-4 shadow-sm"
                >
                    <p class="text-xs font-bold text-slate-500 uppercase">
                        Stock Value
                    </p>
                    <h3 class="mt-1 text-2xl font-bold text-[#16836f]">
                        {{ money(item.stockValue) }}
                    </h3>
                </div>
            </div>

            <div
                class="flex flex-col justify-between gap-3 border-b border-slate-200 pb-3 md:flex-row md:items-center"
            >
                <div class="flex items-center gap-2">
                    <Boxes class="size-4 text-[#007882]" />
                    <span
                        class="text-xs font-extrabold tracking-wider text-[#2A4858] uppercase"
                    >
                        Quantity by Warehouse and Location Bin
                    </span>
                </div>
                <span
                    class="w-fit rounded border px-2 py-0.5 text-[10px] font-bold"
                    :class="statusClass(item.status)"
                >
                    {{ statusLabel(item.status) }}
                </span>
            </div>

            <MasterDataTable
                :rows="visibleBalances"
                empty-text="No warehouse or location balance found."
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
                        Warehouse
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Location Bin
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Type
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
                        Reserved
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Value
                    </th>
                </template>
                <template #row="{ row, index }">
                    <td
                        class="px-4 py-3 text-center text-[10px] text-slate-400"
                    >
                        {{ String(index + 1).padStart(2, '0') }}
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex flex-col gap-1">
                            <span
                                class="font-mono text-xs font-bold text-[#007882]"
                            >
                                {{ row.warehouseCode }}
                            </span>
                            <span class="text-sm font-bold text-slate-700">
                                {{ row.warehouse }}
                            </span>
                            <span class="text-[10px] text-slate-500">
                                {{ row.branch }}
                            </span>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <MapPinned class="size-4 text-slate-400" />
                            <div class="flex flex-col gap-1">
                                <span
                                    class="font-mono text-xs font-bold text-slate-700"
                                >
                                    {{ row.locationCode }}
                                </span>
                                <span class="text-xs text-slate-500">
                                    {{ row.location }}
                                </span>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <span
                            class="rounded border px-2 py-0.5 text-[10px] font-bold capitalize"
                            :class="locationTypeClass(row.locationType)"
                        >
                            {{ locationTypeLabel(row.locationType) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-xs font-bold text-[#16836f]">
                        {{ numberValue(row.quantityAvailable) }}
                        {{ row.unit }}
                    </td>
                    <td class="px-4 py-3 text-xs font-bold text-slate-700">
                        {{ numberValue(row.quantityOnHand) }} {{ row.unit }}
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-500">
                        {{ numberValue(row.quantityReserved) }} {{ row.unit }}
                    </td>
                    <td class="px-4 py-3 text-xs font-bold text-amber-700">
                        {{ money(row.stockValue) }}
                    </td>
                </template>
            </MasterDataTable>
        </div>
    </AppLayout>
</template>
