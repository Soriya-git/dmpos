<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { MonitorCog, Plus, Save, Search, Timer, X } from 'lucide-vue-next';
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

type PosView = 'terminals' | 'sessions';

type TerminalRecord = {
    id: number;
    code: string;
    name: string;
    branch: string;
    deviceType: string;
    sessionStatus: 'open' | 'available';
    currentSessionNo: string | null;
    openedBy: string | null;
    openedAt: string | null;
    status: ApprovalStatus;
};

type SessionRecord = {
    id: number;
    code: string;
    terminal: string;
    branch: string;
    openedBy: string;
    openedAt: string | null;
    openingCashUsd: string;
    openingCashKhr: string;
    totalSalesUsd: string;
    status: ApprovalStatus;
};

type PanelRecord = TerminalRecord | SessionRecord;

const props = defineProps<{
    terminals: TerminalRecord[];
    sessions: SessionRecord[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Master Data' },
    { title: 'POS Terminals', href: '/master-data/pos-terminals' },
];

const activeView = ref<PosView>('terminals');
const search = ref('');
const panelOpen = ref(false);
const panelKind = ref<PosView>('terminals');
const selectedRecord = ref<PanelRecord | null>(null);
const terminals = ref<TerminalRecord[]>([...props.terminals]);
const sessions = ref<SessionRecord[]>([...props.sessions]);

const tabs: {
    key: PosView;
    label: string;
    icon: typeof MonitorCog;
}[] = [
    { key: 'terminals', label: 'POS Terminals', icon: MonitorCog },
    { key: 'sessions', label: 'Current POS Sessions', icon: Timer },
];

const normalizedSearch = computed(() => search.value.trim().toLowerCase());
const totalTerminals = computed(() => terminals.value.length);
const openTerminalCount = computed(
    () =>
        terminals.value.filter((terminal) => terminal.sessionStatus === 'open')
            .length,
);
const availableTerminalCount = computed(
    () =>
        terminals.value.filter(
            (terminal) => terminal.sessionStatus === 'available',
        ).length,
);

const filteredTerminals = computed(() => filterRows(terminals.value));
const filteredSessions = computed(() => filterRows(sessions.value));

const panelTitle = computed(() =>
    selectedRecord.value
        ? `View ${panelKind.value === 'terminals' ? 'POS Terminal' : 'POS Session'}`
        : `New ${panelKind.value === 'terminals' ? 'POS Terminal' : 'POS Session'}`,
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

function switchView(view: PosView) {
    activeView.value = view;
    panelKind.value = view;
}

function openPanel(
    kind: PosView = activeView.value,
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

function terminalSessionClass(status: TerminalRecord['sessionStatus']) {
    return status === 'open'
        ? 'border-amber-200 bg-amber-50 text-amber-700'
        : 'border-[#23AA8F]/20 bg-[#23AA8F]/10 text-[#16836f]';
}

function setTerminalStatus(record: TerminalRecord, status: ApprovalStatus) {
    record.status = status;
}

function setSessionStatus(record: SessionRecord, status: ApprovalStatus) {
    record.status = status;
}
</script>

<template>
    <Head title="POS Terminals" />

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
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div
                    class="rounded-lg border-l-4 border-[#007882] bg-white p-4 shadow-sm"
                >
                    <p class="text-xs font-bold text-slate-500 uppercase">
                        POS Terminals
                    </p>
                    <h3 class="mt-1 text-2xl font-bold">
                        {{ totalTerminals }}
                    </h3>
                </div>
                <div
                    class="rounded-lg border-l-4 border-amber-400 bg-white p-4 shadow-sm"
                >
                    <p class="text-xs font-bold text-slate-500 uppercase">
                        Open Sessions
                    </p>
                    <h3 class="mt-1 text-2xl font-bold text-amber-600">
                        {{ openTerminalCount }}
                    </h3>
                </div>
                <div
                    class="rounded-lg border-l-4 border-[#23AA8F] bg-white p-4 shadow-sm"
                >
                    <p class="text-xs font-bold text-slate-500 uppercase">
                        Available Terminals
                    </p>
                    <h3 class="mt-1 text-2xl font-bold text-[#16836f]">
                        {{ availableTerminalCount }}
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

            <div class="relative w-full max-w-sm">
                <Search
                    class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"
                />
                <Input
                    v-model="search"
                    placeholder="Search POS data..."
                    class="h-9 rounded-lg border-slate-200 bg-white pl-9 text-xs focus-visible:ring-[#007882]"
                />
            </div>

            <MasterDataTable
                v-if="activeView === 'terminals'"
                :rows="filteredTerminals"
                empty-text="No POS terminal data found."
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
                        POS Code
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Terminal Name
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Branch
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Device
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Current Session
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
                    <td class="px-4 py-3 text-xs text-slate-500">
                        {{ row.deviceType }}
                    </td>
                    <td class="px-4 py-3">
                        <span
                            class="rounded border px-2 py-0.5 text-[10px] font-bold"
                            :class="terminalSessionClass(row.sessionStatus)"
                        >
                            {{ row.currentSessionNo ?? 'Available' }}
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
                            @view="openPanel('terminals', row)"
                            @approve="setTerminalStatus(row, 'approved')"
                            @reject="setTerminalStatus(row, 'rejected')"
                            @cancel="setTerminalStatus(row, 'cancelled')"
                        />
                    </td>
                </template>
            </MasterDataTable>

            <MasterDataTable
                v-if="activeView === 'sessions'"
                :rows="filteredSessions"
                empty-text="No current POS sessions found."
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
                        Session No
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Terminal
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Branch
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Opened By
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Opening Cash
                    </th>
                    <th
                        class="px-4 py-3 text-left text-[10px] font-extrabold tracking-wider text-slate-500 uppercase"
                    >
                        Total Sales
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
                        {{ row.terminal }}
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-500">
                        {{ row.branch }}
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-500">
                        {{ row.openedBy }}
                    </td>
                    <td class="px-4 py-3 text-xs font-bold text-amber-700">
                        ${{ row.openingCashUsd }} / {{ row.openingCashKhr }} KHR
                    </td>
                    <td class="px-4 py-3 text-xs font-bold text-[#16836f]">
                        ${{ row.totalSalesUsd }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <ApprovalActionMenu
                            :status="row.status"
                            @view="openPanel('sessions', row)"
                            @approve="setSessionStatus(row, 'approved')"
                            @reject="setSessionStatus(row, 'rejected')"
                            @cancel="setSessionStatus(row, 'cancelled')"
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
                                POS terminal and current session master data
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
                                placeholder="POS-01"
                            />
                        </label>

                        <label class="block">
                            <span
                                class="text-[10px] font-bold text-slate-400 uppercase"
                            >
                                Name / Description
                            </span>
                            <Input
                                :model-value="
                                    selectedRecord && 'name' in selectedRecord
                                        ? selectedRecord.name
                                        : selectedRecord &&
                                            'terminal' in selectedRecord
                                          ? selectedRecord.terminal
                                          : ''
                                "
                                class="mt-1 text-sm focus-visible:ring-[#007882]"
                                placeholder="POS terminal"
                            />
                        </label>

                        <div
                            class="mt-2 space-y-3 border-t border-slate-200 pt-2"
                        >
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
                                v-if="panelKind === 'terminals'"
                                class="block"
                            >
                                <span
                                    class="text-[10px] font-bold text-slate-400 uppercase"
                                >
                                    Device Type
                                </span>
                                <select
                                    class="mt-1 h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus:border-[#007882]"
                                >
                                    <option>desktop</option>
                                    <option>tablet</option>
                                </select>
                            </label>
                            <label
                                v-if="panelKind === 'sessions'"
                                class="block"
                            >
                                <span
                                    class="text-[10px] font-bold text-slate-400 uppercase"
                                >
                                    Opened By
                                </span>
                                <Input
                                    :model-value="
                                        selectedRecord &&
                                        'openedBy' in selectedRecord
                                            ? (selectedRecord.openedBy ?? '')
                                            : ''
                                    "
                                    class="mt-1 text-sm focus-visible:ring-[#007882]"
                                    placeholder="Cashier"
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
