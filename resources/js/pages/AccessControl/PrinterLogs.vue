<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    AlertTriangle,
    CheckCircle2,
    Clock,
    Trash2,
    Filter,
    Printer,
    RotateCcw,
    Search,
    XCircle,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import TablePagination from '@/components/TablePagination.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { usePagination } from '@/composables/usePagination';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

type PrintLogRecord = {
    id: number;
    jobNo: string;
    jobType: string;
    status: 'pending' | 'printed' | 'failed' | 'cancelled';
    branch: string;
    printer: string;
    printerRole: string | null;
    referenceType: string | null;
    referenceNo: string | null;
    printCount: number;
    printedAt: string | null;
    printedBy: string | null;
    errorMessage: string | null;
    createdAt: string | null;
};

const props = defineProps<{
    logs: PrintLogRecord[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Acess Control' },
    { title: 'Printer Log', href: '/access-control/printer-logs' },
];

const search = ref('');
const selectedLogIds = ref<number[]>([]);

const totalLogs = computed(() => props.logs.length);
const printedLogs = computed(
    () => props.logs.filter((log) => log.status === 'printed').length,
);
const failedLogs = computed(
    () => props.logs.filter((log) => log.status === 'failed').length,
);
const pendingLogs = computed(
    () => props.logs.filter((log) => log.status === 'pending').length,
);

const filteredLogs = computed(() => {
    const q = search.value.trim().toLowerCase();

    if (!q) {
        return props.logs;
    }

    return props.logs.filter((log) =>
        [
            log.jobNo,
            log.jobType,
            log.status,
            log.branch,
            log.printer,
            log.printerRole,
            log.referenceType,
            log.referenceNo,
            log.printedBy,
            log.errorMessage,
        ]
            .filter(Boolean)
            .some((value) => String(value).toLowerCase().includes(q)),
    );
});

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
} = usePagination(() => filteredLogs.value, 10);

const selectedCount = computed(() => selectedLogIds.value.length);
const paginatedIds = computed(() => paginatedRows.value.map((log) => log.id));
const allPageSelected = computed(
    () =>
        paginatedIds.value.length > 0 &&
        paginatedIds.value.every((id) => selectedLogIds.value.includes(id)),
);

watch(
    () => props.logs,
    () => {
        const visibleIds = new Set(props.logs.map((log) => log.id));
        selectedLogIds.value = selectedLogIds.value.filter((id) =>
            visibleIds.has(id),
        );
    },
);

function resetSearch() {
    search.value = '';
}

function toggleLog(id: number) {
    selectedLogIds.value = selectedLogIds.value.includes(id)
        ? selectedLogIds.value.filter((selectedId) => selectedId !== id)
        : [...selectedLogIds.value, id];
}

function togglePageSelection() {
    if (allPageSelected.value) {
        selectedLogIds.value = selectedLogIds.value.filter(
            (id) => !paginatedIds.value.includes(id),
        );
        return;
    }

    selectedLogIds.value = Array.from(
        new Set([...selectedLogIds.value, ...paginatedIds.value]),
    );
}

function deleteSelectedLogs() {
    if (selectedCount.value === 0) {
        return;
    }

    if (
        !window.confirm(
            `Delete ${selectedCount.value} selected printer log(s)? This removes the record from the database.`,
        )
    ) {
        return;
    }

    router.delete('/access-control/printer-logs', {
        data: {
            ids: selectedLogIds.value,
        },
        preserveScroll: true,
        preserveState: false,
    });
}

function humanLabel(value: string | null) {
    if (!value) {
        return '-';
    }

    return value
        .split('_')
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join(' ');
}

function statusClass(status: PrintLogRecord['status']) {
    const classes: Record<PrintLogRecord['status'], string> = {
        pending: 'bg-amber-100 text-amber-700',
        printed: 'bg-green-100 text-green-700',
        failed: 'bg-red-100 text-red-700',
        cancelled: 'bg-slate-100 text-slate-500',
    };

    return classes[status];
}
</script>

<template>
    <Head title="Printer Log" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <main
            class="h-[calc(100dvh-4rem)] w-full scrollbar-gutter-stable overflow-y-scroll bg-[#f8fafc] p-4 text-slate-800 md:h-[calc(100dvh-5rem)] md:p-6 xl:p-8 2xl:p-10"
        >
            <section class="w-full">
                <div
                    v-if="($page.props.flash as any)?.success"
                    class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-[#2a4858]"
                >
                    {{ ($page.props.flash as any).success }}
                </div>

                <div
                    class="mb-8 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4 2xl:gap-6"
                >
                    <div
                        class="rounded-lg border-l-4 border-[#007882] bg-white p-5 shadow-sm"
                    >
                        <p class="text-xs font-bold text-slate-500 uppercase">
                            Total Jobs
                        </p>
                        <h3 class="mt-1 text-2xl font-bold">
                            {{ totalLogs }}
                        </h3>
                        <p class="mt-1.5 text-xs text-slate-400">
                            Latest 250 print jobs
                        </p>
                    </div>
                    <div
                        class="rounded-lg border-l-4 border-[#23aa8f] bg-white p-5 shadow-sm"
                    >
                        <p class="text-xs font-bold text-slate-500 uppercase">
                            Printed
                        </p>
                        <h3 class="mt-1 text-2xl font-bold text-[#23aa8f]">
                            {{ printedLogs }}
                        </h3>
                        <p class="mt-1.5 text-xs text-slate-400">
                            Completed jobs
                        </p>
                    </div>
                    <div
                        class="rounded-lg border-l-4 border-amber-400 bg-white p-5 shadow-sm"
                    >
                        <p class="text-xs font-bold text-slate-500 uppercase">
                            Pending
                        </p>
                        <h3 class="mt-1 text-2xl font-bold text-amber-600">
                            {{ pendingLogs }}
                        </h3>
                        <p class="mt-1.5 text-xs text-slate-400">
                            Waiting to print
                        </p>
                    </div>
                    <div
                        class="rounded-lg border-l-4 border-red-400 bg-white p-5 shadow-sm"
                    >
                        <p class="text-xs font-bold text-slate-500 uppercase">
                            Failed
                        </p>
                        <h3 class="mt-1 text-2xl font-bold text-red-500">
                            {{ failedLogs }}
                        </h3>
                        <p class="mt-1.5 text-xs text-slate-400">
                            Jobs with errors
                        </p>
                    </div>
                </div>

                <div
                    class="min-h-[56vh] overflow-hidden rounded-lg border border-slate-100 bg-white shadow-sm"
                >
                    <div
                        class="flex flex-col gap-3 border-b border-slate-100 px-4 py-3 lg:flex-row lg:items-center lg:justify-between"
                    >
                        <div class="flex items-center gap-2">
                            <div class="relative w-full max-w-sm">
                                <Search
                                    class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"
                                />
                                <Input
                                    v-model="search"
                                    type="text"
                                    placeholder="Search job, printer, status, reference..."
                                    class="h-9 rounded-lg border-slate-200 bg-white pl-10 text-sm focus-visible:ring-[#007882]/30"
                                />
                            </div>
                            <Button
                                type="button"
                                variant="outline"
                                class="h-9 rounded-lg border-slate-200 bg-white px-3 text-slate-600"
                                title="Reset search"
                                @click="resetSearch"
                            >
                                <RotateCcw class="size-4" />
                            </Button>
                        </div>

                        <div class="flex items-center gap-2">
                            <span
                                v-if="selectedCount > 0"
                                class="text-xs font-bold text-slate-500"
                            >
                                {{ selectedCount }} selected
                            </span>
                            <Button
                                type="button"
                                variant="outline"
                                class="h-9 rounded-lg border-red-200 bg-white px-3 text-xs font-bold text-red-600 hover:bg-red-50"
                                :disabled="selectedCount === 0"
                                @click="deleteSelectedLogs"
                            >
                                <Trash2 class="size-4" />
                                Delete Selected
                            </Button>
                        </div>
                    </div>

                    <div v-if="filteredLogs.length > 0" class="overflow-x-auto">
                        <table class="w-full border-collapse text-left">
                            <thead
                                class="bg-slate-50 text-xs font-bold text-slate-600 uppercase"
                            >
                                <tr>
                                    <th class="w-10 px-4 py-4 text-center">
                                        <input
                                            type="checkbox"
                                            class="size-4 rounded border-slate-300 text-[#007882] focus:ring-[#007882]/30"
                                            :checked="allPageSelected"
                                            @change="togglePageSelection"
                                        />
                                    </th>
                                    <th class="w-12 px-6 py-4 text-center">
                                        #
                                    </th>
                                    <th class="px-6 py-4">Job</th>
                                    <th class="px-6 py-4">Printer</th>
                                    <th class="px-6 py-4">Reference</th>
                                    <th class="px-6 py-4">Printed</th>
                                    <th class="px-6 py-4">Status</th>
                                    <th class="px-6 py-4">Error</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                                <tr
                                    v-for="(row, index) in paginatedRows"
                                    :key="row.id"
                                    class="transition hover:bg-slate-50/50"
                                >
                                    <td class="px-4 py-4 text-center">
                                        <input
                                            type="checkbox"
                                            class="size-4 rounded border-slate-300 text-[#007882] focus:ring-[#007882]/30"
                                            :checked="
                                                selectedLogIds.includes(row.id)
                                            "
                                            @change="toggleLog(row.id)"
                                        />
                                    </td>
                                    <td
                                        class="px-6 py-4 text-center text-xs text-slate-400"
                                    >
                                        {{
                                            String(
                                                pageStart + index + 1,
                                            ).padStart(2, '0')
                                        }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div
                                            class="font-mono text-xs font-bold text-[#007882]"
                                        >
                                            {{ row.jobNo }}
                                        </div>
                                        <div
                                            class="mt-1 text-xs font-semibold text-slate-500"
                                        >
                                            {{ humanLabel(row.jobType) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div
                                            class="flex items-center gap-2 font-semibold text-slate-800"
                                        >
                                            <Printer
                                                class="size-4 text-slate-400"
                                            />
                                            {{ row.printer }}
                                        </div>
                                        <div
                                            class="mt-1 text-xs text-slate-400"
                                        >
                                            {{ row.branch }} /
                                            {{ humanLabel(row.printerRole) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div
                                            class="font-semibold text-slate-700"
                                        >
                                            {{ row.referenceNo ?? '-' }}
                                        </div>
                                        <div
                                            class="mt-1 text-xs text-slate-400"
                                        >
                                            {{ humanLabel(row.referenceType) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div
                                            class="flex items-center gap-2 text-slate-600"
                                        >
                                            <Clock
                                                class="size-4 text-slate-400"
                                            />
                                            {{
                                                row.printedAt ??
                                                row.createdAt ??
                                                '-'
                                            }}
                                        </div>
                                        <div
                                            class="mt-1 text-xs text-slate-400"
                                        >
                                            {{ row.printedBy ?? 'System' }} /
                                            {{ row.printCount }} time(s)
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-bold uppercase"
                                            :class="statusClass(row.status)"
                                        >
                                            <CheckCircle2
                                                v-if="row.status === 'printed'"
                                                class="size-3.5"
                                            />
                                            <AlertTriangle
                                                v-else-if="
                                                    row.status === 'pending'
                                                "
                                                class="size-3.5"
                                            />
                                            <XCircle v-else class="size-3.5" />
                                            {{ row.status }}
                                        </span>
                                    </td>
                                    <td
                                        class="max-w-xs px-6 py-4 text-xs text-red-500"
                                    >
                                        {{ row.errorMessage ?? '-' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <TablePagination
                        v-if="filteredLogs.length > 0"
                        :current-page="currentPage"
                        :total-pages="totalPages"
                        :total-rows="totalRows"
                        :page-start="pageStart"
                        :page-end="pageEnd"
                        :rows-per-page="pageSize"
                        @go-to-page="goToPage"
                        @update-rows-per-page="setRowsPerPage"
                    />

                    <div v-else class="p-16 text-center">
                        <div
                            class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-slate-50"
                        >
                            <Filter class="size-6 text-slate-300" />
                        </div>
                        <h3 class="font-bold text-[#2a4858]">
                            No printer logs found
                        </h3>
                        <p class="mt-1 text-sm text-slate-500">
                            Print jobs will appear here after tickets, invoices,
                            or receipts are generated.
                        </p>
                    </div>
                </div>
            </section>
        </main>
    </AppLayout>
</template>
