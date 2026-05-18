<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    CheckCircle2,
    Eye,
    Filter,
    MoreVertical,
    RotateCcw,
    Search,
    ShieldAlert,
    XCircle,
} from 'lucide-vue-next';
import { computed, reactive, ref, watch } from 'vue';
import AlertError from '@/components/AlertError.vue';
import TablePagination from '@/components/TablePagination.vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { usePagination } from '@/composables/usePagination';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

type SettlementStatus = 'pending' | 'approved' | 'rejected';

type Settlement = {
    id: number;
    invoiceNo: string;
    displayDate?: string | null;
    customer: string;
    terminal: string;
    status: SettlementStatus | string;
    invoiceStatus: string;
    posSaleQty: number;
    qtyToSettle: number;
    lineCount: number;
    requirementCount: number;
    missingBomCount: number;
    grandTotal: number;
};

const props = defineProps<{
    settlements: Settlement[];
    filters: {
        search?: string | null;
        status?: string | null;
        date_from?: string | null;
        date_to?: string | null;
        pos_terminal_id?: number | string | null;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Stock Operations' },
    { title: 'Stock Movements' },
    {
        title: 'Sale Settlements',
        href: '/stock-movements/stock-settlements',
    },
];

const page = usePage();
const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? 'pending');
const dateFrom = ref(props.filters.date_from ?? '');
const dateTo = ref(props.filters.date_to ?? '');
const selected = ref<number[]>([]);
const qtyToSettle = reactive<Record<number, string>>({});
const confirmAction = ref<'approve' | 'reject' | null>(null);
const confirmIds = ref<number[]>([]);

watch(
    () => props.settlements,
    (rows) => {
        rows.forEach((row) => {
            qtyToSettle[row.id] = String(row.qtyToSettle ?? row.posSaleQty);
        });
    },
    { immediate: true },
);

const {
    currentPage,
    totalRows,
    totalPages,
    pageStart,
    pageEnd,
    paginatedRows,
    goToPage,
    pageSize,
    setRowsPerPage,
} = usePagination(() => props.settlements, 10);

const pageErrors = computed(() =>
    Object.values((page.props.errors ?? {}) as Record<string, string>).filter(
        Boolean,
    ),
);
const pendingCount = computed(
    () => props.settlements.filter((row) => row.status === 'pending').length,
);
const approvedCount = computed(
    () => props.settlements.filter((row) => row.status === 'approved').length,
);
const rejectedCount = computed(
    () => props.settlements.filter((row) => row.status === 'rejected').length,
);
const saleQtyTotal = computed(() =>
    props.settlements.reduce((total, row) => total + Number(row.posSaleQty), 0),
);
const selectedRows = computed(() =>
    props.settlements.filter((row) => selected.value.includes(row.id)),
);
const canBulkAct = computed(
    () =>
        selectedRows.value.length > 0 &&
        selectedRows.value.every((row) => row.status === 'pending'),
);
const allPageSelected = computed(() => {
    const rows = paginatedRows.value;

    return (
        rows.length > 0 && rows.every((row) => selected.value.includes(row.id))
    );
});

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

function statusClass(value: string) {
    const classes: Record<string, string> = {
        pending: 'bg-amber-100 text-amber-700',
        approved: 'bg-green-100 text-green-700',
        rejected: 'bg-rose-100 text-rose-700',
    };

    return classes[value] ?? 'bg-slate-100 text-slate-600';
}

function applyFilters() {
    router.get(
        '/stock-movements/stock-settlements',
        {
            search: search.value || undefined,
            status: status.value || undefined,
            date_from: dateFrom.value || undefined,
            date_to: dateTo.value || undefined,
        },
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        },
    );
}

function resetFilters() {
    search.value = '';
    status.value = 'pending';
    dateFrom.value = '';
    dateTo.value = '';
    router.get(
        '/stock-movements/stock-settlements',
        { status: 'pending' },
        { preserveScroll: true, replace: true },
    );
}

function togglePageSelection() {
    const pageIds = paginatedRows.value.map((row) => row.id);

    if (allPageSelected.value) {
        selected.value = selected.value.filter((id) => !pageIds.includes(id));
        return;
    }

    selected.value = Array.from(new Set([...selected.value, ...pageIds]));
}

function openConfirm(action: 'approve' | 'reject', ids: number[]) {
    confirmAction.value = action;
    confirmIds.value = ids;
}

function closeConfirm() {
    confirmAction.value = null;
    confirmIds.value = [];
}

function submitConfirmed() {
    if (!confirmAction.value || confirmIds.value.length === 0) return;

    const url =
        confirmAction.value === 'approve'
            ? '/stock-movements/stock-settlements/approve'
            : '/stock-movements/stock-settlements/reject';

    router.post(
        url,
        {
            invoice_ids: confirmIds.value,
            quantities: Object.fromEntries(
                confirmIds.value.map((id) => [
                    id,
                    Number(qtyToSettle[id] || 0),
                ]),
            ),
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                selected.value = [];
                closeConfirm();
            },
        },
    );
}

function viewSettlement(row: Settlement) {
    router.visit(`/stock-movements/stock-settlements/${row.id}`);
}
</script>

<template>
    <Head title="Sale Stock Settlements" />

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
                    class="mb-8 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4 2xl:gap-6"
                >
                    <div
                        class="rounded-lg border-l-4 border-[#007882] bg-white p-5 shadow-sm"
                    >
                        <p class="text-xs font-bold text-slate-500 uppercase">
                            POS Invoices
                        </p>
                        <h3 class="mt-1 text-2xl font-bold">
                            {{ settlements.length }}
                        </h3>
                    </div>
                    <div
                        class="rounded-lg border-l-4 border-amber-400 bg-white p-5 shadow-sm"
                    >
                        <p class="text-xs font-bold text-slate-500 uppercase">
                            Pending
                        </p>
                        <h3 class="mt-1 text-2xl font-bold text-amber-600">
                            {{ pendingCount }}
                        </h3>
                    </div>
                    <div
                        class="rounded-lg border-l-4 border-green-400 bg-white p-5 shadow-sm"
                    >
                        <p class="text-xs font-bold text-slate-500 uppercase">
                            Approved
                        </p>
                        <h3 class="mt-1 text-2xl font-bold text-green-600">
                            {{ approvedCount }}
                        </h3>
                    </div>
                    <div
                        class="rounded-lg border-l-4 border-[#23aa8f] bg-white p-5 shadow-sm"
                    >
                        <p class="text-xs font-bold text-slate-500 uppercase">
                            POS Sale Qty
                        </p>
                        <h3 class="mt-1 text-2xl font-bold text-[#007882]">
                            {{ formatNumber(saleQtyTotal) }}
                        </h3>
                    </div>
                </div>

                <div
                    class="mb-6 flex flex-col items-stretch justify-between gap-4 xl:flex-row xl:items-center"
                >
                    <div
                        class="grid w-full grid-cols-1 gap-2 md:grid-cols-[minmax(18rem,28rem)_11rem_10rem_10rem_2.5rem] xl:w-auto"
                    >
                        <div class="relative w-full">
                            <Search
                                class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"
                            />
                            <Input
                                v-model="search"
                                type="text"
                                placeholder="Search invoice, customer, or menu..."
                                class="h-10 rounded-lg border-slate-200 bg-white pl-10 focus-visible:ring-[#007882]/30"
                                @keyup.enter="applyFilters"
                            />
                        </div>
                        <select
                            v-model="status"
                            class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-600 outline-none focus:border-[#007882] focus:ring-2 focus:ring-[#007882]/20"
                            @change="applyFilters"
                        >
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                        <Input
                            v-model="dateFrom"
                            type="date"
                            class="h-10 rounded-lg border-slate-200 bg-white"
                            @change="applyFilters"
                        />
                        <Input
                            v-model="dateTo"
                            type="date"
                            class="h-10 rounded-lg border-slate-200 bg-white"
                            @change="applyFilters"
                        />
                        <Button
                            type="button"
                            variant="outline"
                            class="h-10 rounded-lg border-slate-200 bg-white px-3 text-slate-600"
                            title="Reset filters"
                            @click="resetFilters"
                        >
                            <RotateCcw class="size-4" />
                        </Button>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <Button
                            type="button"
                            variant="outline"
                            class="h-11 rounded-lg border-rose-200 bg-white px-5 font-bold text-rose-600 hover:bg-rose-50"
                            :disabled="!canBulkAct"
                            @click="openConfirm('reject', selected)"
                        >
                            <XCircle class="size-4" />
                            Reject Selected
                        </Button>
                        <Button
                            type="button"
                            class="h-11 rounded-lg bg-[#007882] px-5 font-bold text-white shadow-md hover:bg-[#006773]"
                            :disabled="!canBulkAct"
                            @click="openConfirm('approve', selected)"
                        >
                            <CheckCircle2 class="size-4" />
                            Approve Selected
                        </Button>
                    </div>
                </div>

                <div
                    class="min-h-[56vh] overflow-hidden rounded-lg border border-slate-100 bg-white shadow-sm"
                >
                    <div v-if="settlements.length > 0" class="overflow-x-auto">
                        <table class="w-full border-collapse text-left">
                            <thead
                                class="bg-slate-50 text-xs font-bold text-slate-600 uppercase"
                            >
                                <tr>
                                    <th class="w-16 px-6 py-4">
                                        <input
                                            type="checkbox"
                                            class="size-4 rounded border-slate-300 text-[#007882]"
                                            :checked="allPageSelected"
                                            @change="togglePageSelection"
                                        />
                                    </th>
                                    <th class="px-6 py-4">No</th>
                                    <th class="px-6 py-4">POS Sale</th>
                                    <th class="px-6 py-4">Date</th>
                                    <th class="px-6 py-4 text-right">Qty</th>
                                    <th class="px-6 py-4 text-right">
                                        Qty to Settle
                                    </th>
                                    <th class="px-6 py-4">Stock Check</th>
                                    <th class="px-6 py-4">Status</th>
                                    <th class="px-6 py-4 text-center">
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                                <tr
                                    v-for="row in paginatedRows"
                                    :key="row.id"
                                    class="transition hover:bg-slate-50/50"
                                >
                                    <td class="px-6 py-4">
                                        <input
                                            v-model="selected"
                                            type="checkbox"
                                            class="size-4 rounded border-slate-300 text-[#007882]"
                                            :value="row.id"
                                            :disabled="row.status !== 'pending'"
                                        />
                                    </td>
                                    <td
                                        class="px-6 py-4 font-mono font-bold text-[#007882]"
                                    >
                                        {{ row.invoiceNo }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="font-semibold text-slate-800">
                                            {{ row.customer }}
                                        </p>
                                        <p
                                            class="mt-1 text-xs font-medium text-slate-400"
                                        >
                                            {{ row.terminal }} /
                                            {{ money(row.grandTotal) }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600">
                                        {{ row.displayDate ?? '-' }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-right font-bold text-[#2a4858]"
                                    >
                                        {{ formatNumber(row.posSaleQty) }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <Input
                                            v-model="qtyToSettle[row.id]"
                                            type="number"
                                            min="0"
                                            step="0.0001"
                                            class="ml-auto h-9 w-28 rounded-lg border-slate-200 bg-slate-50 text-right text-sm"
                                            :disabled="row.status !== 'pending'"
                                        />
                                    </td>
                                    <td class="px-6 py-4">
                                        <p
                                            class="text-xs font-bold text-slate-500"
                                        >
                                            {{ row.requirementCount }}
                                            ingredient line{{
                                                row.requirementCount === 1
                                                    ? ''
                                                    : 's'
                                            }}
                                        </p>
                                        <p
                                            v-if="row.missingBomCount"
                                            class="mt-1 text-xs font-bold text-amber-600"
                                        >
                                            {{ row.missingBomCount }} menu{{
                                                row.missingBomCount === 1
                                                    ? ''
                                                    : 's'
                                            }}
                                            missing BOM
                                        </p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="rounded-full px-2.5 py-1 text-xs font-bold uppercase"
                                            :class="statusClass(row.status)"
                                        >
                                            {{ row.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <DropdownMenu :modal="false">
                                            <DropdownMenuTrigger as-child>
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="icon"
                                                    class="mx-auto flex size-8 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-[#007882]"
                                                    title="Actions"
                                                >
                                                    <MoreVertical
                                                        class="size-4"
                                                    />
                                                    <span class="sr-only">
                                                        Open actions
                                                    </span>
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent
                                                align="end"
                                                class="z-[80] w-40"
                                            >
                                                <DropdownMenuItem
                                                    @select="
                                                        viewSettlement(row)
                                                    "
                                                >
                                                    <Eye
                                                        class="size-4 text-[#007882]"
                                                    />
                                                    View
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem
                                                    :disabled="
                                                        row.status !== 'pending'
                                                    "
                                                    @select="
                                                        openConfirm('approve', [
                                                            row.id,
                                                        ])
                                                    "
                                                >
                                                    <CheckCircle2
                                                        class="size-4 text-emerald-600"
                                                    />
                                                    Approve
                                                </DropdownMenuItem>
                                                <DropdownMenuItem
                                                    :disabled="
                                                        row.status !== 'pending'
                                                    "
                                                    @select="
                                                        openConfirm('reject', [
                                                            row.id,
                                                        ])
                                                    "
                                                >
                                                    <XCircle
                                                        class="size-4 text-rose-600"
                                                    />
                                                    Reject
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div
                        v-else
                        class="flex min-h-[56vh] flex-col items-center justify-center px-4 text-center"
                    >
                        <div
                            class="mb-4 flex size-14 items-center justify-center rounded-full bg-slate-50"
                        >
                            <Filter class="size-6 text-slate-300" />
                        </div>
                        <h3 class="text-lg font-bold text-[#2a4858]">
                            No POS sale settlements found
                        </h3>
                        <p class="mt-2 max-w-md text-sm text-slate-500">
                            Settlements appear after POS invoices are issued and
                            can be approved to deduct inventory automatically.
                        </p>
                    </div>
                </div>

                <TablePagination
                    v-if="settlements.length > 0"
                    class="mt-4"
                    :current-page="currentPage"
                    :total-pages="totalPages"
                    :page-start="pageStart"
                    :page-end="pageEnd"
                    :total-rows="totalRows"
                    :rows-per-page="pageSize"
                    @go-to-page="goToPage"
                    @update-rows-per-page="setRowsPerPage"
                />
            </section>

            <div
                v-if="confirmAction"
                class="fixed inset-0 z-[80] flex items-center justify-center bg-[#2a4858]/25 p-4 backdrop-blur-sm"
                @click.self="closeConfirm"
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
                                            ? 'deduct stock for'
                                            : 'reject'
                                    }}
                                    {{ confirmIds.length }} selected invoice{{
                                        confirmIds.length === 1 ? '' : 's'
                                    }}.
                                </p>
                            </div>
                        </div>
                    </header>
                    <footer class="grid grid-cols-2 gap-3 bg-slate-50 p-5">
                        <Button
                            type="button"
                            variant="outline"
                            class="h-11 rounded-lg"
                            @click="closeConfirm"
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
