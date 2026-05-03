<script setup lang="ts">
import {
    CalendarCheck,
    Clock,
    CreditCard,
    DoorOpen,
    ImageIcon,
    LogIn,
    ReceiptText,
    Utensils,
    Users,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

type ActiveSession = {
    id: number;
    session_no: string;
    status: string;
    guest_count?: number | null;
    opened_at?: string | null;
    customer_name?: string | null;
    customer_phone?: string | null;
};

export type DiningResourceCardItem = {
    id: number;
    name: string;
    code?: string | null;
    capacity: number;
    status: string;
    image?: string | null;
    description?: string | null;
    type?: {
        id?: number | null;
        name?: string | null;
    };
    active_session?: ActiveSession | null;
};

export type DiningCustomerOption = {
    id: number;
    name?: string | null;
    phone_number: string;
};

const props = defineProps<{
    resource: DiningResourceCardItem;
    customers: DiningCustomerOption[];
    processing?: boolean;
}>();

const emit = defineEmits<{
    checkIn: [
        resource: DiningResourceCardItem,
        payload: {
            guestCount: number | null;
            customerId: number | null;
            customerPhone: string;
            customerName: string;
        },
    ];
    openOrder: [resource: DiningResourceCardItem];
    editBill: [resource: DiningResourceCardItem];
    payNow: [resource: DiningResourceCardItem];
}>();

const guestCount = ref<number | null>(props.resource.capacity || null);
const customerId = ref<number | null>(null);
const customerPhone = ref('');
const customerName = ref('');
const customerDropdownOpen = ref(false);
const imageFailed = ref(false);

const imageSrc = computed(() => {
    const path = props.resource.image;

    if (!path || imageFailed.value) return null;
    if (path.startsWith('http')) return path;

    return `/storage/${path}`;
});

const resourceStatus = computed(() => props.resource.status.toLowerCase());
const isAvailable = computed(() => resourceStatus.value === 'available');
const isOccupied = computed(() => Boolean(props.resource.active_session));
const isBooked = computed(() => resourceStatus.value === 'booked');

const cardClass = computed(() => {
    return isBooked.value
        ? 'border-2 border-[#FAFA6E]'
        : 'border border-gray-100';
});

const statusLabelClass = computed(() => {
    return (
        {
            available: 'text-green-600',
            occupied: 'text-red-500',
            booked: 'text-yellow-600',
            maintenance: 'text-slate-500',
            disabled: 'text-zinc-500',
        }[resourceStatus.value] ?? 'text-slate-500'
    );
});

const typeBadgeClass = computed(() => {
    return isAvailable.value
        ? 'bg-[#86D780]/20 text-[#2A4858]'
        : 'bg-gray-100 text-gray-500 tracking-widest';
});

const fallbackIcon = computed(() => {
    if (isBooked.value) return CalendarCheck;
    if (isAvailable.value) return DoorOpen;

    return Utensils;
});

const openedMeta = computed(() => {
    return props.resource.active_session?.opened_at ?? null;
});

const filteredCustomers = computed(() => {
    const query = customerPhone.value.trim().toLowerCase();

    if (!query) return props.customers.slice(0, 6);

    return props.customers
        .filter((customer) => {
            return (
                customer.phone_number.toLowerCase().includes(query) ||
                (customer.name ?? '').toLowerCase().includes(query)
            );
        })
        .slice(0, 6);
});

const canAddCustomer = computed(() => {
    const phone = customerPhone.value.trim();

    return (
        phone !== '' &&
        !props.customers.some((customer) => customer.phone_number === phone)
    );
});

const customerLine = computed(() => {
    const session = props.resource.active_session;

    if (!session) return null;

    const name = session.customer_name ?? 'Walk-in / General Customer';
    const phone = session.customer_phone ? ` (${session.customer_phone})` : '';

    return `${name}${phone}`;
});

function selectCustomer(customer: DiningCustomerOption) {
    customerId.value = customer.id;
    customerPhone.value = customer.phone_number;
    customerName.value = customer.name ?? '';
    customerDropdownOpen.value = false;
}

function addCustomerFromPhone() {
    customerId.value = null;
    customerPhone.value = customerPhone.value.trim();
    customerDropdownOpen.value = false;
}

function checkIn() {
    emit('checkIn', props.resource, {
        guestCount: guestCount.value,
        customerId: customerId.value,
        customerPhone: customerPhone.value.trim(),
        customerName: customerName.value.trim(),
    });
}
</script>

<template>
    <article
        class="resource-card flex flex-col overflow-hidden rounded-xl bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-[0_15px_30px_-10px_rgba(42,72,88,0.2)]"
        :class="[cardClass, resourceStatus === 'occupied' ? 'opacity-90' : '']"
    >
        <div class="relative p-2">
            <img
                v-if="imageSrc"
                :src="imageSrc"
                :alt="resource.name"
                class="h-20 w-full rounded-lg bg-slate-200 object-cover"
                @error="imageFailed = true"
            />

            <div
                v-else
                class="flex h-20 w-full items-center justify-center rounded-lg bg-slate-200"
                :class="isBooked ? 'bg-[#FAFA6E]/10' : ''"
            >
                <component
                    :is="imageFailed ? ImageIcon : fallbackIcon"
                    class="h-8 w-8"
                    :class="isBooked ? 'text-[#2A4858]/30' : 'text-gray-300'"
                />
            </div>

            <div
                v-if="isOccupied"
                class="absolute top-4 right-4 inline-flex items-center gap-1 rounded bg-red-600 px-1.5 py-0.5 text-[9px] font-bold text-white shadow-lg"
            >
                <Clock class="h-3 w-3" />
                {{ openedMeta ?? resource.active_session?.session_no }}
            </div>
        </div>

        <div class="flex flex-1 flex-col px-3 pb-3">
            <div class="mb-1.5 flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <h3 class="truncate text-base font-bold text-[#2A4858]">
                        {{ resource.name }}
                    </h3>
                    <span
                        class="inline-flex rounded px-1.5 py-0.5 text-[9px] font-bold uppercase"
                        :class="typeBadgeClass"
                    >
                        {{ resource.type?.name ?? 'No Type' }}
                    </span>
                </div>

                <div class="shrink-0 text-right">
                    <p
                        class="text-[10px] font-bold uppercase"
                        :class="statusLabelClass"
                    >
                        {{ resource.status }}
                    </p>
                    <p
                        class="mt-0.5 inline-flex items-center justify-end gap-1 text-[9px] text-gray-400"
                    >
                        <Users class="h-3 w-3" />
                        {{ resource.capacity }} Seats
                    </p>
                </div>
            </div>

            <template v-if="isOccupied">
                <div
                    class="mt-2 rounded-lg border border-gray-100 bg-gray-50 p-2"
                >
                    <p
                        class="text-[9px] font-bold tracking-wider text-gray-400 uppercase"
                    >
                        Customer
                    </p>
                    <p class="mt-1 truncate text-xs font-bold text-[#2A4858]">
                        {{ customerLine }}
                    </p>
                    <p class="mt-1 text-[9px] text-gray-400">
                        {{ resource.active_session?.session_no }}
                    </p>
                </div>

                <div class="mt-3 flex gap-2">
                    <Button
                        type="button"
                        variant="secondary"
                        class="h-8 flex-1 rounded-lg bg-gray-100 px-2 text-[11px] font-bold text-[#2A4858] hover:bg-gray-200"
                        @click="emit('editBill', resource)"
                    >
                        <ReceiptText class="h-4 w-4" />
                        EDIT BILL
                    </Button>
                    <Button
                        type="button"
                        class="h-8 flex-1 rounded-lg bg-[#23AA8F] px-2 text-[11px] font-bold text-white shadow-sm hover:bg-[#007882]"
                        @click="emit('payNow', resource)"
                    >
                        <CreditCard class="h-4 w-4" />
                        PAY NOW
                    </Button>
                </div>

                <Button
                    type="button"
                    variant="outline"
                    class="mt-2 h-8 w-full rounded-lg border-gray-200 text-[11px] font-bold text-[#2A4858] hover:bg-gray-50"
                    @click="emit('openOrder', resource)"
                >
                    VIEW
                </Button>
            </template>

            <template v-else-if="isBooked">
                <div
                    class="mt-3 rounded-lg border border-yellow-100 bg-yellow-50 p-2"
                >
                    <p
                        class="text-[9px] font-bold tracking-wider text-yellow-800 uppercase"
                    >
                        Reservation
                    </p>
                    <p class="mt-1 text-xs font-bold text-[#2A4858]">
                        Ready for customer check-in
                    </p>
                </div>
                <Button
                    type="button"
                    class="mt-2 h-8 w-full rounded-lg bg-[#2A4858] text-xs font-bold text-white hover:bg-[#203946]"
                    :disabled="processing"
                    @click="checkIn"
                >
                    <LogIn class="h-4 w-4" />
                    CHECK IN
                </Button>
            </template>

            <template v-else>
                <div class="mt-3 space-y-2">
                    <div class="relative">
                        <Input
                            v-model="customerPhone"
                            type="tel"
                            placeholder="Customer phone"
                            class="h-8 rounded-lg border-gray-200 bg-gray-50 px-2 text-xs focus-visible:ring-[#007882]/30"
                            @focus="customerDropdownOpen = true"
                            @input="
                                customerId = null;
                                customerDropdownOpen = true;
                            "
                        />

                        <div
                            v-if="customerPhone && customerDropdownOpen"
                            class="absolute right-0 left-0 z-10 mt-1 max-h-32 overflow-auto rounded-lg border border-gray-100 bg-white p-1 shadow-lg"
                        >
                            <button
                                v-for="customer in filteredCustomers"
                                :key="customer.id"
                                type="button"
                                class="block w-full rounded px-2 py-1.5 text-left text-xs hover:bg-gray-50"
                                @click="selectCustomer(customer)"
                            >
                                <span class="font-bold text-[#2A4858]">
                                    {{ customer.phone_number }}
                                </span>
                                <span class="ml-1 text-gray-500">
                                    {{ customer.name ?? 'Customer' }}
                                </span>
                            </button>

                            <button
                                v-if="canAddCustomer"
                                type="button"
                                class="block w-full rounded bg-[#007882]/10 px-2 py-1.5 text-left text-xs font-bold text-[#007882]"
                                @click="addCustomerFromPhone"
                            >
                                Add {{ customerPhone }}
                            </button>
                        </div>
                    </div>

                    <Input
                        v-if="canAddCustomer"
                        v-model="customerName"
                        type="text"
                        placeholder="Customer name (optional)"
                        class="h-8 rounded-lg border-gray-200 bg-gray-50 px-2 text-xs focus-visible:ring-[#007882]/30"
                    />

                    <div class="relative">
                        <Users
                            class="absolute top-2.5 left-2 h-3.5 w-3.5 text-gray-400"
                        />
                        <Input
                            v-model.number="guestCount"
                            type="number"
                            min="1"
                            :max="resource.capacity"
                            placeholder="Guests"
                            class="h-8 rounded-lg border-gray-200 bg-gray-50 pr-2 pl-7 text-xs focus-visible:ring-[#007882]/30"
                        />
                    </div>
                    <Button
                        type="button"
                        class="h-8 w-full rounded-lg bg-[#007882] text-xs font-bold text-white shadow-md hover:bg-[#2A4858] active:scale-95"
                        :disabled="processing"
                        @click="checkIn"
                    >
                        OPEN
                    </Button>
                </div>
            </template>
        </div>
    </article>
</template>
