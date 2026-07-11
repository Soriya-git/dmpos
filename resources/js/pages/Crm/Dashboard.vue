<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeftRight,
    ArrowRight,
    Armchair,
    ClipboardCheck,
    ClipboardList,
    PackageCheck,
    PackageSearch,
    PackageX,
    ReceiptText,
    ShoppingCart,
    Store,
    Warehouse,
} from 'lucide-vue-next';
import { computed, type Component } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';

type OperationLink = {
    title: string;
    description: string;
    href: string;
    icon: Component;
    accent: string;
    meta?: string;
};

type FlowStep = {
    step: string;
    title: string;
    description: string;
    href: string;
    icon: Component;
};

type StatusCount = {
    key: string;
    label: string;
    count: number;
};

type QueueItem = {
    id: number;
    number: string;
    name: string;
    status: string;
    amount?: number | null;
    date?: string | null;
    href: string;
};

type RecentMovement = {
    id: number;
    type: string;
    referenceNo?: string | null;
    item: string;
    branch: string;
    quantity: number;
    totalCost: number;
    date?: string | null;
};

type OperationDashboard = {
    generatedAt: string;
    scope: {
        branchNames: string[];
    };
    kpis: {
        openPosSessions: number;
        openDiningSessions: number;
        activeOrders: number;
        unpaidInvoices: number;
        unpaidBalance: number;
        pendingPurchases: number;
        receiptsInProgress: number;
        putawayTasks: number;
        pendingStockSettlements: number;
        stockMovementsToday: number;
    };
    salesStatus: {
        orders: StatusCount[];
        invoices: StatusCount[];
        stockSettlements: StatusCount[];
    };
    procurementStatus: {
        purchaseOrders: StatusCount[];
        goodsReceipts: StatusCount[];
        putaway: StatusCount[];
    };
    stockStatus: {
        movementsByType: StatusCount[];
        adjustments: StatusCount[];
        writeOffs: StatusCount[];
        internalTransfers: StatusCount[];
        customerKeepStock: StatusCount[];
    };
    recentQueues: {
        purchases: QueueItem[];
        receipts: QueueItem[];
        putaway: QueueItem[];
    };
    recentMovements: RecentMovement[];
};

const props = defineProps<{
    operationDashboard: OperationDashboard;
}>();

const operationDashboard = computed(() => props.operationDashboard);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
];

const operationCards: OperationLink[] = [
    {
        title: 'Order Operations',
        description:
            'Open tables, manage dining orders, send kitchen jobs, invoice, and settle payment.',
        href: '/orders',
        icon: Armchair,
        accent: 'bg-cyan-50 text-cyan-700 ring-cyan-100',
        meta: 'Dining workflow',
    },
    {
        title: 'Purchase',
        description:
            'Create supplier purchase orders and control approvals before receiving stock.',
        href: '/purchase',
        icon: ShoppingCart,
        accent: 'bg-amber-50 text-amber-700 ring-amber-100',
        meta: 'Procurement',
    },
    {
        title: 'Goods Receipts',
        description:
            'Receive approved purchase orders into staging and confirm received quantities.',
        href: '/goods-receipts',
        icon: PackageCheck,
        accent: 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        meta: 'Inbound receiving',
    },
    {
        title: 'Putaway',
        description:
            'Move received goods from staging into warehouse storage locations.',
        href: '/putaway',
        icon: Warehouse,
        accent: 'bg-blue-50 text-blue-700 ring-blue-100',
        meta: 'Warehouse task',
    },
];

const workflowSteps: FlowStep[] = [
    {
        step: '01',
        title: 'Order Management',
        description: 'Seat, kitchen, invoice, and receipt operations.',
        href: '/orders',
        icon: Armchair,
    },
    {
        step: '02',
        title: 'Purchase Order',
        description: 'Request and approve replenishment from suppliers.',
        href: '/purchase',
        icon: ShoppingCart,
    },
    {
        step: '03',
        title: 'Goods Receipt',
        description: 'Receive stock against approved purchase orders.',
        href: '/goods-receipts',
        icon: PackageCheck,
    },
    {
        step: '04',
        title: 'Putaway',
        description: 'Assign received stock to warehouse locations.',
        href: '/putaway',
        icon: Warehouse,
    },
    {
        step: '05',
        title: 'Stock Movement',
        description:
            'Review balances, transfers, adjustments, settlements, and write-off activity.',
        href: '/balance-on-hand',
        icon: ArrowLeftRight,
    },
];

const stockMovementLinks: OperationLink[] = [
    {
        title: 'Balance On Hand',
        description: 'Current item balances by warehouse and stock location.',
        href: '/balance-on-hand',
        icon: PackageSearch,
        accent: 'bg-slate-50 text-slate-700 ring-slate-100',
    },
    {
        title: 'Sale Settlements',
        description:
            'Post sale-side stock deductions and review settlement records.',
        href: '/stock-movements/stock-settlements',
        icon: ReceiptText,
        accent: 'bg-green-50 text-green-700 ring-green-100',
    },
    {
        title: 'Stock Adjustments',
        description:
            'Record approved count differences and operational corrections.',
        href: '/stock-movements/stock-adjustments',
        icon: ClipboardCheck,
        accent: 'bg-indigo-50 text-indigo-700 ring-indigo-100',
    },
    {
        title: 'Internal Transfer',
        description: 'Move stock between internal warehouses or locations.',
        href: '/stock-movements/internal-transfer',
        icon: ArrowLeftRight,
        accent: 'bg-sky-50 text-sky-700 ring-sky-100',
    },
    {
        title: 'Customer Keep Stock',
        description:
            'Track customer-owned or customer-held inventory movements.',
        href: '/stock-customer',
        icon: Store,
        accent: 'bg-violet-50 text-violet-700 ring-violet-100',
    },
    {
        title: 'Stock Write-off',
        description:
            'Approve and record damage, loss, expiry, or disposal movement.',
        href: '/stock-movements/write-off',
        icon: PackageX,
        accent: 'bg-rose-50 text-rose-700 ring-rose-100',
    },
];

const kpiCards = computed(() => [
    {
        title: 'Open POS Sessions',
        value: props.operationDashboard.kpis.openPosSessions,
        note: `${props.operationDashboard.kpis.openDiningSessions} open dining sessions`,
        href: '/orders',
        icon: Armchair,
        tone: 'bg-cyan-50 text-cyan-700',
    },
    {
        title: 'Active Orders',
        value: props.operationDashboard.kpis.activeOrders,
        note: `${props.operationDashboard.kpis.unpaidInvoices} unpaid invoices`,
        href: '/orders',
        icon: ClipboardList,
        tone: 'bg-amber-50 text-amber-700',
    },
    {
        title: 'Unpaid Balance',
        value: money(props.operationDashboard.kpis.unpaidBalance),
        note: 'Open invoice balance',
        href: '/sales',
        icon: ReceiptText,
        tone: 'bg-rose-50 text-rose-700',
    },
    {
        title: 'Pending Purchases',
        value: props.operationDashboard.kpis.pendingPurchases,
        note: `${props.operationDashboard.kpis.receiptsInProgress} receipts in progress`,
        href: '/purchase',
        icon: ShoppingCart,
        tone: 'bg-emerald-50 text-emerald-700',
    },
    {
        title: 'Putaway Tasks',
        value: props.operationDashboard.kpis.putawayTasks,
        note: 'Inbound stock awaiting location',
        href: '/putaway',
        icon: Warehouse,
        tone: 'bg-blue-50 text-blue-700',
    },
    {
        title: 'Stock Settlement',
        value: props.operationDashboard.kpis.pendingStockSettlements,
        note: `${props.operationDashboard.kpis.stockMovementsToday} movements today`,
        href: '/stock-movements/stock-settlements',
        icon: ArrowLeftRight,
        tone: 'bg-violet-50 text-violet-700',
    },
]);

const queueSections = computed(() => [
    {
        title: 'Purchase Queue',
        href: '/purchase',
        items: props.operationDashboard.recentQueues.purchases,
    },
    {
        title: 'Receipt Queue',
        href: '/goods-receipts',
        items: props.operationDashboard.recentQueues.receipts,
    },
    {
        title: 'Putaway Queue',
        href: '/putaway',
        items: props.operationDashboard.recentQueues.putaway,
    },
]);

function totalCount(items: StatusCount[]) {
    return items.reduce((total, item) => total + item.count, 0);
}

function money(value: number | string | null | undefined) {
    return Number(value ?? 0).toLocaleString(undefined, {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

function number(value: number | string | null | undefined) {
    return Number(value ?? 0).toLocaleString();
}

function statusTone(status: string) {
    if (
        [
            'paid',
            'received',
            'closed',
            'confirmed',
            'approved',
            'served',
        ].includes(status)
    ) {
        return 'bg-emerald-50 text-emerald-700';
    }
    if (
        [
            'created',
            'draft',
            'pending',
            'submitted',
            'in_progress',
            'in_progress_receipt',
            'partially_received',
            'partially_paid',
            'preparing',
            'ready',
            'pay_later',
        ].includes(status)
    ) {
        return 'bg-amber-50 text-amber-700';
    }
    if (
        ['cancelled', 'rejected', 'write_off', 'adjustment_out'].includes(
            status,
        )
    ) {
        return 'bg-rose-50 text-rose-700';
    }

    return 'bg-slate-50 text-slate-600';
}
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <main class="min-h-screen bg-[#f8fafc] p-4 md:p-6">
            <section
                class="mb-5 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
            >
                <div
                    class="grid gap-4 border-b border-slate-100 p-5 lg:grid-cols-[1.4fr_1fr]"
                >
                    <div>
                        <p
                            class="text-xs font-medium tracking-wide text-[#007882] uppercase"
                        >
                            Operations Dashboard
                        </p>
                        <h1
                            class="mt-2 text-2xl font-medium text-[#2A4858] md:text-3xl"
                        >
                            Operation Data and Status Dashboard
                        </h1>
                        <p class="mt-2 max-w-3xl text-sm text-slate-500">
                            Live snapshot of order operations, purchase, goods
                            receipts, putaway tasks, and all stock movement
                            controls.
                        </p>
                        <p class="mt-3 text-xs font-medium text-slate-400">
                            Updated {{ operationDashboard.generatedAt }} ·
                            Branches:
                            {{
                                operationDashboard.scope.branchNames.length
                                    ? operationDashboard.scope.branchNames.join(
                                          ', ',
                                      )
                                    : 'All branches'
                            }}
                        </p>
                    </div>

                    <div class="grid gap-2 sm:grid-cols-3 lg:grid-cols-1">
                        <div class="rounded-xl bg-emerald-50 px-4 py-3">
                            <p
                                class="text-[10px] font-medium tracking-wide text-emerald-700 uppercase"
                            >
                                Procurement Flow
                            </p>
                            <p class="text-sm font-medium text-[#2A4858]">
                                {{ operationDashboard.kpis.pendingPurchases }}
                                purchase records need follow-up
                            </p>
                        </div>
                        <div class="rounded-xl bg-cyan-50 px-4 py-3">
                            <p
                                class="text-[10px] font-medium tracking-wide text-cyan-700 uppercase"
                            >
                                Sale Flow
                            </p>
                            <p class="text-sm font-medium text-[#2A4858]">
                                {{ operationDashboard.kpis.activeOrders }}
                                active orders in service
                            </p>
                        </div>
                        <div class="rounded-xl bg-slate-50 px-4 py-3">
                            <p
                                class="text-[10px] font-medium tracking-wide text-slate-500 uppercase"
                            >
                                Ledger Focus
                            </p>
                            <p class="text-sm font-medium text-[#2A4858]">
                                {{
                                    operationDashboard.kpis.stockMovementsToday
                                }}
                                stock movements posted today
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid gap-3 p-4 sm:grid-cols-2 xl:grid-cols-6">
                    <Link
                        v-for="card in kpiCards"
                        :key="card.title"
                        :href="card.href"
                        class="group rounded-xl border border-slate-100 bg-white p-3 shadow-sm transition hover:-translate-y-0.5 hover:border-[#007882]/30 hover:shadow-md"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div
                                class="flex size-9 items-center justify-center rounded-lg"
                                :class="card.tone"
                            >
                                <component :is="card.icon" class="size-4" />
                            </div>
                            <ArrowRight
                                class="size-3.5 text-slate-300 transition group-hover:translate-x-1 group-hover:text-[#007882]"
                            />
                        </div>
                        <p
                            class="mt-3 text-[10px] font-medium tracking-wide text-slate-400 uppercase"
                        >
                            {{ card.title }}
                        </p>
                        <h2 class="mt-1 text-2xl font-medium text-[#2A4858]">
                            {{ card.value }}
                        </h2>
                        <p class="mt-1 text-xs leading-5 text-slate-500">
                            {{ card.note }}
                        </p>
                    </Link>
                </div>
            </section>

            <section
                class="mb-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
            >
                <div
                    class="mb-4 flex flex-wrap items-center justify-between gap-3"
                >
                    <div>
                        <p
                            class="text-[10px] font-medium tracking-wide text-slate-400 uppercase"
                        >
                            Operation status
                        </p>
                        <h2 class="text-lg font-medium text-[#2A4858]">
                            Current queues by workflow stage
                        </h2>
                    </div>
                    <Link
                        href="/stock-movements/stock-settlements"
                        class="inline-flex h-9 items-center gap-2 rounded-lg bg-[#007882] px-4 text-xs font-medium text-white shadow-sm hover:bg-[#006773]"
                    >
                        View Stock Settlement
                        <ArrowRight class="size-4" />
                    </Link>
                </div>

                <div class="grid gap-3 xl:grid-cols-3">
                    <div class="rounded-xl border border-slate-100 p-3">
                        <div class="mb-3 flex items-center justify-between">
                            <h3 class="font-medium text-[#2A4858]">
                                Sale Operations
                            </h3>
                            <Link
                                href="/orders"
                                class="text-xs font-medium text-[#007882]"
                            >
                                Orders
                            </Link>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <div
                                    class="mb-1 flex items-center justify-between text-xs"
                                >
                                    <span class="font-medium text-slate-500">
                                        Orders
                                    </span>
                                    <span class="font-medium text-[#2A4858]">
                                        {{
                                            totalCount(
                                                operationDashboard.salesStatus
                                                    .orders,
                                            )
                                        }}
                                    </span>
                                </div>
                                <div class="grid grid-cols-2 gap-1.5">
                                    <span
                                        v-for="status in operationDashboard
                                            .salesStatus.orders"
                                        :key="`order-${status.key}`"
                                        class="rounded-lg px-2 py-1.5 text-xs font-medium"
                                        :class="statusTone(status.key)"
                                    >
                                        {{ status.label }}: {{ status.count }}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <div
                                    class="mb-1 flex items-center justify-between text-xs"
                                >
                                    <span class="font-medium text-slate-500">
                                        Invoices
                                    </span>
                                    <span class="font-medium text-[#2A4858]">
                                        {{
                                            totalCount(
                                                operationDashboard.salesStatus
                                                    .invoices,
                                            )
                                        }}
                                    </span>
                                </div>
                                <div class="grid grid-cols-2 gap-1.5">
                                    <span
                                        v-for="status in operationDashboard
                                            .salesStatus.invoices"
                                        :key="`invoice-${status.key}`"
                                        class="rounded-lg px-2 py-1.5 text-xs font-medium"
                                        :class="statusTone(status.key)"
                                    >
                                        {{ status.label }}: {{ status.count }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-100 p-3">
                        <div class="mb-3 flex items-center justify-between">
                            <h3 class="font-medium text-[#2A4858]">
                                Procurement
                            </h3>
                            <Link
                                href="/purchase"
                                class="text-xs font-medium text-[#007882]"
                            >
                                Purchase
                            </Link>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <div
                                    class="mb-1 flex items-center justify-between text-xs"
                                >
                                    <span class="font-medium text-slate-500">
                                        Purchase Orders
                                    </span>
                                    <span class="font-medium text-[#2A4858]">
                                        {{
                                            totalCount(
                                                operationDashboard
                                                    .procurementStatus
                                                    .purchaseOrders,
                                            )
                                        }}
                                    </span>
                                </div>
                                <div class="grid grid-cols-2 gap-1.5">
                                    <span
                                        v-for="status in operationDashboard
                                            .procurementStatus.purchaseOrders"
                                        :key="`po-${status.key}`"
                                        class="rounded-lg px-2 py-1.5 text-xs font-medium"
                                        :class="statusTone(status.key)"
                                    >
                                        {{ status.label }}: {{ status.count }}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <div
                                    class="mb-1 flex items-center justify-between text-xs"
                                >
                                    <span class="font-medium text-slate-500">
                                        Goods Receipts
                                    </span>
                                    <Link
                                        href="/goods-receipts"
                                        class="font-medium text-[#007882]"
                                    >
                                        Open
                                    </Link>
                                </div>
                                <div class="grid grid-cols-2 gap-1.5">
                                    <span
                                        v-for="status in operationDashboard
                                            .procurementStatus.goodsReceipts"
                                        :key="`gr-${status.key}`"
                                        class="rounded-lg px-2 py-1.5 text-xs font-medium"
                                        :class="statusTone(status.key)"
                                    >
                                        {{ status.label }}: {{ status.count }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-100 p-3">
                        <div class="mb-3 flex items-center justify-between">
                            <h3 class="font-medium text-[#2A4858]">
                                Stock Control
                            </h3>
                            <Link
                                href="/balance-on-hand"
                                class="text-xs font-medium text-[#007882]"
                            >
                                Balance
                            </Link>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <div
                                    class="mb-1 flex items-center justify-between text-xs"
                                >
                                    <span class="font-medium text-slate-500">
                                        Putaway
                                    </span>
                                    <Link
                                        href="/putaway"
                                        class="font-medium text-[#007882]"
                                    >
                                        Open
                                    </Link>
                                </div>
                                <div class="grid grid-cols-2 gap-1.5">
                                    <span
                                        v-for="status in operationDashboard
                                            .procurementStatus.putaway"
                                        :key="`putaway-${status.key}`"
                                        class="rounded-lg px-2 py-1.5 text-xs font-medium"
                                        :class="statusTone(status.key)"
                                    >
                                        {{ status.label }}: {{ status.count }}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <div
                                    class="mb-1 flex items-center justify-between text-xs"
                                >
                                    <span class="font-medium text-slate-500">
                                        Stock Settlements
                                    </span>
                                    <Link
                                        href="/stock-movements/stock-settlements"
                                        class="font-medium text-[#007882]"
                                    >
                                        Open
                                    </Link>
                                </div>
                                <div class="grid grid-cols-3 gap-1.5">
                                    <span
                                        v-for="status in operationDashboard
                                            .salesStatus.stockSettlements"
                                        :key="`settlement-${status.key}`"
                                        class="rounded-lg px-2 py-1.5 text-xs font-medium"
                                        :class="statusTone(status.key)"
                                    >
                                        {{ status.label }}: {{ status.count }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="grid gap-5 xl:grid-cols-[1.15fr_1fr]">
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
                >
                    <div class="mb-4">
                        <p
                            class="text-[10px] font-medium tracking-wide text-slate-400 uppercase"
                        >
                            Work queues
                        </p>
                        <h2 class="text-lg font-medium text-[#2A4858]">
                            Latest records waiting for operation action
                        </h2>
                    </div>

                    <div class="grid gap-3 lg:grid-cols-3 xl:grid-cols-1">
                        <div
                            v-for="section in queueSections"
                            :key="section.title"
                            class="rounded-xl border border-slate-100 p-3"
                        >
                            <div
                                class="mb-2 flex items-center justify-between gap-3"
                            >
                                <h3 class="text-sm font-medium text-[#2A4858]">
                                    {{ section.title }}
                                </h3>
                                <Link
                                    :href="section.href"
                                    class="text-xs font-medium text-[#007882]"
                                >
                                    View all
                                </Link>
                            </div>
                            <div v-if="section.items.length" class="space-y-2">
                                <Link
                                    v-for="item in section.items"
                                    :key="`${section.title}-${item.id}`"
                                    :href="item.href"
                                    class="flex items-center justify-between gap-3 rounded-lg bg-slate-50 px-3 py-2 transition hover:bg-emerald-50/60"
                                >
                                    <div class="min-w-0">
                                        <p
                                            class="truncate text-sm font-medium text-[#2A4858]"
                                        >
                                            {{ item.number }}
                                        </p>
                                        <p
                                            class="truncate text-xs text-slate-500"
                                        >
                                            {{ item.name }}
                                        </p>
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <span
                                            class="rounded-full px-2 py-1 text-[10px] font-medium"
                                            :class="statusTone(item.status)"
                                        >
                                            {{
                                                item.status
                                                    .replaceAll('_', ' ')
                                                    .toUpperCase()
                                            }}
                                        </span>
                                        <p
                                            v-if="item.amount !== null"
                                            class="mt-1 text-xs font-medium text-slate-500"
                                        >
                                            {{ money(item.amount) }}
                                        </p>
                                    </div>
                                </Link>
                            </div>
                            <div
                                v-else
                                class="rounded-lg bg-slate-50 px-3 py-5 text-center text-xs font-medium text-slate-400"
                            >
                                No active queue.
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
                >
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <p
                                class="text-[10px] font-medium tracking-wide text-slate-400 uppercase"
                            >
                                Recent stock ledger
                            </p>
                            <h2 class="text-lg font-medium text-[#2A4858]">
                                Latest posted movement activity
                            </h2>
                        </div>
                        <Link
                            href="/balance-on-hand"
                            class="hidden rounded-full bg-slate-50 px-3 py-1 text-[10px] font-medium text-[#007882] uppercase sm:block"
                        >
                            Balance on hand
                        </Link>
                    </div>

                    <div class="space-y-2">
                        <div
                            v-for="movement in operationDashboard.recentMovements"
                            :key="movement.id"
                            class="rounded-xl border border-slate-100 px-3 py-2"
                        >
                            <div
                                class="flex items-center justify-between gap-3"
                            >
                                <div class="flex min-w-0 items-center gap-3">
                                    <div
                                        class="flex size-9 shrink-0 items-center justify-center rounded-lg"
                                        :class="statusTone(movement.type)"
                                    >
                                        <ArrowLeftRight class="size-4" />
                                    </div>
                                    <div class="min-w-0">
                                        <p
                                            class="truncate text-sm font-medium text-[#2A4858]"
                                        >
                                            {{ movement.item }}
                                        </p>
                                        <p
                                            class="truncate text-xs text-slate-500"
                                        >
                                            {{
                                                movement.type.replaceAll(
                                                    '_',
                                                    ' ',
                                                )
                                            }}
                                            · {{ movement.branch }}
                                            <template
                                                v-if="movement.referenceNo"
                                            >
                                                · {{ movement.referenceNo }}
                                            </template>
                                        </p>
                                    </div>
                                </div>
                                <div class="shrink-0 text-right">
                                    <p
                                        class="text-sm font-medium text-[#2A4858]"
                                    >
                                        {{ number(movement.quantity) }}
                                    </p>
                                    <p class="text-xs text-slate-400">
                                        {{ movement.date ?? '-' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div
                            v-if="!operationDashboard.recentMovements.length"
                            class="rounded-xl bg-slate-50 px-4 py-8 text-center text-sm font-medium text-slate-400"
                        >
                            No stock movements posted yet.
                        </div>
                    </div>
                </div>
            </section>

            <section
                class="mt-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
            >
                <div
                    class="mb-4 flex flex-wrap items-center justify-between gap-3"
                >
                    <div>
                        <p
                            class="text-[10px] font-medium tracking-wide text-slate-400 uppercase"
                        >
                            Stock movement operation status
                        </p>
                        <h2 class="text-lg font-medium text-[#2A4858]">
                            All stock movement modules
                        </h2>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <Link
                            href="/stock-movements/stock-adjustments"
                            class="rounded-lg bg-slate-50 px-3 py-2 text-xs font-medium text-[#007882]"
                        >
                            Adjustments
                        </Link>
                        <Link
                            href="/stock-movements/internal-transfer"
                            class="rounded-lg bg-slate-50 px-3 py-2 text-xs font-medium text-[#007882]"
                        >
                            Transfers
                        </Link>
                        <Link
                            href="/stock-movements/write-off"
                            class="rounded-lg bg-slate-50 px-3 py-2 text-xs font-medium text-[#007882]"
                        >
                            Write-off
                        </Link>
                    </div>
                </div>

                <div class="grid gap-3 lg:grid-cols-5">
                    <div class="rounded-xl border border-slate-100 p-3">
                        <h3 class="mb-2 text-sm font-medium text-[#2A4858]">
                            Movement Types
                        </h3>
                        <div class="space-y-1.5">
                            <div
                                v-for="status in operationDashboard.stockStatus
                                    .movementsByType"
                                :key="`movement-${status.key}`"
                                class="flex justify-between rounded-lg bg-slate-50 px-2 py-1.5 text-xs font-medium"
                            >
                                <span>{{ status.label }}</span>
                                <span>{{ status.count }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-xl border border-slate-100 p-3">
                        <h3 class="mb-2 text-sm font-medium text-[#2A4858]">
                            Adjustments
                        </h3>
                        <div class="space-y-1.5">
                            <div
                                v-for="status in operationDashboard.stockStatus
                                    .adjustments"
                                :key="`adjustment-${status.key}`"
                                class="flex justify-between rounded-lg px-2 py-1.5 text-xs font-medium"
                                :class="statusTone(status.key)"
                            >
                                <span>{{ status.label }}</span>
                                <span>{{ status.count }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-xl border border-slate-100 p-3">
                        <h3 class="mb-2 text-sm font-medium text-[#2A4858]">
                            Internal Transfer
                        </h3>
                        <div class="space-y-1.5">
                            <div
                                v-for="status in operationDashboard.stockStatus
                                    .internalTransfers"
                                :key="`transfer-${status.key}`"
                                class="flex justify-between rounded-lg px-2 py-1.5 text-xs font-medium"
                                :class="statusTone(status.key)"
                            >
                                <span>{{ status.label }}</span>
                                <span>{{ status.count }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-xl border border-slate-100 p-3">
                        <h3 class="mb-2 text-sm font-medium text-[#2A4858]">
                            Customer Keep Stock
                        </h3>
                        <div class="space-y-1.5">
                            <div
                                v-for="status in operationDashboard.stockStatus
                                    .customerKeepStock"
                                :key="`customer-stock-${status.key}`"
                                class="flex justify-between rounded-lg px-2 py-1.5 text-xs font-medium"
                                :class="statusTone(status.key)"
                            >
                                <span>{{ status.label }}</span>
                                <span>{{ status.count }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-xl border border-slate-100 p-3">
                        <h3 class="mb-2 text-sm font-medium text-[#2A4858]">
                            Write-off
                        </h3>
                        <div class="space-y-1.5">
                            <div
                                v-for="status in operationDashboard.stockStatus
                                    .writeOffs"
                                :key="`write-off-${status.key}`"
                                class="flex justify-between rounded-lg px-2 py-1.5 text-xs font-medium"
                                :class="statusTone(status.key)"
                            >
                                <span>{{ status.label }}</span>
                                <span>{{ status.count }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </AppLayout>
</template>
