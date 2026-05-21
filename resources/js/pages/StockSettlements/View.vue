<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CheckCircle2,
    PackageCheck,
    ShieldAlert,
    XCircle,
} from 'lucide-vue-next';
import { computed, reactive, ref } from 'vue';
import AlertError from '@/components/AlertError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

type Requirement = {
    invoiceLineId: number;
    menuName: string;
    itemId: number;
    itemCode: string;
    itemName: string;
    unit: string;
    quantity: number;
};

type BalanceOption = {
    id: number;
    warehouse: string;
    location: string;
    locationCode: string;
    available: number;
};

type Settlement = {
    id: number;
    invoiceNo: string;
    displayDate?: string | null;
    customer: string;
    terminal: string;
    status: string;
    invoiceStatus: string;
    posSaleQty: number;
    qtyToSettle: number;
    lineCount: number;
    requirementCount: number;
    missingBomCount: number;
    grandTotal: number;
    createdBy?: string | null;
    settledBy?: string | null;
    rejectedBy?: string | null;
    settledAt?: string | null;
    rejectedAt?: string | null;
    note?: string | null;
    lines: {
        id: number;
        menuName: string;
        quantity: number;
        lineTotal: number;
        hasBom: boolean;
    }[];
    requirements: Requirement[];
};

const props = defineProps<{
    settlement: Settlement;
    balancesByItem: Record<number, BalanceOption[]>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Stock Operations' },
    { title: 'Stock Movements' },
    {
        title: 'Sale Settlements',
        href: '/stock-movements/stock-settlements',
    },
    { title: props.settlement.invoiceNo },
];

const page = usePage();
const qtyToSettle = ref(String(props.settlement.qtyToSettle));
const confirmAction = ref<'approve' | 'reject' | null>(null);
const allocations = reactive<Record<number, Record<number, string>>>({});

props.settlement.requirements.forEach((requirement) => {
    allocations[requirement.itemId] = {};
});

const pageErrors = computed(() =>
    Object.values((page.props.errors ?? {}) as Record<string, string>).filter(
        Boolean,
    ),
);
const canAct = computed(() => props.settlement.status === 'pending');
const actionInfo = computed(() => {
    if (props.settlement.status === 'approved') {
        return {
            status: 'Approved',
            name: props.settlement.settledBy,
            at: actionDate(props.settlement.settledAt),
        };
    }

    if (props.settlement.status === 'rejected') {
        return {
            status: 'Rejected',
            name: props.settlement.rejectedBy,
            at: actionDate(props.settlement.rejectedAt),
        };
    }

    return null;
});
const scale = computed(() => {
    const saleQty = Number(props.settlement.posSaleQty || 0);
    return saleQty > 0 ? Number(qtyToSettle.value || 0) / saleQty : 1;
});
const scaledRequirements = computed(() =>
    props.settlement.requirements.map((requirement) => ({
        ...requirement,
        quantity: requirement.quantity * scale.value,
    })),
);

function formatNumber(value: number | string | null | undefined) {
    return Number(value ?? 0).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 4,
    });
}

function money(value: number | string | null | undefined) {
    return Number(value ?? 0).toLocaleString('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

function actionDate(value: string | null | undefined) {
    if (!value) return null;

    const parsed = new Date(value.replace(' ', 'T'));

    if (!Number.isNaN(parsed.getTime())) {
        return parsed.toLocaleDateString(undefined, {
            month: 'short',
            day: '2-digit',
            year: 'numeric',
        });
    }

    return value.match(/^[A-Za-z]{3}\s+\d{1,2},\s+\d{4}/)?.[0] ?? value;
}

function statusClass(value: string) {
    const classes: Record<string, string> = {
        pending: 'bg-amber-100 text-amber-700',
        approved: 'bg-green-100 text-green-700',
        rejected: 'bg-rose-100 text-rose-700',
    };

    return classes[value] ?? 'bg-slate-100 text-slate-600';
}

function selectedTotal(itemId: number) {
    return Object.values(allocations[itemId] ?? {}).reduce(
        (total, value) => total + Number(value || 0),
        0,
    );
}

function allocationPayload() {
    return Object.fromEntries(
        Object.entries(allocations).map(([itemId, balanceRows]) => [
            itemId,
            Object.entries(balanceRows)
                .filter(([, quantity]) => Number(quantity || 0) > 0)
                .map(([stockBalanceId, quantity]) => ({
                    stock_balance_id: Number(stockBalanceId),
                    quantity: Number(quantity),
                })),
        ]),
    );
}

function submitConfirmed() {
    if (!confirmAction.value) return;

    const url =
        confirmAction.value === 'approve'
            ? '/stock-movements/stock-settlements/approve'
            : '/stock-movements/stock-settlements/reject';

    router.post(
        url,
        {
            invoice_ids: [props.settlement.id],
            quantities: {
                [props.settlement.id]: Number(qtyToSettle.value || 0),
            },
            allocations: {
                [props.settlement.id]: allocationPayload(),
            },
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                confirmAction.value = null;
            },
        },
    );
}
</script>

<template>
    <Head :title="`Sale Settlement ${settlement.invoiceNo}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <main
            class="h-[calc(100dvh-4rem)] w-full [scrollbar-gutter:stable] overflow-y-scroll bg-[#f8fafc] p-4 text-slate-800 md:h-[calc(100dvh-5rem)] md:p-6 xl:p-8 2xl:p-10"
        >
            <section class="w-full">
                <div
                    v-if="($page.props.flash as any)?.success"
                    class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-[#2a4858]"
                >
                    {{ ($page.props.flash as any).success }}
                </div>
                <AlertError
                    v-if="pageErrors.length"
                    class="mb-4"
                    title="Stock settlement action failed."
                    :errors="pageErrors"
                />

                <div
                    class="mb-6 flex flex-col justify-between gap-4 xl:flex-row xl:items-start"
                >
                    <div>
                        <Button
                            type="button"
                            variant="outline"
                            class="mb-4 h-10 rounded-lg border-slate-200 bg-white"
                            @click="
                                router.visit(
                                    '/stock-movements/stock-settlements',
                                )
                            "
                        >
                            <ArrowLeft class="size-4" />
                            Back
                        </Button>
                        <p
                            class="text-xs font-bold tracking-widest text-slate-400 uppercase"
                        >
                            POS Sale Settlement
                        </p>
                        <h1 class="mt-1 text-2xl font-bold text-[#2a4858]">
                            {{ settlement.invoiceNo }}
                        </h1>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ settlement.customer }} /
                            {{ settlement.terminal }} /
                            {{ settlement.displayDate ?? '-' }}
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <span
                            class="inline-flex h-10 items-center rounded-full px-4 text-xs font-bold uppercase"
                            :class="statusClass(settlement.status)"
                        >
                            {{ settlement.status }}
                        </span>
                        <Button
                            type="button"
                            variant="outline"
                            class="h-10 rounded-lg border-rose-200 bg-white px-5 font-bold text-rose-600 hover:bg-rose-50"
                            :disabled="!canAct"
                            @click="confirmAction = 'reject'"
                        >
                            <XCircle class="size-4" />
                            Reject
                        </Button>
                        <Button
                            type="button"
                            class="h-10 rounded-lg bg-[#007882] px-5 font-bold text-white shadow-md hover:bg-[#006773]"
                            :disabled="!canAct"
                            @click="confirmAction = 'approve'"
                        >
                            <CheckCircle2 class="size-4" />
                            Approve
                        </Button>
                    </div>
                </div>

                <div class="mb-6 grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                    <div
                        class="rounded-lg border-l-4 border-[#007882] bg-white p-4 shadow-sm"
                    >
                        <p class="text-xs font-bold text-slate-500 uppercase">
                            POS Sale Qty
                        </p>
                        <h3 class="mt-1 text-xl font-bold">
                            {{ formatNumber(settlement.posSaleQty) }}
                        </h3>
                    </div>
                    <div
                        class="rounded-lg border-l-4 border-[#23aa8f] bg-white p-4 shadow-sm"
                    >
                        <p class="text-xs font-bold text-slate-500 uppercase">
                            Qty to Settle
                        </p>
                        <Input
                            v-model="qtyToSettle"
                            type="number"
                            min="0"
                            step="0.0001"
                            class="mt-2 h-9 rounded-lg border-slate-200 bg-slate-50 text-right text-sm"
                            :disabled="!canAct"
                        />
                    </div>
                    <div
                        class="rounded-lg border-l-4 border-amber-400 bg-white p-4 shadow-sm"
                    >
                        <p class="text-xs font-bold text-slate-500 uppercase">
                            Missing BOM
                        </p>
                        <h3 class="mt-1 text-xl font-bold text-amber-600">
                            {{ settlement.missingBomCount }}
                        </h3>
                    </div>
                    <div
                        class="rounded-lg border-l-4 border-slate-300 bg-white p-4 shadow-sm"
                    >
                        <p class="text-xs font-bold text-slate-500 uppercase">
                            Invoice Value
                        </p>
                        <h3 class="mt-1 text-xl font-bold text-[#2a4858]">
                            {{ money(settlement.grandTotal) }}
                        </h3>
                    </div>
                    <div
                        class="rounded-lg border-l-4 border-slate-300 bg-white p-4 shadow-sm"
                    >
                        <p class="text-xs font-bold text-slate-500 uppercase">
                            People
                        </p>
                        <p class="mt-1 font-bold text-[#2a4858]">
                            Created: {{ settlement.createdBy ?? '-' }}
                        </p>
                        <p class="mt-1 text-xs font-semibold text-slate-500">
                            <span v-if="actionInfo">
                                {{ actionInfo.status
                                }}<template v-if="actionInfo.at">
                                    at
                                    {{ actionInfo.at }}</template
                                >
                                by {{ actionInfo.name ?? '-' }}
                            </span>
                            <span v-else>-</span>
                        </p>
                    </div>
                </div>

                <div class="grid gap-6 xl:grid-cols-[1fr_1.35fr]">
                    <section
                        class="overflow-hidden rounded-lg border border-slate-100 bg-white shadow-sm"
                    >
                        <header class="border-b border-slate-100 p-5">
                            <h2 class="font-bold text-[#2a4858]">
                                Invoice Menu Lines
                            </h2>
                        </header>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead
                                    class="bg-slate-50 text-xs font-bold text-slate-500 uppercase"
                                >
                                    <tr>
                                        <th class="px-5 py-3">Menu</th>
                                        <th class="px-5 py-3 text-right">
                                            Qty
                                        </th>
                                        <th class="px-5 py-3 text-right">
                                            Total
                                        </th>
                                        <th class="px-5 py-3">BOM</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr
                                        v-for="line in settlement.lines"
                                        :key="line.id"
                                    >
                                        <td class="px-5 py-4 font-semibold">
                                            {{ line.menuName }}
                                        </td>
                                        <td class="px-5 py-4 text-right">
                                            {{ formatNumber(line.quantity) }}
                                        </td>
                                        <td
                                            class="px-5 py-4 text-right font-mono font-bold"
                                        >
                                            {{ money(line.lineTotal) }}
                                        </td>
                                        <td class="px-5 py-4">
                                            <span
                                                class="rounded-full px-2.5 py-1 text-xs font-bold uppercase"
                                                :class="
                                                    line.hasBom
                                                        ? 'bg-green-100 text-green-700'
                                                        : 'bg-amber-100 text-amber-700'
                                                "
                                            >
                                                {{
                                                    line.hasBom
                                                        ? 'Ready'
                                                        : 'Missing'
                                                }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section
                        class="overflow-hidden rounded-lg border border-slate-100 bg-white shadow-sm"
                    >
                        <header
                            class="flex items-center justify-between gap-3 border-b border-slate-100 p-5"
                        >
                            <div>
                                <h2 class="font-bold text-[#2a4858]">
                                    Inventory Settlement
                                </h2>
                                <p class="mt-1 text-sm text-slate-500">
                                    Leave allocation blank to auto cut from
                                    available bins in order.
                                </p>
                            </div>
                            <PackageCheck class="size-5 text-[#007882]" />
                        </header>

                        <div class="divide-y divide-slate-100">
                            <article
                                v-for="requirement in scaledRequirements"
                                :key="`${requirement.invoiceLineId}-${requirement.itemId}`"
                                class="p-5"
                            >
                                <div
                                    class="mb-3 flex flex-col justify-between gap-2 md:flex-row md:items-start"
                                >
                                    <div>
                                        <p class="font-bold text-[#2a4858]">
                                            {{ requirement.itemName }}
                                        </p>
                                        <p class="mt-1 text-xs text-slate-500">
                                            {{ requirement.itemCode }} /
                                            {{ requirement.menuName }}
                                        </p>
                                    </div>
                                    <div class="text-left md:text-right">
                                        <p
                                            class="text-xs font-bold text-slate-400 uppercase"
                                        >
                                            Need
                                        </p>
                                        <p class="font-bold text-[#007882]">
                                            {{
                                                formatNumber(
                                                    requirement.quantity,
                                                )
                                            }}
                                            {{ requirement.unit }}
                                        </p>
                                    </div>
                                </div>

                                <div
                                    v-if="
                                        balancesByItem[requirement.itemId]
                                            ?.length
                                    "
                                    class="grid gap-2"
                                >
                                    <div
                                        v-for="balance in balancesByItem[
                                            requirement.itemId
                                        ]"
                                        :key="balance.id"
                                        class="grid items-center gap-2 rounded-lg bg-slate-50 p-2 text-sm md:grid-cols-[1fr_8rem_8rem]"
                                    >
                                        <div>
                                            <p
                                                class="font-semibold text-slate-700"
                                            >
                                                {{ balance.locationCode }} -
                                                {{ balance.location }}
                                            </p>
                                            <p
                                                class="mt-0.5 text-xs text-slate-400"
                                            >
                                                {{ balance.warehouse }}
                                            </p>
                                        </div>
                                        <p
                                            class="text-right text-xs font-bold text-slate-500"
                                        >
                                            Available
                                            {{
                                                formatNumber(balance.available)
                                            }}
                                        </p>
                                        <Input
                                            v-model="
                                                allocations[requirement.itemId][
                                                    balance.id
                                                ]
                                            "
                                            type="number"
                                            min="0"
                                            step="0.0001"
                                            class="h-9 rounded-lg border-slate-200 bg-white text-right text-sm"
                                            placeholder="Auto"
                                            :disabled="!canAct"
                                        />
                                    </div>
                                    <p class="text-xs text-slate-500">
                                        Selected:
                                        {{
                                            formatNumber(
                                                selectedTotal(
                                                    requirement.itemId,
                                                ),
                                            )
                                        }}
                                        {{ requirement.unit }}
                                    </p>
                                </div>

                                <div
                                    v-else
                                    class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm font-semibold text-amber-700"
                                >
                                    No available stock location for this item.
                                </div>
                            </article>
                        </div>
                    </section>
                </div>
            </section>

            <div
                v-if="confirmAction"
                class="fixed inset-0 z-[80] flex items-center justify-center bg-[#2a4858]/25 p-4 backdrop-blur-sm"
                @click.self="confirmAction = null"
            >
                <section
                    class="w-full max-w-md overflow-hidden rounded-lg bg-white shadow-2xl"
                >
                    <header class="border-b border-slate-100 p-5">
                        <div class="flex items-start gap-3">
                            <div
                                class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-700"
                            >
                                <ShieldAlert class="size-5" />
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-[#2a4858]">
                                    Confirm critical action
                                </h2>
                                <p class="mt-1 text-sm text-slate-500">
                                    This will
                                    {{
                                        confirmAction === 'approve'
                                            ? 'cut stock for'
                                            : 'reject'
                                    }}
                                    invoice {{ settlement.invoiceNo }}.
                                </p>
                            </div>
                        </div>
                    </header>
                    <footer class="grid grid-cols-2 gap-3 bg-slate-50 p-5">
                        <Button
                            type="button"
                            variant="outline"
                            class="h-11 rounded-lg"
                            @click="confirmAction = null"
                        >
                            No
                        </Button>
                        <Button
                            type="button"
                            class="h-11 rounded-lg bg-[#007882] font-bold text-white hover:bg-[#006773]"
                            @click="submitConfirmed"
                        >
                            Yes, proceed
                        </Button>
                    </footer>
                </section>
            </div>
        </main>
    </AppLayout>
</template>
