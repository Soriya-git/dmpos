<script setup lang="ts">
import { CalendarDays, X } from 'lucide-vue-next';
import { router, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import SearchDropdown from '@/components/SearchDropdown.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

type TransferLine = {
    id: number;
    itemCode: string;
    itemName: string;
    unit: string;
    quantity: number;
    quantityDispatched?: number;
    quantityReceived?: number;
    quantityOutstanding?: number;
    unitCost: number;
    totalCost: number;
    fromWarehouse?: string | null;
    fromLocation?: string | null;
    fromLocationCode?: string | null;
    fromLocationType?: string | null;
    toWarehouse?: string | null;
    toLocation?: string | null;
    toLocationCode?: string | null;
    toLocationType?: string | null;
    note?: string | null;
};

type TransferRecord = {
    id: number;
    code: string;
    date?: string | null;
    displayDate?: string | null;
    approvedAt?: string | null;
    cancelledAt?: string | null;
    fromWarehouse: string;
    toWarehouse: string;
    fromLocation: string;
    toLocation: string;
    itemCount: number;
    totalQuantity: number;
    totalCost: number;
    note?: string | null;
    createdBy: string;
    approvedBy?: string | null;
    cancelledBy?: string | null;
    actionStatus?: string | null;
    status: string;
    lines: TransferLine[];
};

const props = defineProps<{
    transfer: TransferRecord;
    page?: boolean;
    locations: Array<{
        id: number;
        warehouseId: number;
        code: string;
        name: string;
        warehouse: string;
        type: string;
    }>;
}>();

const emit = defineEmits<{
    close: [];
}>();

const allowedTypeLabels: Record<string, string> = {
    putaway: 'Putaway',
    damage: 'Damage',
    obsolete: 'Obsolete',
    scrap: 'Scrap',
};

const receiveForm = useForm({
    lines: props.transfer.lines.map((line) => ({
        id: line.id,
        to_location_id: '' as number | '',
        quantity: Number(line.quantityOutstanding ?? 0),
    })),
});

const destinationOptions = computed(() =>
    props.locations
        .filter(
            (location) => location.warehouse === props.transfer.toWarehouse,
        )
        .map((location) => ({
            value: location.id,
            label: `${location.code} - ${location.name}`,
            description: locationTypeLabel(location.type),
        })),
);

const canReceive = computed(() =>
    receiveForm.lines.some(
        (line) => Number(line.quantity) > 0 && line.to_location_id,
    ),
);

function savePutaway() {
    receiveForm.patch(
        `/stock-movements/internal-transfer/${props.transfer.id}/receive`,
        { preserveScroll: true, onSuccess: () => emit('close') },
    );
}

function rejectInbound() {
    if (!window.confirm('Reject this inbound transfer? All outstanding stock will be returned to the source warehouse.')) return;
    router.patch(
        `/stock-movements/internal-transfer/${props.transfer.id}/reject`,
        {},
        { preserveScroll: true, onSuccess: () => emit('close') },
    );
}

const actionInfo = computed(() => {
    if (props.transfer.actionStatus === 'Approved') {
        return {
            status: 'Approved',
            name: props.transfer.approvedBy,
            at: actionDate(props.transfer.approvedAt),
        };
    }

    if (
        props.transfer.actionStatus === 'Rejected' ||
        props.transfer.actionStatus === 'Cancelled'
    ) {
        return {
            status: props.transfer.actionStatus,
            name: props.transfer.cancelledBy,
            at: actionDate(props.transfer.cancelledAt),
        };
    }

    return null;
});

function actionDate(value: string | null | undefined) {
    if (!value) return null;

    const parsed = new Date(value.replace(' ', 'T'));

    if (!Number.isNaN(parsed.getTime())) {
        return parsed.toLocaleDateString(undefined, {
            month: 'short',
            day: '2-digit',
            year: 'numeric',
        });
    }

    return value.match(/^[A-Za-z]{3}\s+\d{1,2},\s+\d{4}/)?.[0] ?? value;
}

function decimalValue(value: number | string | null | undefined) {
    return Number(String(value ?? 0).replaceAll(',', ''));
}

function money(value: number | string | null | undefined) {
    return decimalValue(value).toLocaleString(undefined, {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

function numberValue(value: number | string | null | undefined) {
    return decimalValue(value).toLocaleString(undefined, {
        minimumFractionDigits: 0,
        maximumFractionDigits: 4,
    });
}

function locationTypeLabel(value: string | null | undefined) {
    return (
        allowedTypeLabels[value ?? ''] ??
        String(value ?? '').replaceAll('_', ' ')
    );
}

function locationTypeClass(value: string | null | undefined) {
    const classes: Record<string, string> = {
        putaway: 'bg-[#23aa8f]/10 text-[#007882]',
        damage: 'bg-red-100 text-red-700',
        obsolete: 'bg-amber-100 text-amber-700',
        scrap: 'bg-slate-200 text-slate-700',
    };

    return classes[value ?? ''] ?? 'bg-slate-100 text-slate-600';
}

function statusLabel(value: string) {
    const labels: Record<string, string> = {
        draft: 'Draft',
        approved: 'Approved',
        in_transit: 'Awaiting Putaway',
        received: 'Completed',
        cancelled: 'Cancelled',
        rejected: 'Rejected',
    };

    return labels[value] ?? value.replaceAll('_', ' ');
}

function statusClass(value: string) {
    const classes: Record<string, string> = {
        draft: 'bg-amber-100 text-amber-700',
        approved: 'bg-blue-100 text-blue-700',
        in_transit: 'bg-blue-100 text-blue-700',
        received: 'bg-green-100 text-green-700',
        cancelled: 'bg-slate-100 text-slate-600',
        rejected: 'bg-red-100 text-red-700',
    };

    return classes[value] ?? 'bg-slate-100 text-slate-600';
}
</script>

<template>
    <div
        :class="
            page
                ? 'w-full'
                : 'fixed inset-0 z-[75] flex items-center justify-center bg-[#2a4858]/20 p-4 backdrop-blur-sm'
        "
        @click.self="emit('close')"
    >
        <section
            :class="[
                'flex w-full flex-col overflow-hidden rounded-lg bg-white',
                page ? 'shadow-sm' : 'max-h-[90vh] max-w-6xl shadow-2xl',
            ]"
        >
            <header
                class="flex items-start justify-between gap-4 border-b border-slate-100 p-5"
            >
                <div>
                    <p
                        class="text-xs font-bold tracking-widest text-slate-400 uppercase"
                    >
                        Internal Transfer Detail
                    </p>
                    <h2 class="mt-1 text-xl font-bold text-[#2a4858]">
                        {{ transfer.code }}
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ transfer.fromWarehouse }} to
                        {{ transfer.toWarehouse }}
                    </p>
                </div>
                <Button
                    v-if="!page"
                    type="button"
                    variant="outline"
                    class="h-9 w-9 rounded-lg border-slate-100 p-0 text-slate-400"
                    title="Close"
                    @click="emit('close')"
                >
                    <X class="size-4" />
                </Button>
            </header>

            <div class="min-h-0 flex-1 overflow-y-auto p-5">
                <div class="mb-4 grid gap-3 text-sm sm:grid-cols-3">
                    <div class="space-y-1 rounded-lg bg-slate-50 p-3">
                        <p class="text-xs font-bold text-slate-400 uppercase">
                            Schedule
                        </p>
                        <p class="font-bold text-[#2a4858]">
                            Date: {{ transfer.displayDate ?? '-' }}
                        </p>
                        <p class="text-xs font-semibold text-[#007882]">
                            {{ transfer.fromLocation }} to
                            {{ transfer.toLocation }}
                        </p>
                    </div>
                    <div class="space-y-2 rounded-lg bg-slate-50 p-3">
                        <p class="text-xs font-bold text-slate-400 uppercase">
                            Status
                        </p>
                        <span
                            class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold uppercase"
                            :class="statusClass(transfer.status)"
                        >
                            {{ statusLabel(transfer.status) }}
                        </span>
                        <p class="text-xs font-semibold text-slate-500">
                            {{ transfer.itemCount }} item{{
                                transfer.itemCount === 1 ? '' : 's'
                            }}
                            /
                            {{ numberValue(transfer.totalQuantity) }} qty
                        </p>
                    </div>
                    <div class="space-y-1 rounded-lg bg-slate-50 p-3">
                        <p class="text-xs font-bold text-slate-400 uppercase">
                            People
                        </p>
                        <p class="font-bold text-[#2a4858]">
                            Created: {{ transfer.createdBy ?? '-' }}
                        </p>
                        <p class="text-xs font-semibold text-slate-500">
                            <span v-if="actionInfo">
                                {{ actionInfo.status
                                }}<template v-if="actionInfo.at">
                                    at
                                    {{ actionInfo.at }}</template
                                >
                                by {{ actionInfo.name ?? '-' }}
                            </span>
                            <span v-else>-</span>
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[920px] text-left text-sm">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th
                                    class="py-3 pr-4 text-xs font-bold text-slate-400 uppercase"
                                >
                                    Item
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold text-slate-400 uppercase"
                                >
                                    From
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold text-slate-400 uppercase"
                                >
                                    To
                                </th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-bold text-slate-400 uppercase"
                                >
                                    Qty
                                </th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-bold text-slate-400 uppercase"
                                >
                                    Cost
                                </th>
                                <th
                                    class="py-3 pl-4 text-right text-xs font-bold text-slate-400 uppercase"
                                >
                                    Total
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="line in transfer.lines"
                                :key="line.id"
                                class="border-b border-slate-50"
                            >
                                <td class="py-4 pr-4">
                                    <div class="font-bold text-[#2a4858]">
                                        {{ line.itemName }}
                                    </div>
                                    <p class="mt-1 text-xs text-slate-400">
                                        {{ line.itemCode }} / {{ line.unit }}
                                    </p>
                                </td>
                                <td class="px-4 py-4">
                                    <p class="font-bold text-[#2a4858]">
                                        {{ line.fromWarehouse ?? '-' }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-400">
                                        {{ line.fromLocationCode }} -
                                        {{ line.fromLocation }}
                                    </p>
                                    <span
                                        v-if="line.fromLocationType"
                                        class="mt-2 inline-flex rounded-full px-2 py-0.5 text-[11px] font-bold uppercase"
                                        :class="
                                            locationTypeClass(
                                                line.fromLocationType,
                                            )
                                        "
                                    >
                                        {{
                                            locationTypeLabel(
                                                line.fromLocationType,
                                            )
                                        }}
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    <p class="font-bold text-[#2a4858]">
                                        {{ line.toWarehouse ?? '-' }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-400">
                                        {{ line.toLocationCode }} -
                                        {{ line.toLocation }}
                                    </p>
                                    <span
                                        v-if="line.toLocationType"
                                        class="mt-2 inline-flex rounded-full px-2 py-0.5 text-[11px] font-bold uppercase"
                                        :class="
                                            locationTypeClass(
                                                line.toLocationType,
                                            )
                                        "
                                    >
                                        {{
                                            locationTypeLabel(
                                                line.toLocationType,
                                            )
                                        }}
                                    </span>
                                </td>
                                <td
                                    class="px-4 py-4 text-right font-bold text-[#007882]"
                                >
                                    {{ numberValue(line.quantity) }}
                                </td>
                                <td
                                    class="px-4 py-4 text-right font-mono font-bold text-[#2a4858]"
                                >
                                    {{ money(line.unitCost) }}
                                </td>
                                <td
                                    class="py-4 pl-4 text-right font-mono font-bold text-[#007882]"
                                >
                                    {{ money(line.totalCost) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div
                    v-if="transfer.status === 'in_transit'"
                    class="mt-5 rounded-lg border border-blue-100 bg-blue-50/50 p-4"
                >
                    <h3 class="font-bold text-[#2a4858]">Destination Putaway</h3>
                    <p class="mt-1 text-sm text-slate-500">
                        Choose a location and receive all or part of each outstanding quantity.
                    </p>
                    <div class="mt-4 space-y-3">
                        <div
                            v-for="(line, index) in transfer.lines"
                            :key="line.id"
                            class="grid gap-3 rounded-lg bg-white p-3 md:grid-cols-[minmax(0,1fr)_minmax(15rem,1fr)_9rem] md:items-end"
                        >
                            <div>
                                <p class="font-bold text-[#2a4858]">{{ line.itemName }}</p>
                                <p class="text-xs text-slate-500">
                                    Received {{ numberValue(line.quantityReceived) }} / Dispatched {{ numberValue(line.quantityDispatched) }} {{ line.unit }}
                                </p>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold text-slate-500">Putaway Location</label>
                                <SearchDropdown
                                    v-model="receiveForm.lines[index].to_location_id"
                                    :options="destinationOptions"
                                    placeholder="Choose location"
                                    search-placeholder="Search location..."
                                />
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold text-slate-500">Qty to Putaway</label>
                                <Input
                                    v-model.number="receiveForm.lines[index].quantity"
                                    type="number"
                                    min="0"
                                    :max="line.quantityOutstanding"
                                    step="0.0001"
                                />
                            </div>
                        </div>
                    </div>
                    <p v-if="receiveForm.errors.lines" class="mt-3 text-sm font-semibold text-red-600">
                        {{ receiveForm.errors.lines }}
                    </p>
                    <div class="mt-4 flex justify-end gap-2">
                        <Button
                            type="button"
                            variant="destructive"
                            :disabled="receiveForm.processing"
                            @click="rejectInbound"
                        >
                            Reject Transfer
                        </Button>
                        <Button
                            type="button"
                            class="bg-[#007882] text-white hover:bg-[#006773]"
                            :disabled="!canReceive || receiveForm.processing"
                            @click="savePutaway"
                        >
                            Accept & Putaway
                        </Button>
                    </div>
                </div>
            </div>

            <footer
                class="flex flex-col justify-between gap-3 border-t border-slate-100 bg-slate-50 p-5 sm:flex-row sm:items-center"
            >
                <div class="flex items-center gap-2 text-sm text-slate-500">
                    <CalendarDays class="size-4 text-[#007882]" />
                    {{ transfer.lines.length }} item line{{
                        transfer.lines.length === 1 ? '' : 's'
                    }}
                </div>
                <div class="text-right">
                    <p
                        class="text-xs font-bold tracking-widest text-slate-400 uppercase"
                    >
                        Transfer Value
                    </p>
                    <p class="text-xl font-bold text-[#007882]">
                        {{ money(transfer.totalCost) }}
                    </p>
                </div>
            </footer>
        </section>
    </div>
</template>
