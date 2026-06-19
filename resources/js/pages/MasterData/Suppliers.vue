<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Filter, Plus, RotateCcw, Search } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import ApprovalActionMenu from '@/components/master-data/ApprovalActionMenu.vue';
import TablePagination from '@/components/TablePagination.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { usePagination } from '@/composables/usePagination';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import SupplierCreate from './SupplierCreate.vue';

type ApprovalStatus =
    | 'draft'
    | 'pending'
    | 'approved'
    | 'rejected'
    | 'cancelled';

type SupplierRecord = {
    id: number;
    code: string;
    name: string;
    contactPerson: string | null;
    phone: string | null;
    email: string | null;
    address: string | null;
    status: ApprovalStatus;
};

const props = defineProps<{
    suppliers: SupplierRecord[];
}>();

const view = ref<'list' | 'create'>('list');
const createRef = ref<InstanceType<typeof SupplierCreate> | null>(null);
const search = ref('');

const breadcrumbs = computed<BreadcrumbItem[]>(() =>
    view.value === 'create'
        ? [
              { title: 'Master Data' },
              { title: 'Suppliers', href: '/master-data/suppliers' },
              { title: 'Create' },
          ]
        : [
              { title: 'Master Data' },
              { title: 'Suppliers', href: '/master-data/suppliers' },
          ],
);

const totalSuppliers = computed(() => props.suppliers.length);
const activeSuppliers = computed(
    () => props.suppliers.filter((s) => s.status === 'approved').length,
);
const pendingSuppliers = computed(
    () =>
        props.suppliers.filter((s) =>
            ['draft', 'pending'].includes(s.status),
        ).length,
);
const inactiveSuppliers = computed(
    () =>
        props.suppliers.filter((s) =>
            ['rejected', 'cancelled'].includes(s.status),
        ).length,
);

const activePercent = computed(() =>
    totalSuppliers.value
        ? Math.round((activeSuppliers.value / totalSuppliers.value) * 100)
        : 0,
);
const pendingPercent = computed(() =>
    totalSuppliers.value
        ? Math.round((pendingSuppliers.value / totalSuppliers.value) * 100)
        : 0,
);
const inactivePercent = computed(() =>
    totalSuppliers.value
        ? Math.round((inactiveSuppliers.value / totalSuppliers.value) * 100)
        : 0,
);

const filteredSuppliers = computed(() => {
    const q = search.value.trim().toLowerCase();
    if (!q) return props.suppliers;

    return props.suppliers.filter((s) =>
        [s.code, s.name, s.contactPerson, s.phone, s.email]
            .filter(Boolean)
            .some((v) => String(v).toLowerCase().includes(q)),
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
} = usePagination(() => filteredSuppliers.value, 10);

function statusLabel(status: ApprovalStatus) {
    if (status === 'cancelled') return 'Inactive';
    return status.charAt(0).toUpperCase() + status.slice(1);
}

function statusClass(status: ApprovalStatus) {
    const classes: Record<ApprovalStatus, string> = {
        draft: 'bg-slate-100 text-slate-600',
        pending: 'bg-amber-100 text-amber-700',
        approved: 'bg-green-100 text-green-700',
        rejected: 'bg-red-100 text-red-700',
        cancelled: 'bg-slate-100 text-slate-500',
    };
    return classes[status];
}

function showCreate() {
    view.value = 'create';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function showList() {
    view.value = 'list';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function resetSearch() {
    search.value = '';
}

function updateStatus(
    supplier: SupplierRecord,
    action: 'approve' | 'reject' | 'cancel',
) {
    router.patch(
        `/master-data/suppliers/${supplier.id}/${action}`,
        {},
        { preserveScroll: true, preserveState: false },
    );
}
</script>

<template>
    <Head title="Suppliers" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #actions>
            <template v-if="view === 'list'">
                <Button
                    type="button"
                    class="h-9 rounded-lg bg-[#007882] px-4 text-xs font-bold text-white shadow-md hover:bg-[#006773]"
                    @click="showCreate"
                >
                    <Plus class="size-4" />
                    New Supplier
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
                        Save Supplier
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

                <!-- Stat cards -->
                <div
                    class="mb-8 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4 2xl:gap-6"
                >
                    <div
                        class="rounded-lg border-l-4 border-[#007882] bg-white p-5 shadow-sm"
                    >
                        <p class="text-xs font-bold text-slate-500 uppercase">
                            Total Suppliers
                        </p>
                        <h3 class="mt-1 text-2xl font-bold">
                            {{ totalSuppliers }}
                        </h3>
                        <p class="mt-1.5 text-xs text-slate-400">
                            Registered suppliers
                        </p>
                    </div>
                    <div
                        class="rounded-lg border-l-4 border-[#23aa8f] bg-white p-5 shadow-sm"
                    >
                        <p class="text-xs font-bold text-slate-500 uppercase">
                            Active
                        </p>
                        <h3 class="mt-1 text-2xl font-bold text-[#23aa8f]">
                            {{ activeSuppliers }}
                        </h3>
                        <p class="mt-1.5 text-xs text-slate-400">
                            {{ activePercent }}% of total suppliers
                        </p>
                    </div>
                    <div
                        class="rounded-lg border-l-4 border-amber-400 bg-white p-5 shadow-sm"
                    >
                        <p class="text-xs font-bold text-slate-500 uppercase">
                            Pending
                        </p>
                        <h3 class="mt-1 text-2xl font-bold text-amber-600">
                            {{ pendingSuppliers }}
                        </h3>
                        <p class="mt-1.5 text-xs text-slate-400">
                            {{ pendingPercent }}% of total suppliers
                        </p>
                    </div>
                    <div
                        class="rounded-lg border-l-4 border-red-400 bg-white p-5 shadow-sm"
                    >
                        <p class="text-xs font-bold text-slate-500 uppercase">
                            Inactive
                        </p>
                        <h3 class="mt-1 text-2xl font-bold text-red-500">
                            {{ inactiveSuppliers }}
                        </h3>
                        <p class="mt-1.5 text-xs text-slate-400">
                            {{ inactivePercent }}% of total suppliers
                        </p>
                    </div>
                </div>

                <!-- Table -->
                <div
                    class="min-h-[56vh] overflow-hidden rounded-lg border border-slate-100 bg-white shadow-sm"
                >
                    <!-- Toolbar -->
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
                                placeholder="Search code, name, phone, email..."
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
                        v-if="filteredSuppliers.length > 0"
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
                                    <th class="px-6 py-4">Code</th>
                                    <th class="px-6 py-4">Supplier Name</th>
                                    <th class="px-6 py-4">Contact Person</th>
                                    <th class="px-6 py-4">Phone</th>
                                    <th class="px-6 py-4">Email</th>
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
                                        {{ String(pageStart + index).padStart(2, '0') }}
                                    </td>
                                    <td
                                        class="px-6 py-4 font-mono text-xs font-bold text-[#007882]"
                                    >
                                        {{ row.code }}
                                    </td>
                                    <td
                                        class="px-6 py-4 font-semibold text-slate-800"
                                    >
                                        {{ row.name }}
                                    </td>
                                    <td class="px-6 py-4 text-slate-500">
                                        {{ row.contactPerson ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-slate-500">
                                        {{ row.phone ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-slate-500">
                                        {{ row.email ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="rounded-full px-2.5 py-1 text-xs font-bold uppercase"
                                            :class="statusClass(row.status)"
                                        >
                                            {{ statusLabel(row.status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <ApprovalActionMenu
                                            :status="row.status"
                                            view-label="View"
                                            :actionable-statuses="[
                                                'draft',
                                                'pending',
                                            ]"
                                            @approve="
                                                updateStatus(row, 'approve')
                                            "
                                            @reject="updateStatus(row, 'reject')"
                                            @cancel="updateStatus(row, 'cancel')"
                                        />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <TablePagination
                        v-if="filteredSuppliers.length > 0"
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
                            No suppliers found
                        </h3>
                        <p class="mt-1 text-sm text-slate-500">
                            Add a new supplier or adjust your search.
                        </p>
                    </div>
                </div>
            </section>

            <SupplierCreate
                v-else
                ref="createRef"
                @success="showList"
            />
        </main>
    </AppLayout>
</template>
