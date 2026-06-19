<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Building2, GitBranch, Plus, Save, Search, X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
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

type OrganizationView = 'companies' | 'branches';

type CompanyRecord = {
    id: number;
    code: string;
    name: string;
    email: string | null;
    phone: string | null;
    address: string | null;
    branchesCount: number;
    status: ApprovalStatus;
};

type BranchRecord = {
    id: number;
    code: string;
    name: string;
    company: string;
    companyId: number;
    phone: string | null;
    vatNumber: string | null;
    address: string | null;
    logo: string | null;
    logoUrl: string | null;
    paymentQrcode: string | null;
    paymentQrcodeUrl: string | null;
    status: ApprovalStatus;
};

type PanelRecord = CompanyRecord | BranchRecord;

const props = defineProps<{
    companies: CompanyRecord[];
    branches: BranchRecord[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Master Data' },
    { title: 'Company & Branch', href: '/master-data/company-branches' },
];

const activeView = ref<OrganizationView>('companies');
const search = ref('');
const panelOpen = ref(false);
const panelKind = ref<OrganizationView>('companies');
const selectedRecord = ref<PanelRecord | null>(null);
const companies = ref<CompanyRecord[]>([...props.companies]);
const branches = ref<BranchRecord[]>([...props.branches]);

const branchForm = useForm({
    name: '',
    code: '',
    company_id: '',
    phone: '',
    vat_number: '',
    address: '',
    is_active: true,
    logo: null as File | null,
    payment_qrcode: null as File | null,
});

const logoPreview = ref<string | null>(null);
const paymentQrcodePreview = ref<string | null>(null);

const tabs: {
    key: OrganizationView;
    label: string;
    icon: typeof Building2;
}[] = [
    { key: 'companies', label: 'Company', icon: Building2 },
    { key: 'branches', label: 'Branch', icon: GitBranch },
];

const normalizedSearch = computed(() => search.value.trim().toLowerCase());
const filteredCompanies = computed(() => filterRows(companies.value));
const filteredBranches = computed(() => filterRows(branches.value));

watch(
    () => props.companies,
    (nextCompanies) => {
        companies.value = [...nextCompanies];
    },
);

watch(
    () => props.branches,
    (nextBranches) => {
        branches.value = [...nextBranches];
    },
);

const panelTitle = computed(() =>
    selectedRecord.value
        ? `Edit ${panelKind.value === 'companies' ? 'Company' : 'Branch'}`
        : `New ${panelKind.value === 'companies' ? 'Company' : 'Branch'}`,
);

const panelSubtitle = computed(() =>
    panelKind.value === 'companies'
        ? 'Company profile and head office master data'
        : 'Branch location and operating master data',
);

function filterRows<T extends Record<string, unknown>>(rows: T[]) {
    if (!normalizedSearch.value) {
        return rows;
    }

    return rows.filter((row) =>
        Object.values(row).some((value) =>
            String(value).toLowerCase().includes(normalizedSearch.value),
        ),
    );
}

function switchView(view: OrganizationView) {
    activeView.value = view;
    panelKind.value = view;
}

function openPanel(
    kind: OrganizationView = activeView.value,
    record: PanelRecord | null = null,
) {
    panelKind.value = kind;
    selectedRecord.value = record;
    resetForms(record);
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

function setCompanyStatus(record: CompanyRecord, status: ApprovalStatus) {
    record.status = status;
}

function setBranchStatus(record: BranchRecord, status: ApprovalStatus) {
    record.status = status;
}

function resetForms(record: PanelRecord | null) {
    branchForm.reset();
    branchForm.clearErrors();
    logoPreview.value = null;
    paymentQrcodePreview.value = null;

    if (panelKind.value !== 'branches' || !record || !('companyId' in record)) {
        return;
    }

    branchForm.name = record.name;
    branchForm.code = record.code;
    branchForm.company_id = String(record.companyId);
    branchForm.phone = record.phone ?? '';
    branchForm.vat_number = record.vatNumber ?? '';
    branchForm.address = record.address ?? '';
    branchForm.is_active = record.status !== 'cancelled';
    logoPreview.value = record.logoUrl;
    paymentQrcodePreview.value = record.paymentQrcodeUrl;
}

function selectBranchImage(event: Event, field: 'logo' | 'payment_qrcode') {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;

    branchForm[field] = file;

    if (!file) {
        if (field === 'logo') {
            logoPreview.value =
                selectedRecord.value && 'logoUrl' in selectedRecord.value
                    ? selectedRecord.value.logoUrl
                    : null;
        } else {
            paymentQrcodePreview.value =
                selectedRecord.value &&
                'paymentQrcodeUrl' in selectedRecord.value
                    ? selectedRecord.value.paymentQrcodeUrl
                    : null;
        }

        return;
    }

    const preview = URL.createObjectURL(file);

    if (field === 'logo') {
        logoPreview.value = preview;
    } else {
        paymentQrcodePreview.value = preview;
    }
}

function savePanel() {
    if (
        panelKind.value !== 'branches' ||
        !selectedRecord.value ||
        !('companyId' in selectedRecord.value)
    ) {
        closePanel();

        return;
    }

    branchForm.post(
        `/master-data/company-branches/branches/${selectedRecord.value.id}`,
        {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: closePanel,
        },
    );
}
</script>

<template>
    <Head title="Company & Branch" />

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
                        Company & Branch
                    </h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Company records, branch locations, contact details, and
                        active status.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <div class="relative">
                        <Search
                            class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"
                        />
                        <Input
                            v-model="search"
                            placeholder="Search organizations..."
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
                v-if="activeView === 'companies'"
                :rows="filteredCompanies"
                empty-text="No company data found."
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
                        Company Code
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Company Name
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Email
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Phone
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Branches
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
                        {{ row.email ?? '-' }}
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-500">
                        {{ row.phone ?? '-' }}
                    </td>
                    <td class="px-4 py-3 text-xs font-bold text-amber-700">
                        {{ row.branchesCount }}
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
                            @view="openPanel('companies', row)"
                            @approve="setCompanyStatus(row, 'approved')"
                            @reject="setCompanyStatus(row, 'rejected')"
                            @cancel="setCompanyStatus(row, 'cancelled')"
                        />
                    </td>
                </template>
            </MasterDataTable>

            <MasterDataTable
                v-if="activeView === 'branches'"
                :rows="filteredBranches"
                empty-text="No branch data found."
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
                        Branch Code
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Branch Name
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Company
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Phone
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Address
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
                    <td class="px-4 py-3">
                        <span
                            class="rounded bg-blue-50 px-2 py-0.5 text-[10px] font-bold text-blue-600"
                        >
                            {{ row.company }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-500">
                        {{ row.phone ?? '-' }}
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-500">
                        {{ row.address ?? '-' }}
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
                            @view="openPanel('branches', row)"
                            @approve="setBranchStatus(row, 'approved')"
                            @reject="setBranchStatus(row, 'rejected')"
                            @cancel="setBranchStatus(row, 'cancelled')"
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
                                {{ panelSubtitle }}
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
                                Code / Identifier
                            </span>
                            <Input
                                v-if="panelKind === 'branches'"
                                v-model="branchForm.code"
                                class="mt-1 font-mono text-sm focus-visible:ring-[#007882]"
                                placeholder="Ex: DRG"
                            />
                            <Input
                                v-else
                                :model-value="selectedRecord?.code ?? ''"
                                class="mt-1 font-mono text-sm focus-visible:ring-[#007882]"
                                placeholder="Ex: DRG"
                            />
                        </label>

                        <label class="block">
                            <span
                                class="text-[10px] font-bold text-slate-400 uppercase"
                            >
                                Name / Description
                            </span>
                            <Input
                                v-if="panelKind === 'branches'"
                                v-model="branchForm.name"
                                class="mt-1 text-sm focus-visible:ring-[#007882]"
                                placeholder="Enter name"
                            />
                            <Input
                                v-else
                                :model-value="selectedRecord?.name ?? ''"
                                class="mt-1 text-sm focus-visible:ring-[#007882]"
                                placeholder="Enter name"
                            />
                        </label>

                        <div
                            class="mt-2 space-y-3 border-t border-slate-200 pt-2"
                        >
                            <label
                                v-if="panelKind === 'branches'"
                                class="block"
                            >
                                <span
                                    class="text-[10px] font-bold text-slate-400 uppercase"
                                >
                                    Company
                                </span>
                                <select
                                    v-model="branchForm.company_id"
                                    class="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus:border-[#007882]"
                                >
                                    <option
                                        v-for="company in companies"
                                        :key="company.id"
                                        :value="company.id"
                                    >
                                        {{ company.name }}
                                    </option>
                                </select>
                            </label>
                            <label
                                v-if="panelKind === 'companies'"
                                class="block"
                            >
                                <span
                                    class="text-[10px] font-bold text-slate-400 uppercase"
                                >
                                    Email
                                </span>
                                <Input
                                    :model-value="
                                        selectedRecord &&
                                        'email' in selectedRecord
                                            ? (selectedRecord.email ?? '')
                                            : ''
                                    "
                                    class="mt-1 text-sm focus-visible:ring-[#007882]"
                                    placeholder="company@example.com"
                                />
                            </label>
                            <label class="block">
                                <span
                                    class="text-[10px] font-bold text-slate-400 uppercase"
                                >
                                    Phone
                                </span>
                                <Input
                                    v-if="panelKind === 'branches'"
                                    v-model="branchForm.phone"
                                    class="mt-1 text-sm focus-visible:ring-[#007882]"
                                    placeholder="+66 ..."
                                />
                                <Input
                                    v-else
                                    :model-value="selectedRecord?.phone ?? ''"
                                    class="mt-1 text-sm focus-visible:ring-[#007882]"
                                    placeholder="+66 ..."
                                />
                            </label>
                            <label
                                v-if="panelKind === 'branches'"
                                class="block"
                            >
                                <span
                                    class="text-[10px] font-bold text-slate-400 uppercase"
                                >
                                    VAT Number
                                </span>
                                <Input
                                    v-model="branchForm.vat_number"
                                    class="mt-1 text-sm focus-visible:ring-[#007882]"
                                    placeholder="VAT number"
                                />
                            </label>
                            <label class="block">
                                <span
                                    class="text-[10px] font-bold text-slate-400 uppercase"
                                >
                                    Address
                                </span>
                                <Input
                                    v-if="panelKind === 'branches'"
                                    v-model="branchForm.address"
                                    class="mt-1 text-sm focus-visible:ring-[#007882]"
                                    placeholder="Address"
                                />
                                <Input
                                    v-else
                                    :model-value="selectedRecord?.address ?? ''"
                                    class="mt-1 text-sm focus-visible:ring-[#007882]"
                                    placeholder="Address"
                                />
                            </label>
                        </div>

                        <div
                            v-if="panelKind === 'branches'"
                            class="grid gap-3 border-t border-slate-200 pt-3 sm:grid-cols-2"
                        >
                            <label class="block">
                                <span
                                    class="text-[10px] font-bold text-slate-400 uppercase"
                                >
                                    Slip Logo
                                </span>
                                <div
                                    class="mt-1 flex aspect-[4/3] items-center justify-center overflow-hidden rounded-lg border border-dashed border-slate-200 bg-white"
                                >
                                    <img
                                        v-if="logoPreview"
                                        :src="logoPreview"
                                        alt="Branch logo preview"
                                        class="h-full w-full object-contain p-2"
                                    />
                                    <span
                                        v-else
                                        class="px-3 text-center text-[10px] font-bold text-slate-400 uppercase"
                                    >
                                        No logo
                                    </span>
                                </div>
                                <input
                                    type="file"
                                    accept="image/*"
                                    class="mt-2 block w-full text-[11px] text-slate-500 file:mr-2 file:rounded-md file:border-0 file:bg-[#007882] file:px-3 file:py-1.5 file:text-[10px] file:font-bold file:text-white"
                                    @change="selectBranchImage($event, 'logo')"
                                />
                                <p
                                    v-if="branchForm.errors.logo"
                                    class="mt-1 text-[10px] font-bold text-red-500"
                                >
                                    {{ branchForm.errors.logo }}
                                </p>
                            </label>

                            <label class="block">
                                <span
                                    class="text-[10px] font-bold text-slate-400 uppercase"
                                >
                                    Payment QR Code
                                </span>
                                <div
                                    class="mt-1 flex aspect-square items-center justify-center overflow-hidden rounded-lg border border-dashed border-slate-200 bg-white"
                                >
                                    <img
                                        v-if="paymentQrcodePreview"
                                        :src="paymentQrcodePreview"
                                        alt="Payment QR code preview"
                                        class="h-full w-full object-contain p-2"
                                    />
                                    <span
                                        v-else
                                        class="px-3 text-center text-[10px] font-bold text-slate-400 uppercase"
                                    >
                                        No QR code
                                    </span>
                                </div>
                                <input
                                    type="file"
                                    accept="image/*"
                                    class="mt-2 block w-full text-[11px] text-slate-500 file:mr-2 file:rounded-md file:border-0 file:bg-[#007882] file:px-3 file:py-1.5 file:text-[10px] file:font-bold file:text-white"
                                    @change="
                                        selectBranchImage(
                                            $event,
                                            'payment_qrcode',
                                        )
                                    "
                                />
                                <p
                                    v-if="branchForm.errors.payment_qrcode"
                                    class="mt-1 text-[10px] font-bold text-red-500"
                                >
                                    {{ branchForm.errors.payment_qrcode }}
                                </p>
                            </label>
                        </div>

                        <label class="block">
                            <span
                                class="text-[10px] font-bold text-slate-400 uppercase"
                            >
                                Status
                            </span>
                            <select
                                v-if="panelKind === 'branches'"
                                v-model="branchForm.is_active"
                                class="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus:border-[#007882]"
                            >
                                <option :value="true">Active</option>
                                <option :value="false">Inactive</option>
                            </select>
                            <select
                                v-else
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
                        :disabled="branchForm.processing"
                        @click="savePanel"
                    >
                        <Save class="size-4" />
                        Save Changes
                    </Button>
                </SheetFooter>
            </SheetContent>
        </Sheet>
    </AppLayout>
</template>
