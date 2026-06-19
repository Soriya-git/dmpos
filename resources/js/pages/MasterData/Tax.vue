<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Percent, Plus, Save, Search, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import ApprovalActionMenu from '@/components/master-data/ApprovalActionMenu.vue';
import MasterDataTable from '@/components/master-data/MasterDataTable.vue';
import { Button } from '@/components/ui/button';
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

type ApprovalStatus =
    | 'draft'
    | 'pending'
    | 'approved'
    | 'rejected'
    | 'cancelled';

type TaxRecord = {
    id: number;
    code: string;
    name: string;
    company: string;
    rate: string;
    rateLabel: string;
    isDefault: boolean;
    menusCount: number;
    orderLinesCount: number;
    status: ApprovalStatus;
};

const props = defineProps<{
    taxes: TaxRecord[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Master Data' },
    { title: 'Taxes', href: '/master-data/taxes' },
];

const search = ref('');
const panelOpen = ref(false);
const selectedRecord = ref<TaxRecord | null>(null);
const taxes = ref<TaxRecord[]>([...props.taxes]);

const normalizedSearch = computed(() => search.value.trim().toLowerCase());

const filteredTaxes = computed(() => {
    if (!normalizedSearch.value) {
        return taxes.value;
    }

    return taxes.value.filter((tax) =>
        Object.values(tax).some((value) =>
            String(value).toLowerCase().includes(normalizedSearch.value),
        ),
    );
});

const panelTitle = computed(() =>
    selectedRecord.value ? 'Edit Tax' : 'New Tax',
);

function openPanel(record: TaxRecord | null = null) {
    selectedRecord.value = record;
    panelOpen.value = true;
}

function closePanel() {
    panelOpen.value = false;
    selectedRecord.value = null;
}

function statusLabel(status: ApprovalStatus) {
    return status === 'cancelled'
        ? 'Inactive'
        : status.charAt(0).toUpperCase() + status.slice(1);
}

function statusClass(status: ApprovalStatus) {
    const classes: Record<ApprovalStatus, string> = {
        draft: 'border-slate-200 bg-slate-100 text-slate-600',
        pending: 'border-amber-200 bg-amber-50 text-amber-700',
        approved: 'border-[#23AA8F]/20 bg-[#23AA8F]/10 text-[#16836f]',
        rejected: 'border-rose-200 bg-rose-50 text-rose-700',
        cancelled: 'border-slate-300 bg-slate-100 text-slate-500',
    };

    return classes[status];
}

function setTaxStatus(record: TaxRecord, status: ApprovalStatus) {
    record.status = status;
}
</script>

<template>
    <Head title="Taxes" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-[calc(100dvh-4rem)] w-full [scrollbar-gutter:stable] flex-col gap-6 overflow-y-scroll bg-[#f8fafc] p-4 text-slate-800 md:h-[calc(100dvh-5rem)] md:p-6 xl:p-8 2xl:p-10"
        >
            <div
                class="flex flex-col justify-between gap-4 lg:flex-row lg:items-center"
            >
                <div>
                    <h1
                        class="text-2xl font-semibold tracking-tight text-[#2A4858]"
                    >
                        Taxes
                    </h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Tax codes, percentage rates, default rules, and active
                        status.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <div class="relative">
                        <Search
                            class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"
                        />
                        <Input
                            v-model="search"
                            placeholder="Search taxes..."
                            class="h-9 w-52 rounded-lg border-slate-200 bg-white pl-9 text-xs focus-visible:ring-[#007882] lg:w-64"
                        />
                    </div>
                    <Button
                        class="h-9 rounded-lg bg-[#007882] px-4 text-xs font-bold text-white hover:bg-[#006871]"
                        @click="openPanel()"
                    >
                        <Plus class="size-4" />
                        New
                    </Button>
                </div>
            </div>

            <div
                class="flex gap-4 overflow-x-auto border-b border-slate-200 pb-1 whitespace-nowrap"
            >
                <button
                    class="flex items-center gap-2 border-b-3 border-[#23AA8F] px-2 py-2 text-xs font-extrabold tracking-wider text-[#007882] uppercase transition-colors"
                >
                    <Percent class="size-4" />
                    Tax Registry
                </button>
            </div>

            <MasterDataTable
                :rows="filteredTaxes"
                empty-text="No tax data found."
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
                        Tax Code
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Tax Name
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Company
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Rate
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Default
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Usage
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
                    <td class="px-4 py-3 text-sm font-bold text-slate-700">
                        {{ row.name }}
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-500">
                        {{ row.company }}
                    </td>
                    <td class="px-4 py-3 text-xs font-bold text-amber-700">
                        {{ row.rateLabel }}
                    </td>
                    <td class="px-4 py-3">
                        <span
                            class="rounded bg-blue-50 px-2 py-0.5 text-[10px] font-bold text-blue-600"
                        >
                            {{ row.isDefault ? 'Default' : 'Optional' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-500">
                        {{ row.menusCount }} menus /
                        {{ row.orderLinesCount }} lines
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
                        <ApprovalActionMenu
                            :status="row.status"
                            @view="openPanel(row)"
                            @approve="setTaxStatus(row, 'approved')"
                            @reject="setTaxStatus(row, 'rejected')"
                            @cancel="setTaxStatus(row, 'cancelled')"
                        />
                    </td>
                </template>
            </MasterDataTable>
        </div>

        <Sheet :open="panelOpen" @update:open="(open) => !open && closePanel()">
            <SheetContent class="w-full gap-0 p-0 sm:max-w-[450px]">
                <SheetHeader class="bg-[#2A4858] p-6 text-white">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <SheetTitle class="text-lg font-bold text-white">
                                {{ panelTitle }}
                            </SheetTitle>
                            <p
                                class="mt-1 text-[10px] tracking-widest text-white/50 uppercase"
                            >
                                Tax percentage and default master data
                            </p>
                        </div>
                        <Button
                            variant="ghost"
                            size="icon"
                            class="size-7 rounded-full bg-white/10 text-white hover:bg-white/20 hover:text-white"
                            @click="closePanel"
                        >
                            <X class="size-4" />
                            <span class="sr-only">Close panel</span>
                        </Button>
                    </div>
                </SheetHeader>

                <div class="flex-1 space-y-4 overflow-y-auto p-6">
                    <div class="space-y-3 rounded-lg bg-slate-50 p-4">
                        <label class="block">
                            <span
                                class="text-[10px] font-bold text-slate-400 uppercase"
                            >
                                Tax Code
                            </span>
                            <Input
                                :model-value="selectedRecord?.code ?? ''"
                                class="mt-1 font-mono text-sm focus-visible:ring-[#007882]"
                                placeholder="VAT_10"
                            />
                        </label>

                        <label class="block">
                            <span
                                class="text-[10px] font-bold text-slate-400 uppercase"
                            >
                                Tax Name
                            </span>
                            <Input
                                :model-value="selectedRecord?.name ?? ''"
                                class="mt-1 text-sm focus-visible:ring-[#007882]"
                                placeholder="VAT 10%"
                            />
                        </label>

                        <div
                            class="mt-2 space-y-3 border-t border-slate-200 pt-2"
                        >
                            <label class="block">
                                <span
                                    class="text-[10px] font-bold text-slate-400 uppercase"
                                >
                                    Rate
                                </span>
                                <Input
                                    :model-value="selectedRecord?.rate ?? ''"
                                    class="mt-1 font-bold text-[#007882] focus-visible:ring-[#007882]"
                                    placeholder="10.0000"
                                />
                            </label>
                            <label class="block">
                                <span
                                    class="text-[10px] font-bold text-slate-400 uppercase"
                                >
                                    Default
                                </span>
                                <select
                                    class="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus:border-[#007882]"
                                >
                                    <option>Default</option>
                                    <option>Optional</option>
                                </select>
                            </label>
                        </div>

                        <label class="block">
                            <span
                                class="text-[10px] font-bold text-slate-400 uppercase"
                            >
                                Status
                            </span>
                            <select
                                class="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus:border-[#007882]"
                            >
                                <option>Active</option>
                                <option>Inactive</option>
                            </select>
                        </label>
                    </div>
                </div>

                <SheetFooter class="flex-row gap-3 border-t p-6">
                    <Button
                        variant="outline"
                        class="flex-1 rounded-lg text-xs font-bold text-slate-500"
                        @click="closePanel"
                    >
                        Cancel
                    </Button>
                    <Button
                        class="flex-1 rounded-lg bg-[#007882] text-xs font-bold text-white hover:bg-[#006871]"
                        @click="closePanel"
                    >
                        <Save class="size-4" />
                        Save Changes
                    </Button>
                </SheetFooter>
            </SheetContent>
        </Sheet>
    </AppLayout>
</template>
