<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    Ban,
    Building2,
    CheckCircle2,
    CreditCard,
    Eye,
    MoreVertical,
    Pencil,
    Plus,
    Save,
    Search,
    Users,
    X,
    XCircle,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import ApprovalActionMenu from '@/components/master-data/ApprovalActionMenu.vue';
import MasterDataTable from '@/components/master-data/MasterDataTable.vue';
import MembershipCardPreview from '@/components/master-data/MembershipCardPreview.vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
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

type CustomerView = 'customers' | 'groups';

type CardStatus = 'active' | 'inactive' | 'blocked' | 'expired' | 'cancelled';

type CustomerRecord = {
    id: number;
    code: string;
    name: string;
    phone: string;
    email: string | null;
    address: string | null;
    group: string;
    status: ApprovalStatus;
    membership_cards: CustomerMembershipCard[];
    membership_card_count: number;
};

type CustomerGroupRecord = {
    id: number;
    code: string;
    name: string;
    description: string | null;
    members: number;
    status: ApprovalStatus;
};

type CustomerPanelRecord = CustomerRecord | CustomerGroupRecord;

type CustomerMembershipCard = {
    id: number;
    customerId: number;
    cardNo: string;
    cardName: string;
    status: CardStatus;
    issuedDate: string;
    expiredDate: string;
    remark: string;
    balances: {
        currency: string;
        balance: number;
    }[];
};

const props = defineProps<{
    customers: CustomerRecord[];
    groups: CustomerGroupRecord[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Master Data' },
    { title: 'Customers', href: '/master-data/customers' },
];

const activeView = ref<CustomerView>('customers');
const search = ref('');
const panelOpen = ref(false);
const panelKind = ref<CustomerView>('customers');
const selectedRecord = ref<CustomerPanelRecord | null>(null);
const cardPanelOpen = ref(false);
const selectedCustomer = ref<CustomerRecord | null>(null);
const selectedCardId = ref<number | null>(null);

const customers = ref<CustomerRecord[]>([...props.customers]);
const groups = ref<CustomerGroupRecord[]>([...props.groups]);
const customerCards = ref<Record<number, CustomerMembershipCard[]>>(
    Object.fromEntries(
        props.customers.map((customer) => [
            customer.id,
            customer.membership_cards ?? [],
        ]),
    ),
);

const cardForm = ref<CustomerMembershipCard>({
    id: 0,
    customerId: 0,
    cardNo: '',
    cardName: '',
    status: 'active',
    issuedDate: '',
    expiredDate: '',
    remark: '',
    balances: [{ currency: 'USD', balance: 0 }],
});

const tabs: {
    key: CustomerView;
    label: string;
    icon: typeof Users;
}[] = [
    { key: 'customers', label: 'Customer Registry', icon: Users },
    { key: 'groups', label: 'Customer Groups', icon: Building2 },
];

const normalizedSearch = computed(() => search.value.trim().toLowerCase());

const filteredCustomers = computed(() => filterRows(customers.value));
const filteredGroups = computed(() => filterRows(groups.value));
const selectedCustomerCards = computed(() =>
    selectedCustomer.value
        ? (customerCards.value[selectedCustomer.value.id] ?? [])
        : [],
);

const panelTitle = computed(() =>
    selectedRecord.value
        ? `Edit ${panelKind.value === 'customers' ? 'Customer' : 'Group'}`
        : `New ${panelKind.value === 'customers' ? 'Customer' : 'Group'}`,
);

const panelSubtitle = computed(() =>
    panelKind.value === 'customers'
        ? 'Customer profile and billing master data'
        : 'Customer segmentation and payment rules',
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

function switchView(view: CustomerView) {
    activeView.value = view;
    panelKind.value = view;
}

function openPanel(
    kind: CustomerView = activeView.value,
    record: CustomerPanelRecord | null = null,
) {
    panelKind.value = kind;
    selectedRecord.value = record;
    panelOpen.value = true;
}

function closePanel() {
    panelOpen.value = false;
    selectedRecord.value = null;
}

function statusLabel(status: ApprovalStatus) {
    return status === 'cancelled'
        ? 'Cancel'
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

function setCustomerStatus(record: CustomerRecord, status: ApprovalStatus) {
    record.status = status;
}

function setGroupStatus(record: CustomerGroupRecord, status: ApprovalStatus) {
    record.status = status;
}

function cardsForCustomer(customerId: number) {
    return customerCards.value[customerId] ?? [];
}

function hasCustomerCards(customerId: number) {
    return cardsForCustomer(customerId).length > 0;
}

function copyCard(card: CustomerMembershipCard) {
    return {
        ...card,
        balances: card.balances.map((balance) => ({ ...balance })),
    };
}

function openCardPanel(customer: CustomerRecord) {
    selectedCustomer.value = customer;
    cardPanelOpen.value = true;

    const firstCard = cardsForCustomer(customer.id)[0];
    if (firstCard) {
        selectedCardId.value = firstCard.id;
        cardForm.value = copyCard(firstCard);
    } else {
        selectedCardId.value = null;
        cardForm.value = {
            id: 0,
            customerId: customer.id,
            cardNo: '',
            cardName: '',
            status: 'inactive',
            issuedDate: '',
            expiredDate: '',
            remark: '',
            balances: [],
        };
    }
}

function closeCardPanel() {
    cardPanelOpen.value = false;
    selectedCustomer.value = null;
    selectedCardId.value = null;
}

function selectCustomerCard(card: CustomerMembershipCard) {
    selectedCardId.value = card.id;
    cardForm.value = copyCard(card);
}

function viewCustomerCard(card: CustomerMembershipCard) {
    router.get('/membership-cards', { card: card.id });
}

function cardBalanceSummary(card: CustomerMembershipCard) {
    return card.balances
        .map(
            (balance) =>
                `${balance.currency} ${balance.balance.toLocaleString()}`,
        )
        .join(' / ');
}

function canAct(status: ApprovalStatus) {
    return ['draft', 'pending'].includes(status);
}
</script>

<template>
    <Head title="Customers" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #actions>
            <Button
                class="h-9 rounded-lg bg-[#007882] px-4 text-xs font-bold text-white hover:bg-[#006871]"
                @click="openPanel()"
            >
                <Plus class="size-4" />
                New
            </Button>
        </template>

        <div
            class="flex h-[calc(100dvh-4rem)] w-full [scrollbar-gutter:stable] flex-col gap-6 overflow-y-scroll bg-[#f8fafc] p-4 text-slate-800 md:h-[calc(100dvh-5rem)] md:p-6 xl:p-8 2xl:p-10"
        >
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

            <div class="relative w-full max-w-sm">
                <Search
                    class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"
                />
                <Input
                    v-model="search"
                    placeholder="Search customers..."
                    class="h-9 rounded-lg border-slate-200 bg-white pl-9 text-xs focus-visible:ring-[#007882]"
                />
            </div>

            <MasterDataTable
                v-if="activeView === 'customers'"
                :rows="filteredCustomers"
                empty-text="No customer registry data found."
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
                        Customer Code
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Customer Name
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Phone
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Email
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Group
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
                    <td class="px-4 py-3">
                        <div class="text-sm font-bold text-slate-700">
                            {{ row.name }}
                        </div>
                        <button
                            v-if="hasCustomerCards(row.id)"
                            type="button"
                            class="mt-1 inline-flex items-center gap-1 rounded border border-[#23AA8F]/20 bg-[#23AA8F]/10 px-1.5 py-0.5 text-[10px] font-bold text-[#16836f] hover:border-[#23AA8F]/40 hover:bg-[#23AA8F]/15"
                            @click="openCardPanel(row)"
                        >
                            <CreditCard class="size-3" />
                            {{ row.membership_card_count }} card
                        </button>
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-500">
                        {{ row.phone }}
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-500">
                        {{ row.email ?? '-' }}
                    </td>
                    <td class="px-4 py-3">
                        <span
                            class="rounded bg-blue-50 px-2 py-0.5 text-[10px] font-bold text-blue-600"
                        >
                            {{ row.group }}
                        </span>
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
                        <DropdownMenu :modal="false">
                            <DropdownMenuTrigger as-child>
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="icon"
                                    class="mx-auto flex size-8 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-[#007882]"
                                    title="Actions"
                                >
                                    <MoreVertical class="size-4" />
                                    <span class="sr-only">Open actions</span>
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent
                                align="end"
                                class="z-[80] w-44"
                            >
                                <DropdownMenuItem
                                    @select="openPanel('customers', row)"
                                >
                                    <Pencil
                                        v-if="canAct(row.status)"
                                        class="size-4 text-[#007882]"
                                    />
                                    <Eye v-else class="size-4 text-[#007882]" />
                                    {{ canAct(row.status) ? 'Edit' : 'View' }}
                                </DropdownMenuItem>
                                <DropdownMenuItem @select="openCardPanel(row)">
                                    <CreditCard class="size-4 text-[#007882]" />
                                    Manage Card
                                </DropdownMenuItem>
                                <DropdownMenuSeparator />
                                <DropdownMenuItem
                                    :disabled="!canAct(row.status)"
                                    @select="setCustomerStatus(row, 'approved')"
                                >
                                    <CheckCircle2
                                        class="size-4 text-emerald-600"
                                    />
                                    Approve
                                </DropdownMenuItem>
                                <DropdownMenuItem
                                    :disabled="!canAct(row.status)"
                                    @select="setCustomerStatus(row, 'rejected')"
                                >
                                    <XCircle class="size-4 text-rose-600" />
                                    Reject
                                </DropdownMenuItem>
                                <DropdownMenuItem
                                    :disabled="!canAct(row.status)"
                                    variant="destructive"
                                    @select="
                                        setCustomerStatus(row, 'cancelled')
                                    "
                                >
                                    <Ban class="size-4" />
                                    Cancel
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </td>
                </template>
            </MasterDataTable>

            <MasterDataTable
                v-if="activeView === 'groups'"
                :rows="filteredGroups"
                empty-text="No customer group data found."
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
                        Group Code
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Group Name
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Description
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Members
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
                    <td class="px-4 py-3 text-xs text-slate-500">
                        {{ row.description ?? '-' }}
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-500">
                        {{ row.members }}
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
                            @view="openPanel('groups', row)"
                            @approve="setGroupStatus(row, 'approved')"
                            @reject="setGroupStatus(row, 'rejected')"
                            @cancel="setGroupStatus(row, 'cancelled')"
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
                                :model-value="selectedRecord?.code ?? ''"
                                class="mt-1 font-mono text-sm focus-visible:ring-[#007882]"
                                placeholder="Ex: CUS-0001"
                            />
                        </label>

                        <label class="block">
                            <span
                                class="text-[10px] font-bold text-slate-400 uppercase"
                            >
                                Name / Description
                            </span>
                            <Input
                                :model-value="selectedRecord?.name ?? ''"
                                class="mt-1 text-sm focus-visible:ring-[#007882]"
                                placeholder="Enter name"
                            />
                        </label>

                        <div
                            v-if="panelKind === 'customers'"
                            class="mt-2 space-y-3 border-t border-slate-200 pt-2"
                        >
                            <label class="block">
                                <span
                                    class="text-[10px] font-bold text-slate-400 uppercase"
                                >
                                    Phone
                                </span>
                                <Input
                                    :model-value="
                                        selectedRecord &&
                                        'phone' in selectedRecord
                                            ? selectedRecord.phone
                                            : ''
                                    "
                                    class="mt-1 text-sm focus-visible:ring-[#007882]"
                                    placeholder="+66 ..."
                                />
                            </label>
                            <label class="block">
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
                                    placeholder="customer@example.com"
                                />
                            </label>
                            <label class="block">
                                <span
                                    class="text-[10px] font-bold text-slate-400 uppercase"
                                >
                                    Customer Group
                                </span>
                                <select
                                    class="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus:border-[#007882]"
                                >
                                    <option>Retail</option>
                                    <option>VIP</option>
                                    <option>Corporate</option>
                                </select>
                            </label>
                        </div>

                        <div
                            v-else
                            class="mt-2 space-y-3 border-t border-slate-200 pt-2"
                        >
                            <label class="block">
                                <span
                                    class="text-[10px] font-bold text-slate-400 uppercase"
                                >
                                    Description
                                </span>
                                <Input
                                    :model-value="
                                        selectedRecord &&
                                        'description' in selectedRecord
                                            ? (selectedRecord.description ?? '')
                                            : ''
                                    "
                                    class="mt-1 text-sm focus-visible:ring-[#007882]"
                                    placeholder="Group description"
                                />
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

        <Sheet
            :open="cardPanelOpen"
            @update:open="(open) => !open && closeCardPanel()"
        >
            <SheetContent class="w-full gap-0 p-0 sm:max-w-[760px]">
                <SheetHeader class="bg-[#2A4858] p-6 text-white">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <SheetTitle class="text-lg font-bold text-white">
                                Manage Card
                            </SheetTitle>
                            <p
                                class="mt-1 text-[10px] tracking-widest text-white/50 uppercase"
                            >
                                {{ selectedCustomer?.name ?? 'Customer' }}
                            </p>
                        </div>
                        <Button
                            variant="ghost"
                            size="icon"
                            class="size-7 rounded-full bg-white/10 text-white hover:bg-white/20 hover:text-white"
                            @click="closeCardPanel"
                        >
                            <X class="size-4" />
                            <span class="sr-only">Close card panel</span>
                        </Button>
                    </div>
                </SheetHeader>

                <div
                    class="grid min-h-0 flex-1 gap-0 overflow-y-auto lg:grid-cols-[280px_minmax(0,1fr)]"
                >
                    <div
                        class="border-b border-slate-200 p-5 lg:border-r lg:border-b-0"
                    >
                        <div class="mb-4 flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-black text-[#2A4858]">
                                    Cards
                                </h3>
                                <p class="text-xs text-slate-500">
                                    {{ selectedCustomerCards.length }} card
                                    record
                                </p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <div
                                v-for="card in selectedCustomerCards"
                                :key="card.id"
                                class="flex items-start gap-2 rounded-lg border p-3 transition"
                                :class="
                                    selectedCardId === card.id
                                        ? 'border-[#23AA8F]/40 bg-[#23AA8F]/10'
                                        : 'border-slate-200 bg-white hover:border-[#23AA8F]/30'
                                "
                            >
                                <button
                                    type="button"
                                    class="min-w-0 flex-1 text-left"
                                    @click="selectCustomerCard(card)"
                                >
                                    <div
                                        class="truncate font-mono text-xs font-bold text-[#007882]"
                                    >
                                        {{ card.cardNo }}
                                    </div>
                                    <div
                                        class="mt-1 truncate text-xs font-bold text-slate-700"
                                    >
                                        {{ card.cardName }}
                                    </div>
                                    <div
                                        class="mt-1 truncate text-[10px] text-slate-500"
                                    >
                                        {{
                                            cardBalanceSummary(card) ||
                                            'No balance'
                                        }}
                                    </div>
                                </button>

                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="icon"
                                    class="size-8 shrink-0 rounded-lg text-slate-400 hover:bg-white hover:text-[#007882]"
                                    title="View card"
                                    @click.stop="viewCustomerCard(card)"
                                >
                                    <Eye class="size-4" />
                                    <span class="sr-only">View card</span>
                                </Button>
                            </div>

                            <div
                                v-if="selectedCustomerCards.length === 0"
                                class="rounded-lg border border-dashed border-slate-200 p-4 text-center text-xs text-slate-400"
                            >
                                No cards found for this customer.
                            </div>
                        </div>
                    </div>

                    <div v-if="cardForm.id > 0" class="space-y-5 p-5">
                        <MembershipCardPreview
                            :card-no="cardForm.cardNo"
                            :card-name="cardForm.cardName"
                            :customer-name="selectedCustomer?.name"
                            company-branch="DM Group / Central Branch"
                            :issued-date="cardForm.issuedDate"
                            :expired-date="cardForm.expiredDate"
                            :remark="cardForm.remark"
                            :status="cardForm.status"
                        />

                        <div class="space-y-4 rounded-lg bg-slate-50 p-4">
                            <div class="grid gap-3 md:grid-cols-2">
                                <div>
                                    <span
                                        class="text-[10px] font-bold text-slate-400 uppercase"
                                    >
                                        Card Number
                                    </span>
                                    <div
                                        class="mt-1 rounded-md border border-slate-200 bg-white px-3 py-2 font-mono text-sm font-bold text-[#007882]"
                                    >
                                        {{ cardForm.cardNo || '-' }}
                                    </div>
                                </div>
                                <div>
                                    <span
                                        class="text-[10px] font-bold text-slate-400 uppercase"
                                    >
                                        Card Name
                                    </span>
                                    <div
                                        class="mt-1 rounded-md border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-700"
                                    >
                                        {{ cardForm.cardName || '-' }}
                                    </div>
                                </div>
                            </div>

                            <div class="grid gap-3 md:grid-cols-3">
                                <div>
                                    <span
                                        class="text-[10px] font-bold text-slate-400 uppercase"
                                    >
                                        Issued Date
                                    </span>
                                    <div
                                        class="mt-1 rounded-md border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700"
                                    >
                                        {{ cardForm.issuedDate || '-' }}
                                    </div>
                                </div>
                                <div>
                                    <span
                                        class="text-[10px] font-bold text-slate-400 uppercase"
                                    >
                                        Expiry Date
                                    </span>
                                    <div
                                        class="mt-1 rounded-md border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700"
                                    >
                                        {{ cardForm.expiredDate || '-' }}
                                    </div>
                                </div>
                                <div>
                                    <span
                                        class="text-[10px] font-bold text-slate-400 uppercase"
                                    >
                                        Status
                                    </span>
                                    <div
                                        class="mt-1 rounded-md border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 uppercase"
                                    >
                                        {{ cardForm.status }}
                                    </div>
                                </div>
                            </div>

                            <div
                                class="rounded-lg border border-slate-200 bg-white p-3"
                            >
                                <span
                                    class="text-[10px] font-bold text-slate-400 uppercase"
                                >
                                    Balance
                                </span>
                                <p
                                    class="mt-1 text-xs font-semibold text-slate-500"
                                >
                                    Card balance is managed from the membership
                                    card balance page.
                                </p>
                            </div>

                            <div>
                                <span
                                    class="text-[10px] font-bold text-slate-400 uppercase"
                                >
                                    Remark
                                </span>
                                <div
                                    class="mt-1 min-h-20 rounded-md border border-slate-200 bg-white px-3 py-2 text-xs leading-relaxed text-slate-600"
                                >
                                    {{ cardForm.remark || '-' }}
                                </div>
                            </div>

                            <Button
                                type="button"
                                class="w-full rounded-lg bg-[#007882] text-xs font-bold text-white hover:bg-[#006871]"
                                @click="viewCustomerCard(cardForm)"
                            >
                                <Eye class="size-4" />
                                View Full Card
                            </Button>
                        </div>
                    </div>
                    <div
                        v-else
                        class="flex min-h-[360px] items-center justify-center p-5 text-center"
                    >
                        <div>
                            <CreditCard
                                class="mx-auto size-10 text-slate-300"
                            />
                            <p class="mt-3 text-sm font-bold text-slate-500">
                                No card selected
                            </p>
                            <p class="mt-1 text-xs text-slate-400">
                                Select a card from the list to view its details.
                            </p>
                        </div>
                    </div>
                </div>
            </SheetContent>
        </Sheet>
    </AppLayout>
</template>
