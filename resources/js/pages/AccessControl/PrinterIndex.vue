<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    Cable,
    CheckCircle2,
    Filter,
    Pencil,
    Plus,
    RotateCcw,
    Search,
    Star,
    XCircle,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import TablePagination from '@/components/TablePagination.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { usePagination } from '@/composables/usePagination';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import PrinterCreate, { type PrinterRecord } from './PrinterCreate.vue';

type BranchOption = {
    id: number;
    name: string;
    code: string | null;
};

const props = defineProps<{
    printers: PrinterRecord[];
    branchOptions: BranchOption[];
    defaultBranchId: number | null;
}>();

const view = ref<'list' | 'form'>('list');
const selectedPrinter = ref<PrinterRecord | null>(null);
const createRef = ref<InstanceType<typeof PrinterCreate> | null>(null);
const search = ref('');

const breadcrumbs = computed<BreadcrumbItem[]>(() =>
    view.value === 'form'
        ? [
              { title: 'Acess Control' },
              {
                  title: 'Manage Printer',
                  href: '/access-control/printers',
              },
              { title: selectedPrinter.value ? 'Edit' : 'Create' },
          ]
        : [
              { title: 'Acess Control' },
              {
                  title: 'Manage Printer',
                  href: '/access-control/printers',
              },
          ],
);

const totalPrinters = computed(() => props.printers.length);
const activePrinters = computed(
    () => props.printers.filter((printer) => printer.isActive).length,
);
const defaultPrinters = computed(
    () => props.printers.filter((printer) => printer.isDefault).length,
);
const networkPrinters = computed(
    () =>
        props.printers.filter((printer) => printer.connectionType === 'network')
            .length,
);

const filteredPrinters = computed(() => {
    const q = search.value.trim().toLowerCase();

    if (!q) {
        return props.printers;
    }

    return props.printers.filter((printer) =>
        [
            printer.name,
            printer.code,
            printer.branch,
            printer.printerType,
            printer.printerRole,
            printer.connectionType,
            printer.ipAddress,
            printer.hostName,
            printer.paperSize,
            printer.description,
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
} = usePagination(() => filteredPrinters.value, 10);

function showCreate() {
    selectedPrinter.value = null;
    view.value = 'form';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function showEdit(printer: PrinterRecord) {
    selectedPrinter.value = printer;
    view.value = 'form';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function showList() {
    selectedPrinter.value = null;
    view.value = 'list';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function resetSearch() {
    search.value = '';
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

function connectionLabel(printer: PrinterRecord) {
    if (printer.connectionType === 'network') {
        const host = printer.ipAddress || printer.hostName || 'No host';
        return `${host}:${printer.port ?? 9100}`;
    }

    return humanLabel(printer.connectionType);
}
</script>

<template>
    <Head title="Manage Printer" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #actions>
            <template v-if="view === 'list'">
                <Button
                    type="button"
                    class="h-9 rounded-lg bg-[#007882] px-4 text-xs font-bold text-white shadow-md hover:bg-[#006773]"
                    @click="showCreate"
                >
                    <Plus class="size-4" />
                    New Printer
                </Button>
            </template>
            <template v-else>
                <div class="flex gap-2">
                    <Button
                        type="button"
                        variant="ghost"
                        class="h-9 font-semibold text-slate-600 hover:text-red-500"
                        :disabled="createRef?.isProcessing"
                        @click="showList"
                    >
                        Cancel
                    </Button>
                    <Button
                        type="button"
                        class="h-9 rounded-lg bg-[#007882] px-4 text-xs font-bold text-white shadow-md hover:bg-[#006773]"
                        :disabled="createRef?.isProcessing"
                        @click="createRef?.submit()"
                    >
                        {{
                            selectedPrinter ? 'Save Printer' : 'Create Printer'
                        }}
                    </Button>
                </div>
            </template>
        </template>

        <main
            class="h-[calc(100dvh-4rem)] w-full scrollbar-gutter-stable overflow-y-scroll bg-[#f8fafc] p-4 text-slate-800 md:h-[calc(100dvh-5rem)] md:p-6 xl:p-8 2xl:p-10"
        >
            <section v-if="view === 'list'" class="w-full">
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
                            Total Printers
                        </p>
                        <h3 class="mt-1 text-2xl font-bold">
                            {{ totalPrinters }}
                        </h3>
                        <p class="mt-1.5 text-xs text-slate-400">
                            Configured devices
                        </p>
                    </div>
                    <div
                        class="rounded-lg border-l-4 border-[#23aa8f] bg-white p-5 shadow-sm"
                    >
                        <p class="text-xs font-bold text-slate-500 uppercase">
                            Active
                        </p>
                        <h3 class="mt-1 text-2xl font-bold text-[#23aa8f]">
                            {{ activePrinters }}
                        </h3>
                        <p class="mt-1.5 text-xs text-slate-400">
                            Available for print routing
                        </p>
                    </div>
                    <div
                        class="rounded-lg border-l-4 border-amber-400 bg-white p-5 shadow-sm"
                    >
                        <p class="text-xs font-bold text-slate-500 uppercase">
                            Defaults
                        </p>
                        <h3 class="mt-1 text-2xl font-bold text-amber-600">
                            {{ defaultPrinters }}
                        </h3>
                        <p class="mt-1.5 text-xs text-slate-400">
                            Branch role defaults
                        </p>
                    </div>
                    <div
                        class="rounded-lg border-l-4 border-blue-400 bg-white p-5 shadow-sm"
                    >
                        <p class="text-xs font-bold text-slate-500 uppercase">
                            Network
                        </p>
                        <h3 class="mt-1 text-2xl font-bold text-blue-600">
                            {{ networkPrinters }}
                        </h3>
                        <p class="mt-1.5 text-xs text-slate-400">
                            IP or host routed printers
                        </p>
                    </div>
                </div>

                <div
                    class="min-h-[56vh] overflow-hidden rounded-lg border border-slate-100 bg-white shadow-sm"
                >
                    <div
                        class="flex items-center gap-2 border-b border-slate-100 px-4 py-3"
                    >
                        <div class="relative w-full max-w-sm">
                            <Search
                                class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"
                            />
                            <Input
                                v-model="search"
                                type="text"
                                placeholder="Search printer, branch, role, host..."
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

                    <div
                        v-if="filteredPrinters.length > 0"
                        class="overflow-x-auto"
                    >
                        <table class="w-full border-collapse text-left">
                            <thead
                                class="bg-slate-50 text-xs font-bold text-slate-600 uppercase"
                            >
                                <tr>
                                    <th class="w-12 px-6 py-4 text-center">
                                        #
                                    </th>
                                    <th class="px-6 py-4">Printer</th>
                                    <th class="px-6 py-4">Branch</th>
                                    <th class="px-6 py-4">Role</th>
                                    <th class="px-6 py-4">Connection</th>
                                    <th class="px-6 py-4">Paper</th>
                                    <th class="px-6 py-4">Status</th>
                                    <th class="px-6 py-4 text-center">
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                                <tr
                                    v-for="(row, index) in paginatedRows"
                                    :key="row.id"
                                    class="transition hover:bg-slate-50/50"
                                >
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
                                            class="font-semibold text-slate-800"
                                        >
                                            {{ row.name }}
                                        </div>
                                        <div
                                            class="mt-1 font-mono text-xs font-bold text-[#007882]"
                                        >
                                            {{ row.code ?? 'No code' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-slate-500">
                                        {{ row.branch }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div
                                            class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600 uppercase"
                                        >
                                            {{ humanLabel(row.printerRole) }}
                                        </div>
                                        <div
                                            class="mt-1 text-xs text-slate-400"
                                        >
                                            {{ humanLabel(row.printerType) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div
                                            class="flex items-center gap-2 font-semibold text-slate-700"
                                        >
                                            <Cable
                                                class="size-4 text-slate-400"
                                            />
                                            {{ connectionLabel(row) }}
                                        </div>
                                        <div
                                            class="mt-1 text-xs text-slate-400"
                                        >
                                            {{
                                                humanLabel(row.networkProtocol)
                                            }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-slate-500">
                                        {{ row.paperSize }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col gap-1.5">
                                            <span
                                                class="inline-flex w-fit items-center gap-1 rounded-full px-2.5 py-1 text-xs font-bold uppercase"
                                                :class="
                                                    row.isActive
                                                        ? 'bg-green-100 text-green-700'
                                                        : 'bg-slate-100 text-slate-500'
                                                "
                                            >
                                                <CheckCircle2
                                                    v-if="row.isActive"
                                                    class="size-3.5"
                                                />
                                                <XCircle
                                                    v-else
                                                    class="size-3.5"
                                                />
                                                {{
                                                    row.isActive
                                                        ? 'Active'
                                                        : 'Inactive'
                                                }}
                                            </span>
                                            <span
                                                v-if="row.isDefault"
                                                class="inline-flex w-fit items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-700 uppercase"
                                            >
                                                <Star class="size-3.5" />
                                                Default
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <Button
                                            type="button"
                                            variant="outline"
                                            class="h-8 rounded-lg border-slate-200 px-3 text-xs font-bold text-slate-600"
                                            @click="showEdit(row)"
                                        >
                                            <Pencil class="size-3.5" />
                                            Edit
                                        </Button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <TablePagination
                        v-if="filteredPrinters.length > 0"
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
                            No printers found
                        </h3>
                        <p class="mt-1 text-sm text-slate-500">
                            Add a new printer or adjust your search.
                        </p>
                    </div>
                </div>
            </section>

            <PrinterCreate
                v-else
                ref="createRef"
                :branch-options="branchOptions"
                :default-branch-id="defaultBranchId"
                :printer="selectedPrinter"
                @success="showList"
            />
        </main>
    </AppLayout>
</template>
