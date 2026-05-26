<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    Boxes,
    CalendarDays,
    CheckCircle2,
    Clock3,
    CreditCard,
    ReceiptText,
    Search,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import MasterDataTable from '@/components/master-data/MasterDataTable.vue';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

type StockView = 'all' | 'confirmed' | 'invoiced' | 'paid' | 'pending';

type StockRow = {
    id: number;
    menuId: number;
    menuCode: string;
    menuName: string;
    category: string;
    itemCode: string | null;
    itemName: string | null;
    unit: string;
    route: string;
    confirmedQty: number;
    invoicedQty: number;
    paidQty: number;
    pendingInvoiceQty: number;
    pendingPaymentQty: number;
    orderCount: number;
    invoiceCount: number;
    totalAmount: number;
    latestOrderNo: string | null;
    latestInvoiceNo: string | null;
    latestSeat: string | null;
    latestConfirmedAt: string | null;
    latestInvoiceAt: string | null;
    latestPaidAt: string | null;
    status: 'confirmed' | 'invoiced' | 'paid';
};

const props = defineProps<{
    session: {
        date: string;
        status: string;
        sessionNo: string | null;
        branchName: string | null;
        terminalName: string | null;
        openedAt: string | null;
    };
    rows: StockRow[];
    stats: {
        stockMenus: number;
        confirmedQty: number;
        invoicedQty: number;
        paidQty: number;
        pendingQty: number;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Sale Operations' },
    { title: 'Report' },
    {
        title: 'Stock',
        href: '/operations-report/daily-session-stock',
    },
];

const activeView = ref<StockView>('all');
const search = ref('');
const sessionDate = ref(props.session.date);

const tabs: {
    key: StockView;
    label: string;
    icon: typeof Boxes;
}[] = [
    { key: 'all', label: 'All Menu', icon: Boxes },
    { key: 'confirmed', label: 'Confirmed', icon: CheckCircle2 },
    { key: 'invoiced', label: 'Invoiced', icon: ReceiptText },
    { key: 'paid', label: 'Paid', icon: CreditCard },
    { key: 'pending', label: 'Pending', icon: Clock3 },
];

const normalizedSearch = computed(() => search.value.trim().toLowerCase());
const visibleRows = computed(() => {
    const rows =
        activeView.value === 'all'
            ? props.rows
            : activeView.value === 'pending'
              ? props.rows.filter(
                    (row) =>
                        Number(row.pendingInvoiceQty) > 0 ||
                        Number(row.pendingPaymentQty) > 0,
                )
              : props.rows.filter((row) => row.status === activeView.value);

    if (!normalizedSearch.value) {
        return rows;
    }

    return rows.filter((row) =>
        [
            row.menuCode,
            row.menuName,
            row.category,
            row.itemCode,
            row.itemName,
            row.latestOrderNo,
            row.latestInvoiceNo,
            row.latestSeat,
            row.route,
            row.status,
        ].some((value) =>
            String(value ?? '')
                .toLowerCase()
                .includes(normalizedSearch.value),
        ),
    );
});

function switchView(view: StockView) {
    activeView.value = view;
}

function applyDateFilter() {
    router.get(
        '/operations-report/daily-session-stock',
        { date: sessionDate.value },
        { preserveScroll: true },
    );
}

function numberValue(value: number | string | null | undefined) {
    return Number(value ?? 0).toLocaleString(undefined, {
        minimumFractionDigits: 0,
        maximumFractionDigits: 4,
    });
}

function formatDateTime(value: string | null) {
    if (!value) {
        return 'Not yet';
    }

    return new Intl.DateTimeFormat(undefined, {
        month: 'short',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(value));
}

function statusLabel(value: StockRow['status']) {
    const labels: Record<StockRow['status'], string> = {
        confirmed: 'Confirmed',
        invoiced: 'Invoiced',
        paid: 'Paid',
    };

    return labels[value];
}

function statusClass(value: StockRow['status']) {
    const classes: Record<StockRow['status'], string> = {
        confirmed: 'border-[#007882]/20 bg-[#007882]/10 text-[#00636b]',
        invoiced: 'border-amber-200 bg-amber-50 text-amber-700',
        paid: 'border-[#23AA8F]/20 bg-[#23AA8F]/10 text-[#16836f]',
    };

    return classes[value];
}
</script>

<template>
    <Head title="Daily Session Stock" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 lg:p-6">
            <div
                class="flex flex-col justify-between gap-4 xl:flex-row xl:items-center"
            >
                <div>
                    <h1
                        class="text-2xl font-semibold tracking-tight text-[#2A4858]"
                    >
                        Daily Session Stock
                    </h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Menu items sent to the stock route, confirmed, invoiced,
                        and paid for the POS session date.
                    </p>
                    <div
                        class="mt-3 flex flex-wrap items-center gap-2 text-xs text-slate-500"
                    >
                        <span
                            class="rounded border border-slate-200 bg-white px-2 py-1 font-bold text-slate-700"
                        >
                            {{ session.branchName || 'Branch' }}
                        </span>
                        <span
                            class="rounded border border-slate-200 bg-white px-2 py-1"
                        >
                            Session:
                            {{ session.sessionNo || 'No open session' }}
                        </span>
                        <span
                            class="rounded border border-slate-200 bg-white px-2 py-1"
                        >
                            Terminal:
                            {{ session.terminalName || 'Any terminal' }}
                        </span>
                    </div>
                </div>

                <div
                    class="flex flex-col gap-3 sm:flex-row sm:items-center xl:justify-end"
                >
                    <label class="relative block">
                        <CalendarDays
                            class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"
                        />
                        <Input
                            v-model="sessionDate"
                            type="date"
                            class="h-9 w-full rounded-lg border-slate-200 bg-white pl-9 text-xs focus-visible:ring-[#007882] sm:w-44"
                            @change="applyDateFilter"
                        />
                    </label>
                    <div class="relative">
                        <Search
                            class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"
                        />
                        <Input
                            v-model="search"
                            placeholder="Search stock order menu..."
                            class="h-9 w-full rounded-lg border-slate-200 bg-white pl-9 text-xs focus-visible:ring-[#007882] sm:w-64"
                        />
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
                <div
                    class="rounded-lg border-l-4 border-[#007882] bg-white p-4 shadow-sm"
                >
                    <p class="text-xs font-bold text-slate-500 uppercase">
                        Stock Order Menu
                    </p>
                    <h3 class="mt-1 text-2xl font-bold">
                        {{ stats.stockMenus }}
                    </h3>
                </div>
                <div
                    class="rounded-lg border-l-4 border-[#23AA8F] bg-white p-4 shadow-sm"
                >
                    <p class="text-xs font-bold text-slate-500 uppercase">
                        Confirmed Qty
                    </p>
                    <h3 class="mt-1 text-2xl font-bold text-[#16836f]">
                        {{ numberValue(stats.confirmedQty) }}
                    </h3>
                </div>
                <div
                    class="rounded-lg border-l-4 border-sky-400 bg-white p-4 shadow-sm"
                >
                    <p class="text-xs font-bold text-slate-500 uppercase">
                        Invoiced Qty
                    </p>
                    <h3 class="mt-1 text-2xl font-bold text-sky-700">
                        {{ numberValue(stats.invoicedQty) }}
                    </h3>
                </div>
                <div
                    class="rounded-lg border-l-4 border-emerald-500 bg-white p-4 shadow-sm"
                >
                    <p class="text-xs font-bold text-slate-500 uppercase">
                        Paid Qty
                    </p>
                    <h3 class="mt-1 text-2xl font-bold text-emerald-700">
                        {{ numberValue(stats.paidQty) }}
                    </h3>
                </div>
                <div
                    class="rounded-lg border-l-4 border-amber-400 bg-white p-4 shadow-sm"
                >
                    <p class="text-xs font-bold text-slate-500 uppercase">
                        Pending Qty
                    </p>
                    <h3 class="mt-1 text-2xl font-bold text-amber-600">
                        {{ numberValue(stats.pendingQty) }}
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
                :rows="visibleRows"
                empty-text="No menu items with stock print route found for this POS session date."
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
                        Stock Menu
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Confirmed
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Invoiced
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Paid
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Orders / Invoices
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Status
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
                                {{ row.menuCode }}
                            </span>
                            <span class="text-sm font-bold text-slate-700">
                                {{ row.menuName }}
                            </span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-xs">
                        <div class="font-bold text-[#16836f]">
                            {{ numberValue(row.confirmedQty) }}
                            {{ row.unit }}
                        </div>
                        <div class="mt-1 text-slate-500">
                            {{ formatDateTime(row.latestConfirmedAt) }}
                        </div>
                    </td>
                    <td class="px-4 py-3 text-xs">
                        <div class="font-bold text-sky-700">
                            {{ numberValue(row.invoicedQty) }}
                            {{ row.unit }}
                        </div>
                        <div class="mt-1 text-slate-500">
                            {{ formatDateTime(row.latestInvoiceAt) }}
                        </div>
                    </td>
                    <td class="px-4 py-3 text-xs">
                        <div class="font-bold text-emerald-700">
                            {{ numberValue(row.paidQty) }}
                            {{ row.unit }}
                        </div>
                        <div class="mt-1 text-slate-500">
                            {{ formatDateTime(row.latestPaidAt) }}
                        </div>
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-600">
                        <div>
                            <span class="font-bold text-slate-700">
                                Order:
                            </span>
                            {{ row.latestOrderNo || 'Not confirmed' }}
                        </div>
                        <div class="mt-1">
                            <span class="font-bold text-slate-700">
                                Invoice:
                            </span>
                            {{ row.latestInvoiceNo || 'Not invoiced' }}
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <span
                            class="rounded border px-2 py-0.5 text-[10px] font-bold"
                            :class="statusClass(row.status)"
                        >
                            {{ statusLabel(row.status) }}
                        </span>
                    </td>
                </template>
            </MasterDataTable>
        </div>
    </AppLayout>
</template>
