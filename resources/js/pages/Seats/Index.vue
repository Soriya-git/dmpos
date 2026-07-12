<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    ChevronDown,
    Maximize2,
    Minimize2,
    MonitorCog,
    Power,
    Search,
} from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import DiningResourceCard, {
    type DiningCustomerOption,
    type DiningPriceListOption,
    type DiningResourceCardItem,
} from '@/components/seats/DiningResourceCard.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';

type TypeItem = {
    id: number;
    name: string;
};

const props = defineProps<{
    posSession: {
        id: number;
        session_no: string;
        branch_name?: string | null;
        terminal_name?: string | null;
        opened_by?: string | null;
        can_close?: boolean;
        opened_at?: string | null;
    } | null;
    openPosSessions: {
        id: number;
        session_no: string;
        branch_name?: string | null;
        terminal_name?: string | null;
        opened_by?: string | null;
        can_close?: boolean;
        opened_at?: string | null;
    }[];
    availablePosTerminals: {
        id: number;
        name: string;
        code?: string | null;
        branch_name?: string | null;
    }[];
    requiresPosSessionSelection: boolean;
    resources: DiningResourceCardItem[];
    types: TypeItem[];
    customers: DiningCustomerOption[];
    priceLists: DiningPriceListOption[];
    filters: {
        status?: string | null;
        type_id?: number | string | null;
        search?: string | null;
    };
}>();

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const typeId = ref(props.filters.type_id ?? '');
const pageRoot = ref<HTMLElement | null>(null);
const isFullscreen = ref(false);
const closeProcessingSessionId = ref<number | null>(null);
const selectedPosSessionId = ref<number | string>(props.posSession?.id ?? '');
const sessionSelectorOpen = ref(false);

const checkInForm = useForm({
    pos_session_id: null as number | null,
    guest_count: null as number | null,
    customer_id: null as number | null,
    customer_phone: '',
    customer_name: '',
    menu_price_list_id: null as number | null,
    note: '',
});

const statusOptions = [
    { label: 'All', value: '', dotClass: null },
    { label: 'Available', value: 'available', dotClass: 'bg-[#86D780]' },
    { label: 'Occupied', value: 'occupied', dotClass: 'bg-red-500' },
    { label: 'Booked', value: 'booked', dotClass: 'bg-[#FAFA6E]' },
];

const filteredCount = computed(() => props.resources.length);
const posIsOpen = computed(() => Boolean(props.posSession));
const hasOpenPosSessions = computed(() => props.openPosSessions.length > 0);
const hasAvailablePosTerminal = computed(
    () => props.availablePosTerminals.length > 0,
);
const canCloseSelectedPosSession = computed(
    () => props.posSession?.can_close === true,
);
const posActionLabel = computed(() => {
    if (canCloseSelectedPosSession.value) return 'CLOSE NOW';
    if (hasAvailablePosTerminal.value) return 'OPEN';
    if (posIsOpen.value) return 'POS SESSION';

    return 'OPEN NOW';
});

const summary = computed(() => {
    return {
        available: props.resources.filter((r) => r.status === 'available')
            .length,
        occupied: props.resources.filter((r) => r.status === 'occupied').length,
        booked: props.resources.filter((r) => r.status === 'booked').length,
    };
});

const zoneOptions = computed(() => [{ id: '', name: 'All' }, ...props.types]);

function applyFilters() {
    router.get(
        '/orders',
        {
            pos_session_id: selectedPosSessionId.value || undefined,
            search: search.value || undefined,
            status: status.value || undefined,
            type_id: typeId.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

function selectPosSession() {
    sessionSelectorOpen.value = false;

    router.get(
        '/orders',
        {
            pos_session_id: selectedPosSessionId.value || undefined,
            search: search.value || undefined,
            status: status.value || undefined,
            type_id: typeId.value || undefined,
        },
        {
            preserveScroll: true,
            replace: true,
        },
    );
}

function choosePosSession(sessionId: number) {
    selectedPosSessionId.value = sessionId;
    selectPosSession();
}

function selectZone(value: number | string) {
    typeId.value = value;
    applyFilters();
}

function selectStatus(value: string) {
    status.value = value;
    applyFilters();
}

function checkIn(
    resource: DiningResourceCardItem,
    payload: {
        guestCount: number | null;
        customerId: number | null;
        customerPhone: string;
        customerName: string;
        priceListId: number | null;
    },
) {
    checkInForm.guest_count = payload.guestCount;
    checkInForm.customer_id = payload.customerId;
    checkInForm.customer_phone = payload.customerPhone;
    checkInForm.customer_name = payload.customerName;
    checkInForm.menu_price_list_id = payload.priceListId;
    checkInForm.pos_session_id = props.posSession?.id ?? null;

    checkInForm.post(`/orders/${resource.id}/check-in`, {
        preserveScroll: true,
        onFinish: () => {
            checkInForm.pos_session_id = null;
            checkInForm.guest_count = null;
            checkInForm.customer_id = null;
            checkInForm.customer_phone = '';
            checkInForm.customer_name = '';
            checkInForm.menu_price_list_id = null;
        },
    });
}

function openOrder(resource: DiningResourceCardItem) {
    if (!resource.active_session) return;

    router.visit(
        `/orders/${resource.active_session.id}/menu${
            props.posSession?.id ? `?pos_session_id=${props.posSession.id}` : ''
        }`,
    );
}

function closeOrder(resource: DiningResourceCardItem) {
    if (!resource.active_session) return;

    closeProcessingSessionId.value = resource.active_session.id;

    router.post(
        `/orders/${resource.active_session.id}/close`,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                closeProcessingSessionId.value = null;
            },
        },
    );
}

function openPosSessionPage() {
    router.visit('/pos-sessions');
}

function openAnotherPosSessionPage() {
    router.visit('/pos-sessions?open_new=1');
}

async function toggleFullscreen() {
    if (isFullscreen.value) {
        if (document.fullscreenElement) {
            await document.exitFullscreen();
        }

        isFullscreen.value = false;
        return;
    }

    if (pageRoot.value?.requestFullscreen) {
        await pageRoot.value.requestFullscreen();
    }

    isFullscreen.value = true;
}

function syncFullscreenState() {
    isFullscreen.value = document.fullscreenElement === pageRoot.value;
}

onMounted(() => {
    document.addEventListener('fullscreenchange', syncFullscreenState);
});

onBeforeUnmount(() => {
    document.removeEventListener('fullscreenchange', syncFullscreenState);
});
</script>

<template>
    <Head title="Orders / Seats" />

    <AppLayout>
        <main
            ref="pageRoot"
            class="min-h-screen bg-[#f0f4f5] p-3 md:p-4"
            :class="
                isFullscreen ? 'fixed inset-0 z-50 overflow-auto' : 'relative'
            "
        >
            <div class="mx-auto max-w-7xl">
                <header
                    class="mb-4 flex flex-col gap-3 md:flex-row md:items-end md:justify-between"
                >
                    <div>
                        <div class="flex flex-wrap items-center gap-3">
                            <Button
                                type="button"
                                class="h-10 rounded-lg px-4 text-xs font-black tracking-wide text-white shadow-lg transition"
                                :class="
                                    canCloseSelectedPosSession
                                        ? 'bg-red-600 shadow-red-600/20 hover:bg-red-700'
                                        : 'bg-[#23AA8F] shadow-[#23AA8F]/25 hover:bg-[#007882] disabled:cursor-not-allowed disabled:bg-slate-300 disabled:text-slate-500 disabled:shadow-none'
                                "
                                :disabled="
                                    Boolean(posSession) &&
                                    !canCloseSelectedPosSession
                                "
                                @click="openPosSessionPage"
                            >
                                <Power
                                    v-if="canCloseSelectedPosSession"
                                    class="h-4 w-4"
                                />
                                <MonitorCog v-else class="h-4 w-4" />
                                {{ posActionLabel }}
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                class="h-8 rounded-lg border-gray-200 px-3 text-xs font-bold text-[#2A4858]"
                                @click="toggleFullscreen"
                            >
                                <Minimize2
                                    v-if="isFullscreen"
                                    class="h-4 w-4"
                                />
                                <Maximize2 v-else class="h-4 w-4" />
                                {{
                                    isFullscreen
                                        ? 'Exit Full Screen'
                                        : 'Full Screen'
                                }}
                            </Button>
                        </div>
                        <div
                            class="mt-3 flex flex-wrap items-center gap-2 text-xs"
                        >
                            <span
                                v-if="hasOpenPosSessions && !posSession"
                                class="rounded-full border border-amber-100 bg-amber-50 px-3 py-1 font-bold text-amber-700 shadow-sm"
                            >
                                Select a POS session before opening a resource.
                            </span>
                            <div
                                v-if="posSession"
                                class="relative inline-flex items-center overflow-visible rounded-full border border-[#23AA8F]/20 bg-white shadow-md ring-1 shadow-slate-200/70 ring-white"
                            >
                                <button
                                    v-if="hasAvailablePosTerminal"
                                    type="button"
                                    class="rounded-l-full bg-[#23AA8F] px-3 py-1.5 text-[10px] font-black tracking-wide text-white transition hover:bg-[#007882] disabled:cursor-not-allowed disabled:bg-slate-200 disabled:text-slate-500"
                                    @click="openAnotherPosSessionPage"
                                >
                                    Open Another Terminal
                                </button>
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 font-bold text-[#2A4858] transition hover:bg-[#23AA8F]/10"
                                    :class="
                                        hasAvailablePosTerminal
                                            ? 'rounded-r-full'
                                            : 'rounded-full'
                                    "
                                    @click="
                                        sessionSelectorOpen =
                                            !sessionSelectorOpen
                                    "
                                >
                                    <span class="text-slate-500">
                                        Session:
                                    </span>
                                    <span
                                        class="text-[#007882] underline decoration-[#23AA8F]/40 underline-offset-2"
                                    >
                                        {{ posSession.session_no }}
                                    </span>
                                    <ChevronDown
                                        class="h-3.5 w-3.5 text-slate-400 transition"
                                        :class="
                                            sessionSelectorOpen
                                                ? 'rotate-180'
                                                : 'rotate-0'
                                        "
                                    />
                                </button>

                                <div
                                    v-if="sessionSelectorOpen"
                                    class="absolute top-9 left-0 z-40 w-72 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-2xl shadow-slate-300/60"
                                >
                                    <button
                                        v-for="session in openPosSessions"
                                        :key="session.id"
                                        type="button"
                                        class="flex w-full flex-col border-b border-slate-100 px-3 py-2 text-left last:border-b-0 hover:bg-[#23AA8F]/10"
                                        :class="
                                            posSession.id === session.id
                                                ? 'bg-[#23AA8F]/10'
                                                : ''
                                        "
                                        @click="choosePosSession(session.id)"
                                    >
                                        <span
                                            class="text-xs font-black text-[#2A4858]"
                                        >
                                            {{ session.session_no }}
                                            <span
                                                v-if="
                                                    posSession.id === session.id
                                                "
                                                class="text-[#007882]"
                                            >
                                                Current
                                            </span>
                                        </span>
                                        <span
                                            class="mt-0.5 text-[10px] font-medium text-slate-500"
                                        >
                                            {{ session.terminal_name }} /
                                            {{ session.opened_by }}
                                        </span>
                                    </button>
                                </div>
                            </div>
                            <span
                                v-if="posSession?.branch_name"
                                class="rounded-full border border-white bg-white px-3 py-1 font-bold text-[#2A4858] shadow-md shadow-slate-200/60"
                            >
                                Branch: {{ posSession.branch_name }}
                            </span>
                            <span
                                v-if="posSession?.terminal_name"
                                class="rounded-full border border-white bg-white px-3 py-1 font-bold text-[#2A4858] shadow-md shadow-slate-200/60"
                            >
                                Terminal: {{ posSession.terminal_name }}
                            </span>
                            <span
                                v-if="!posSession && !hasOpenPosSessions"
                                class="rounded-full border border-red-100 bg-red-50 px-3 py-1 font-bold text-red-600 shadow-sm"
                            >
                                POS is closed. Open a POS session before opening
                                a resource.
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-2 text-center">
                        <div class="rounded-xl bg-white px-4 py-3 shadow-sm">
                            <div class="text-xl font-black text-green-600">
                                {{ summary.available }}
                            </div>
                            <div class="text-[10px] font-bold text-[#2A4858]">
                                Available
                            </div>
                        </div>
                        <div class="rounded-xl bg-white px-4 py-3 shadow-sm">
                            <div class="text-xl font-black text-red-500">
                                {{ summary.occupied }}
                            </div>
                            <div class="text-[10px] font-bold text-[#2A4858]">
                                Occupied
                            </div>
                        </div>
                        <div class="rounded-xl bg-white px-4 py-3 shadow-sm">
                            <div class="text-xl font-black text-yellow-600">
                                {{ summary.booked }}
                            </div>
                            <div class="text-[10px] font-bold text-[#2A4858]">
                                Booked
                            </div>
                        </div>
                    </div>
                </header>

                <section
                    class="sticky top-3 z-20 mb-5 flex flex-col gap-3 rounded-xl border border-gray-100 bg-white p-3 shadow-sm"
                >
                    <div class="flex flex-wrap items-center gap-2">
                        <Button
                            v-for="zone in zoneOptions"
                            :key="zone.id || 'all'"
                            type="button"
                            variant="outline"
                            class="h-8 rounded-lg border-gray-200 px-3 text-xs font-bold transition-all hover:border-[#007882]"
                            :class="
                                String(typeId) === String(zone.id)
                                    ? 'border-[#007882] bg-[#007882] text-white hover:bg-[#007882]'
                                    : 'bg-white text-[#2A4858]'
                            "
                            @click="selectZone(zone.id)"
                        >
                            {{ zone.name }}
                        </Button>
                    </div>

                    <div
                        class="flex w-full flex-col gap-2 md:flex-row md:items-center md:justify-between"
                    >
                        <div class="flex flex-wrap items-center gap-2">
                            <Button
                                v-for="item in statusOptions"
                                :key="item.value"
                                type="button"
                                variant="outline"
                                class="h-8 rounded-lg border-gray-200 px-3 text-xs font-bold transition-all hover:border-[#007882]"
                                :class="
                                    status === item.value
                                        ? 'border-[#007882] bg-[#007882] text-white hover:bg-[#007882]'
                                        : 'bg-white text-[#2A4858]'
                                "
                                @click="selectStatus(item.value)"
                            >
                                <span
                                    v-if="item.dotClass"
                                    class="h-2.5 w-2.5 rounded-full"
                                    :class="item.dotClass"
                                ></span>
                                {{ item.label }}
                            </Button>
                        </div>

                        <div class="relative min-w-0 flex-1 md:max-w-xs">
                            <Search
                                class="absolute top-2.5 left-3 h-4 w-4 text-gray-400"
                            />
                            <Input
                                v-model="search"
                                type="text"
                                placeholder="Search table..."
                                class="h-9 rounded-xl border-gray-200 bg-gray-50 pr-3 pl-9 text-sm focus-visible:ring-[#007882]/30"
                                @keyup.enter="applyFilters"
                            />
                        </div>
                    </div>
                </section>

                <div class="mb-4 flex items-center justify-between">
                    <p class="text-sm font-medium text-[#2A4858]/60">
                        Showing {{ filteredCount }} resource(s)
                    </p>
                </div>

                <section
                    v-if="resources.length > 0"
                    class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5"
                >
                    <DiningResourceCard
                        v-for="resource in resources"
                        :key="resource.id"
                        :resource="resource"
                        :customers="customers"
                        :price-lists="priceLists"
                        :processing="checkInForm.processing"
                        :pos-open="posIsOpen"
                        :close-processing="
                            closeProcessingSessionId ===
                            resource.active_session?.id
                        "
                        @check-in="checkIn"
                        @open-order="openOrder"
                        @close-order="closeOrder"
                    />
                </section>

                <section
                    v-else
                    class="rounded-2xl border border-dashed border-[#2A4858]/20 bg-white p-10 text-center"
                >
                    <p class="font-bold text-[#2A4858]">
                        No room or table found.
                    </p>
                    <p class="mt-1 text-sm text-[#2A4858]/50">
                        Try another zone, status, or search term.
                    </p>
                </section>
            </div>
        </main>
    </AppLayout>
</template>
