<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Maximize2, Minimize2, Search } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import DiningResourceCard, {
    type DiningCustomerOption,
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
        opened_at?: string | null;
    };
    resources: DiningResourceCardItem[];
    types: TypeItem[];
    customers: DiningCustomerOption[];
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

const checkInForm = useForm({
    guest_count: null as number | null,
    customer_id: null as number | null,
    customer_phone: '',
    customer_name: '',
    note: '',
});

const statusOptions = [
    { label: 'All', value: '', dotClass: null },
    { label: 'Available', value: 'available', dotClass: 'bg-[#86D780]' },
    { label: 'Occupied', value: 'occupied', dotClass: 'bg-red-500' },
    { label: 'Booked', value: 'booked', dotClass: 'bg-[#FAFA6E]' },
];

const filteredCount = computed(() => props.resources.length);

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
    },
) {
    checkInForm.guest_count = payload.guestCount;
    checkInForm.customer_id = payload.customerId;
    checkInForm.customer_phone = payload.customerPhone;
    checkInForm.customer_name = payload.customerName;

    checkInForm.post(`/orders/${resource.id}/check-in`, {
        preserveScroll: true,
        onFinish: () => {
            checkInForm.guest_count = null;
            checkInForm.customer_id = null;
            checkInForm.customer_phone = '';
            checkInForm.customer_name = '';
        },
    });
}

function openOrder(resource: DiningResourceCardItem) {
    if (!resource.active_session) return;

    router.visit(`/orders/${resource.active_session.id}/menu`);
}

function editBill(resource: DiningResourceCardItem) {
    if (!resource.active_session) return;

    router.visit(`/orders/${resource.active_session.id}/bill`);
}

function payNow(resource: DiningResourceCardItem) {
    if (!resource.active_session) return;

    router.visit(`/orders/${resource.active_session.id}/payment`);
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
                            <h1
                                class="text-2xl font-black tracking-tight text-[#2A4858]"
                            >
                                Dinning Resource
                            </h1>
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
                        <div class="mt-3 flex flex-wrap gap-2 text-xs">
                            <span
                                class="rounded-full bg-white px-3 py-1 font-bold text-[#2A4858] shadow-sm"
                            >
                                Session: {{ posSession.session_no }}
                            </span>
                            <span
                                v-if="posSession.branch_name"
                                class="rounded-full bg-white px-3 py-1 font-bold text-[#2A4858] shadow-sm"
                            >
                                Branch: {{ posSession.branch_name }}
                            </span>
                            <span
                                v-if="posSession.terminal_name"
                                class="rounded-full bg-white px-3 py-1 font-bold text-[#2A4858] shadow-sm"
                            >
                                Terminal: {{ posSession.terminal_name }}
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
                        :processing="checkInForm.processing"
                        @check-in="checkIn"
                        @open-order="openOrder"
                        @edit-bill="editBill"
                        @pay-now="payNow"
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
