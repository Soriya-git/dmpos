<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Check,
    ChevronRight,
    Layers3,
    MoveRight,
    Save,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { usePosFormatting } from '@/composables/usePosFormatting';
import AppLayout from '@/layouts/AppLayout.vue';

type DiningSession = {
    id: number;
    sessionNo: string;
    seatName?: string | null;
    diningResourceId?: number | null;
};

type ManagedLine = {
    id: number;
    orderId: number;
    orderNo: string;
    menuName: string;
    quantity: number;
    unitPrice: number;
    totalAmount: number;
    note?: string | null;
    status: string;
    billGroup: string;
    diningResourceId: number;
};

type DiningResource = {
    id: number;
    name: string;
    code?: string | null;
    status?: string | null;
};

type Assignment = ManagedLine & {
    originalBillGroup: string;
    originalDiningResourceId: number;
};

const props = defineProps<{
    diningSession: DiningSession;
    lines: ManagedLine[];
    resources: DiningResource[];
}>();

const { money } = usePosFormatting();
const saving = ref(false);
const selectedLineIds = ref<Set<number>>(new Set());
const selectedBillGroup = ref('Bill A');
const customBillGroup = ref('');
const selectedResourceId = ref<number>(
    props.diningSession.diningResourceId ?? props.resources[0]?.id ?? 0,
);

const assignments = ref<Assignment[]>(
    props.lines.map((line) => ({
        ...line,
        billGroup: line.billGroup || 'Bill A',
        originalBillGroup: line.billGroup || 'Bill A',
        originalDiningResourceId: line.diningResourceId,
    })),
);

const billGroups = computed(() => {
    const groups = new Set(assignments.value.map((line) => line.billGroup));

    return [...groups].sort((a, b) => a.localeCompare(b));
});

const selectedCount = computed(() => selectedLineIds.value.size);
const totalAmount = computed(() =>
    assignments.value.reduce((sum, line) => sum + Number(line.totalAmount), 0),
);

const hasChanges = computed(() =>
    assignments.value.some(
        (line) =>
            line.billGroup !== line.originalBillGroup ||
            line.diningResourceId !== line.originalDiningResourceId,
    ),
);

const groupedBills = computed(() => {
    const groups = new Map<
        string,
        {
            key: string;
            billGroup: string;
            resource: DiningResource | null;
            lines: Assignment[];
            total: number;
        }
    >();

    assignments.value.forEach((line) => {
        const resource = resourceFor(line.diningResourceId);
        const key = `${line.billGroup}-${line.diningResourceId}`;

        if (!groups.has(key)) {
            groups.set(key, {
                key,
                billGroup: line.billGroup,
                resource,
                lines: [],
                total: 0,
            });
        }

        const group = groups.get(key);

        if (!group) return;

        group.lines.push(line);
        group.total += Number(line.totalAmount);
    });

    return [...groups.values()].sort((a, b) =>
        `${a.billGroup}-${a.resource?.name ?? ''}`.localeCompare(
            `${b.billGroup}-${b.resource?.name ?? ''}`,
        ),
    );
});

function resourceFor(resourceId: number) {
    return (
        props.resources.find((resource) => resource.id === resourceId) ?? null
    );
}

function toggleLine(lineId: number) {
    const next = new Set(selectedLineIds.value);

    if (next.has(lineId)) {
        next.delete(lineId);
    } else {
        next.add(lineId);
    }

    selectedLineIds.value = next;
}

function selectAll() {
    selectedLineIds.value = new Set(assignments.value.map((line) => line.id));
}

function clearSelection() {
    selectedLineIds.value = new Set();
}

function nextBillGroup() {
    const nextLetter = String.fromCharCode(65 + billGroups.value.length);
    const group = `Bill ${nextLetter}`;

    selectedBillGroup.value = group;
    customBillGroup.value = group;
}

function applyToSelected() {
    if (selectedCount.value === 0) return;

    const billGroup =
        customBillGroup.value.trim() || selectedBillGroup.value || 'Bill A';

    assignments.value = assignments.value.map((line) => {
        if (!selectedLineIds.value.has(line.id)) {
            return line;
        }

        return {
            ...line,
            billGroup,
            diningResourceId: selectedResourceId.value,
        };
    });
}

function moveAllToResource(resourceId: number) {
    assignments.value = assignments.value.map((line) => ({
        ...line,
        diningResourceId: resourceId,
    }));
    selectedResourceId.value = resourceId;
}

function saveAssignments() {
    if (saving.value || assignments.value.length === 0) return;

    saving.value = true;

    router.post(
        `/orders/${props.diningSession.id}/manage`,
        {
            assignments: assignments.value.map((line) => ({
                line_id: line.id,
                bill_group: line.billGroup,
                dining_resource_id: line.diningResourceId,
            })),
        },
        {
            preserveScroll: true,
            onFinish: () => {
                saving.value = false;
            },
        },
    );
}

function backToOrder() {
    router.get(`/orders/${props.diningSession.id}/menu`);
}
</script>

<template>
    <Head title="Manage Orders" />

    <AppLayout>
        <div class="flex min-h-screen flex-col bg-[#F6F7F7] text-[#2A4858]">
            <header
                class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 bg-white px-5 py-4"
            >
                <div class="flex min-w-0 items-center gap-3">
                    <button
                        type="button"
                        class="flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-[#2A4858] transition hover:border-[#23AA8F] hover:text-[#007882]"
                        title="Back to order"
                        @click="backToOrder"
                    >
                        <ArrowLeft class="h-4 w-4" />
                    </button>
                    <div class="min-w-0">
                        <p
                            class="text-[10px] font-black tracking-widest text-gray-400 uppercase"
                        >
                            {{ diningSession.sessionNo }}
                        </p>
                        <h1 class="truncate text-xl font-black">
                            Manage Orders
                        </h1>
                        <p class="text-xs font-semibold text-gray-400">
                            {{ diningSession.seatName || 'Current table' }}
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <div
                        class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-right"
                    >
                        <p class="text-[10px] font-black text-gray-400">
                            Total
                        </p>
                        <p class="text-sm font-black text-[#007882]">
                            {{ money(totalAmount) }}
                        </p>
                    </div>
                    <button
                        type="button"
                        class="inline-flex h-10 items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 text-xs font-black text-[#2A4858] transition hover:border-[#23AA8F] hover:text-[#007882]"
                        @click="backToOrder"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="inline-flex h-10 items-center gap-2 rounded-xl bg-[#23AA8F] px-4 text-xs font-black text-white shadow-lg shadow-[#23AA8F]/20 transition hover:bg-[#007882] disabled:cursor-not-allowed disabled:bg-gray-300 disabled:shadow-none"
                        :disabled="saving || !hasChanges"
                        @click="saveAssignments"
                    >
                        <Save class="h-4 w-4" />
                        Save
                    </button>
                </div>
            </header>

            <main
                class="grid min-h-0 flex-1 gap-4 p-4 xl:grid-cols-[1fr_420px]"
            >
                <section class="min-h-0 rounded-2xl bg-white shadow-sm">
                    <div
                        class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 p-4"
                    >
                        <div>
                            <h2 class="text-sm font-black">Order Items</h2>
                            <p class="text-xs font-semibold text-gray-400">
                                Select items, then apply a bill or table change.
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                class="rounded-lg border border-gray-200 px-3 py-2 text-[11px] font-black text-[#2A4858] hover:border-[#23AA8F]"
                                @click="selectAll"
                            >
                                Select All
                            </button>
                            <button
                                type="button"
                                class="rounded-lg border border-gray-200 px-3 py-2 text-[11px] font-black text-gray-500 hover:border-[#23AA8F]"
                                @click="clearSelection"
                            >
                                Clear
                            </button>
                        </div>
                    </div>

                    <div class="grid gap-3 p-4 md:grid-cols-2 2xl:grid-cols-3">
                        <article
                            v-for="line in assignments"
                            :key="line.id"
                            class="rounded-xl border p-3 transition"
                            :class="
                                selectedLineIds.has(line.id)
                                    ? 'border-[#23AA8F] bg-[#23AA8F]/5 shadow-sm'
                                    : 'border-gray-100 bg-white hover:border-gray-200'
                            "
                            @click="toggleLine(line.id)"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p
                                        class="truncate text-sm font-black text-[#2A4858]"
                                    >
                                        {{ line.menuName }}
                                    </p>
                                    <p
                                        class="text-[11px] font-semibold text-gray-400"
                                    >
                                        {{ line.orderNo }} | Qty
                                        {{ line.quantity }}
                                    </p>
                                </div>
                                <span
                                    class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full border"
                                    :class="
                                        selectedLineIds.has(line.id)
                                            ? 'border-[#23AA8F] bg-[#23AA8F] text-white'
                                            : 'border-gray-200 text-transparent'
                                    "
                                >
                                    <Check class="h-3.5 w-3.5" />
                                </span>
                            </div>

                            <p
                                v-if="line.note"
                                class="mt-2 rounded-lg bg-gray-50 px-2 py-1 text-[11px] font-semibold text-gray-500"
                            >
                                {{ line.note }}
                            </p>

                            <div
                                class="mt-3 grid grid-cols-2 gap-2 text-[11px] font-bold"
                            >
                                <div class="rounded-lg bg-gray-50 p-2">
                                    <p class="text-gray-400">Bill</p>
                                    <p class="truncate text-[#2A4858]">
                                        {{ line.billGroup }}
                                    </p>
                                </div>
                                <div class="rounded-lg bg-gray-50 p-2">
                                    <p class="text-gray-400">Table</p>
                                    <p class="truncate text-[#2A4858]">
                                        {{
                                            resourceFor(line.diningResourceId)
                                                ?.name || 'Unassigned'
                                        }}
                                    </p>
                                </div>
                            </div>

                            <div
                                class="mt-3 flex items-center justify-between border-t border-dashed pt-2"
                            >
                                <span
                                    class="text-[11px] font-bold text-gray-400"
                                >
                                    Line Total
                                </span>
                                <span class="text-sm font-black text-[#007882]">
                                    {{ money(line.totalAmount) }}
                                </span>
                            </div>
                        </article>

                        <div
                            v-if="assignments.length === 0"
                            class="col-span-full rounded-xl border border-dashed border-gray-200 p-10 text-center text-sm font-semibold text-gray-400"
                        >
                            No billable order items are available to manage.
                        </div>
                    </div>
                </section>

                <aside class="space-y-4">
                    <section class="rounded-2xl bg-white p-4 shadow-sm">
                        <div class="mb-3 flex items-center gap-2">
                            <Layers3 class="h-4 w-4 text-[#23AA8F]" />
                            <h2 class="text-sm font-black">Apply Changes</h2>
                        </div>

                        <div class="space-y-3">
                            <label class="block">
                                <span
                                    class="text-[11px] font-black text-gray-400"
                                >
                                    Bill
                                </span>
                                <select
                                    v-model="selectedBillGroup"
                                    class="mt-1 h-10 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm font-bold outline-none focus:border-[#23AA8F]"
                                >
                                    <option
                                        v-for="group in billGroups"
                                        :key="group"
                                        :value="group"
                                    >
                                        {{ group }}
                                    </option>
                                </select>
                            </label>

                            <div class="grid grid-cols-[1fr_auto] gap-2">
                                <input
                                    v-model="customBillGroup"
                                    type="text"
                                    placeholder="Custom bill name"
                                    class="h-10 min-w-0 rounded-xl border border-gray-200 px-3 text-sm font-bold outline-none focus:border-[#23AA8F]"
                                />
                                <button
                                    type="button"
                                    class="rounded-xl border border-gray-200 px-3 text-[11px] font-black text-[#2A4858] hover:border-[#23AA8F]"
                                    @click="nextBillGroup"
                                >
                                    New
                                </button>
                            </div>

                            <label class="block">
                                <span
                                    class="text-[11px] font-black text-gray-400"
                                >
                                    Table
                                </span>
                                <select
                                    v-model.number="selectedResourceId"
                                    class="mt-1 h-10 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm font-bold outline-none focus:border-[#23AA8F]"
                                >
                                    <option
                                        v-for="resource in resources"
                                        :key="resource.id"
                                        :value="resource.id"
                                    >
                                        {{ resource.name }}
                                    </option>
                                </select>
                            </label>

                            <button
                                type="button"
                                class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-[#2A4858] text-xs font-black text-white transition hover:bg-[#203946] disabled:cursor-not-allowed disabled:bg-gray-300"
                                :disabled="selectedCount === 0"
                                @click="applyToSelected"
                            >
                                Apply to {{ selectedCount }} Item(s)
                                <ChevronRight class="h-4 w-4" />
                            </button>
                        </div>
                    </section>

                    <section class="rounded-2xl bg-white p-4 shadow-sm">
                        <div class="mb-3 flex items-center gap-2">
                            <MoveRight class="h-4 w-4 text-[#23AA8F]" />
                            <h2 class="text-sm font-black">Move Whole Table</h2>
                        </div>
                        <div class="max-h-52 space-y-2 overflow-y-auto pr-1">
                            <button
                                v-for="resource in resources"
                                :key="resource.id"
                                type="button"
                                class="flex w-full items-center justify-between gap-2 rounded-xl border border-gray-100 px-3 py-2 text-left text-xs font-black transition hover:border-[#23AA8F]"
                                @click="moveAllToResource(resource.id)"
                            >
                                <span class="truncate">{{
                                    resource.name
                                }}</span>
                                <span class="text-[10px] text-gray-400">
                                    {{ resource.status || 'active' }}
                                </span>
                            </button>
                        </div>
                    </section>
                    <section class="rounded-2xl bg-white p-4 shadow-sm">
                        <h2 class="mb-3 text-sm font-black">Bill Preview</h2>
                        <div class="space-y-3">
                            <article
                                v-for="group in groupedBills"
                                :key="group.key"
                                class="rounded-xl border border-gray-100 p-3"
                            >
                                <div
                                    class="flex items-start justify-between gap-3"
                                >
                                    <div class="min-w-0">
                                        <p class="text-sm font-black">
                                            {{ group.billGroup }}
                                        </p>
                                        <p
                                            class="truncate text-[11px] font-semibold text-gray-400"
                                        >
                                            {{
                                                group.resource?.name ||
                                                'No table'
                                            }}
                                        </p>
                                    </div>
                                    <p
                                        class="text-sm font-black text-[#007882]"
                                    >
                                        {{ money(group.total) }}
                                    </p>
                                </div>
                                <div class="mt-2 space-y-1">
                                    <div
                                        v-for="line in group.lines"
                                        :key="line.id"
                                        class="flex items-center justify-between gap-2 text-[11px] font-bold text-gray-500"
                                    >
                                        <span class="truncate">
                                            {{ line.menuName }} x{{
                                                line.quantity
                                            }}
                                        </span>
                                        <span class="shrink-0">
                                            {{ money(line.totalAmount) }}
                                        </span>
                                    </div>
                                </div>
                            </article>
                        </div>
                    </section>
                </aside>
            </main>
        </div>
    </AppLayout>
</template>
