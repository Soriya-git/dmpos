<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    Boxes,
    MapPinned,
    PackageCheck,
    Plus,
    Save,
    Search,
    Warehouse,
    X,
} from 'lucide-vue-next';
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

type WarehouseView = 'warehouses' | 'locations';

type LocationType =
    | 'inbound_staging'
    | 'putaway'
    | 'outbound_staging'
    | 'scrap'
    | 'damage'
    | 'obsolete'
    | 'general';

type WarehouseRecord = {
    id: number;
    code: string;
    name: string;
    branch: string;
    address: string | null;
    locationsCount: number;
    quantityOnHand: string;
    quantityAvailable: string;
    isDefault: boolean;
    description: string | null;
    status: ApprovalStatus;
};

type LocationRecord = {
    id: number;
    code: string;
    name: string;
    warehouse: string;
    branch: string;
    locationType: LocationType;
    isSaleable: boolean;
    quantityOnHand: string;
    quantityAvailable: string;
    description: string | null;
    status: ApprovalStatus;
};

type PanelRecord = WarehouseRecord | LocationRecord;

const props = defineProps<{
    warehouses: WarehouseRecord[];
    locations: LocationRecord[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Master Data' },
    {
        title: 'Warehouse & Location',
        href: '/master-data/warehouse-locations',
    },
];

const activeView = ref<WarehouseView>('warehouses');
const search = ref('');
const panelOpen = ref(false);
const panelKind = ref<WarehouseView>('warehouses');
const selectedRecord = ref<PanelRecord | null>(null);
const warehouses = ref<WarehouseRecord[]>([...props.warehouses]);
const locations = ref<LocationRecord[]>([...props.locations]);

const tabs: {
    key: WarehouseView;
    label: string;
    icon: typeof Warehouse;
}[] = [
    { key: 'warehouses', label: 'Warehouses', icon: Warehouse },
    { key: 'locations', label: 'Locations', icon: MapPinned },
];

const normalizedSearch = computed(() => search.value.trim().toLowerCase());
const totalWarehouses = computed(() => warehouses.value.length);
const totalLocations = computed(() => locations.value.length);
const saleableLocations = computed(
    () => locations.value.filter((location) => location.isSaleable).length,
);
const totalOnHand = computed(() =>
    locations.value
        .reduce(
            (total, location) =>
                total + Number(location.quantityOnHand.replace(/,/g, '')),
            0,
        )
        .toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }),
);

const filteredWarehouses = computed(() => filterRows(warehouses.value));
const filteredLocations = computed(() => filterRows(locations.value));

const panelTitle = computed(() =>
    selectedRecord.value ? `Edit ${panelLabel()}` : `New ${panelLabel()}`,
);

function panelLabel() {
    return panelKind.value === 'warehouses' ? 'Warehouse' : 'Location';
}

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

function switchView(view: WarehouseView) {
    activeView.value = view;
    panelKind.value = view;
}

function openPanel(
    kind: WarehouseView = activeView.value,
    record: PanelRecord | null = null,
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

function locationTypeLabel(type: LocationType) {
    return type.replace(/_/g, ' ');
}

function locationTypeClass(type: LocationType) {
    const classes: Record<LocationType, string> = {
        inbound_staging: 'border-blue-200 bg-blue-50 text-blue-700',
        putaway: 'border-[#23AA8F]/20 bg-[#23AA8F]/10 text-[#16836f]',
        outbound_staging: 'border-indigo-200 bg-indigo-50 text-indigo-700',
        scrap: 'border-slate-300 bg-slate-100 text-slate-500',
        damage: 'border-rose-200 bg-rose-50 text-rose-700',
        obsolete: 'border-orange-200 bg-orange-50 text-orange-700',
        general: 'border-slate-200 bg-slate-50 text-slate-600',
    };

    return classes[type];
}

function setWarehouseStatus(record: WarehouseRecord, status: ApprovalStatus) {
    record.status = status;
}

function setLocationStatus(record: LocationRecord, status: ApprovalStatus) {
    record.status = status;
}
</script>

<template>
    <Head title="Warehouse & Location" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 lg:p-6">
            <div
                class="flex flex-col justify-between gap-4 lg:flex-row lg:items-center"
            >
                <div>
                    <h1
                        class="text-2xl font-semibold tracking-tight text-[#2A4858]"
                    >
                        Warehouse & Location
                    </h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Branch warehouses, storage bins, saleable zones, and
                        stock quantities.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <div class="relative">
                        <Search
                            class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"
                        />
                        <Input
                            v-model="search"
                            placeholder="Search warehouse data..."
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

            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div
                    class="rounded-lg border-l-4 border-[#007882] bg-white p-4 shadow-sm"
                >
                    <p class="text-xs font-bold text-slate-500 uppercase">
                        Warehouses
                    </p>
                    <h3 class="mt-1 text-2xl font-bold">
                        {{ totalWarehouses }}
                    </h3>
                </div>
                <div
                    class="rounded-lg border-l-4 border-blue-400 bg-white p-4 shadow-sm"
                >
                    <p class="text-xs font-bold text-slate-500 uppercase">
                        Locations
                    </p>
                    <h3 class="mt-1 text-2xl font-bold text-blue-600">
                        {{ totalLocations }}
                    </h3>
                </div>
                <div
                    class="rounded-lg border-l-4 border-[#23AA8F] bg-white p-4 shadow-sm"
                >
                    <p class="text-xs font-bold text-slate-500 uppercase">
                        Saleable Zones
                    </p>
                    <h3 class="mt-1 text-2xl font-bold text-[#16836f]">
                        {{ saleableLocations }}
                    </h3>
                </div>
                <div
                    class="rounded-lg border-l-4 border-amber-400 bg-white p-4 shadow-sm"
                >
                    <p class="text-xs font-bold text-slate-500 uppercase">
                        On Hand Qty
                    </p>
                    <h3 class="mt-1 text-2xl font-bold text-amber-600">
                        {{ totalOnHand }}
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
                v-if="activeView === 'warehouses'"
                :rows="filteredWarehouses"
                empty-text="No warehouse data found."
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
                        Warehouse Code
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Warehouse Name
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Branch
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Locations
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Stock
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Default
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
                            {{ row.branch }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-xs font-bold text-amber-700">
                        {{ row.locationsCount }}
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex flex-col gap-1 text-xs">
                            <span class="font-bold text-slate-700">
                                {{ row.quantityOnHand }} on hand
                            </span>
                            <span class="text-[10px] text-[#16836f]">
                                {{ row.quantityAvailable }} available
                            </span>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <span
                            class="rounded border px-2 py-0.5 text-[10px] font-bold"
                            :class="
                                row.isDefault
                                    ? 'border-[#23AA8F]/20 bg-[#23AA8F]/10 text-[#16836f]'
                                    : 'border-slate-200 bg-slate-50 text-slate-500'
                            "
                        >
                            {{ row.isDefault ? 'Default' : 'Secondary' }}
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
                        <ApprovalActionMenu
                            :status="row.status"
                            @view="openPanel('warehouses', row)"
                            @approve="setWarehouseStatus(row, 'approved')"
                            @reject="setWarehouseStatus(row, 'rejected')"
                            @cancel="setWarehouseStatus(row, 'cancelled')"
                        />
                    </td>
                </template>
            </MasterDataTable>

            <MasterDataTable
                v-if="activeView === 'locations'"
                :rows="filteredLocations"
                empty-text="No stock location data found."
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
                        Location Code
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Location Name
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Warehouse
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Type
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Stock
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Saleable
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
                        {{ row.warehouse }}
                    </td>
                    <td class="px-4 py-3">
                        <span
                            class="rounded border px-2 py-0.5 text-[10px] font-bold capitalize"
                            :class="locationTypeClass(row.locationType)"
                        >
                            {{ locationTypeLabel(row.locationType) }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex flex-col gap-1 text-xs">
                            <span class="font-bold text-slate-700">
                                {{ row.quantityOnHand }} on hand
                            </span>
                            <span class="text-[10px] text-[#16836f]">
                                {{ row.quantityAvailable }} available
                            </span>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <span
                            class="rounded border px-2 py-0.5 text-[10px] font-bold"
                            :class="
                                row.isSaleable
                                    ? 'border-[#23AA8F]/20 bg-[#23AA8F]/10 text-[#16836f]'
                                    : 'border-slate-300 bg-slate-100 text-slate-500'
                            "
                        >
                            {{ row.isSaleable ? 'Saleable' : 'Non-saleable' }}
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
                        <ApprovalActionMenu
                            :status="row.status"
                            @view="openPanel('locations', row)"
                            @approve="setLocationStatus(row, 'approved')"
                            @reject="setLocationStatus(row, 'rejected')"
                            @cancel="setLocationStatus(row, 'cancelled')"
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
                                Warehouse and stock location master data
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
                    <div class="space-y-3 rounded-xl bg-slate-50 p-4">
                        <label class="block">
                            <span
                                class="text-[10px] font-bold text-slate-400 uppercase"
                            >
                                Code / Identifier
                            </span>
                            <Input
                                :model-value="selectedRecord?.code ?? ''"
                                class="mt-1 font-mono text-sm focus-visible:ring-[#007882]"
                                placeholder="WH-001"
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
                            class="mt-2 space-y-3 border-t border-slate-200 pt-2"
                        >
                            <label
                                v-if="panelKind === 'locations'"
                                class="block"
                            >
                                <span
                                    class="text-[10px] font-bold text-slate-400 uppercase"
                                >
                                    Warehouse
                                </span>
                                <select
                                    class="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus:border-[#007882]"
                                >
                                    <option
                                        v-for="warehouse in warehouses"
                                        :key="warehouse.id"
                                    >
                                        {{ warehouse.name }}
                                    </option>
                                </select>
                            </label>
                            <label class="block">
                                <span
                                    class="text-[10px] font-bold text-slate-400 uppercase"
                                >
                                    Branch
                                </span>
                                <Input
                                    :model-value="
                                        selectedRecord &&
                                        'branch' in selectedRecord
                                            ? selectedRecord.branch
                                            : ''
                                    "
                                    class="mt-1 text-sm focus-visible:ring-[#007882]"
                                    placeholder="Branch"
                                />
                            </label>
                            <label
                                v-if="panelKind === 'locations'"
                                class="block"
                            >
                                <span
                                    class="text-[10px] font-bold text-slate-400 uppercase"
                                >
                                    Location Type
                                </span>
                                <select
                                    class="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus:border-[#007882]"
                                >
                                    <option>Inbound Staging</option>
                                    <option>Putaway</option>
                                    <option>Outbound Staging</option>
                                    <option>Damage</option>
                                    <option>Obsolete</option>
                                    <option>Scrap</option>
                                    <option>General</option>
                                </select>
                            </label>
                            <label
                                v-if="panelKind === 'warehouses'"
                                class="block"
                            >
                                <span
                                    class="text-[10px] font-bold text-slate-400 uppercase"
                                >
                                    Address
                                </span>
                                <Input
                                    :model-value="
                                        selectedRecord &&
                                        'address' in selectedRecord
                                            ? (selectedRecord.address ?? '')
                                            : ''
                                    "
                                    class="mt-1 text-sm focus-visible:ring-[#007882]"
                                    placeholder="Warehouse address"
                                />
                            </label>
                            <label class="block">
                                <span
                                    class="text-[10px] font-bold text-slate-400 uppercase"
                                >
                                    Description
                                </span>
                                <Input
                                    :model-value="
                                        selectedRecord?.description ?? ''
                                    "
                                    class="mt-1 text-sm focus-visible:ring-[#007882]"
                                    placeholder="Description"
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
    </AppLayout>
</template>
