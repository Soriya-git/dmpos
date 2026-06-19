<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Boxes, Info, Trash2 } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import SearchDropdown from '@/components/SearchDropdown.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

type InvoiceLine = {
    item_id: number;
    unit_id: number;
    item_name?: string | null;
    item_code?: string | null;
    unit_code?: string | null;
    quantity_invoiced: number;
    quantity_kept: number;
    quantity_remaining: number;
};

type Invoice = {
    id: number;
    invoice_no: string;
    customer_name: string;
    customer_phone?: string | null;
    total_remaining: number;
    lines: InvoiceLine[];
};

type CustomerLocation = {
    id: number;
    name: string;
    code?: string | null;
    warehouse_name?: string | null;
};

type EditableKeep = {
    id: number;
    transfer_no: string;
    invoice_id: number;
    note?: string | null;
    lines: {
        allocation_key: string;
        item_id: number;
        unit_id: number;
        to_location_id: number | '';
        quantity: number;
        selected: boolean;
    }[];
};

const props = defineProps<{
    nextTransferNo: string;
    keep?: EditableKeep | null;
    invoice?: Invoice | null;
    invoices: Invoice[];
    customerLocations: CustomerLocation[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Stock Operations' },
    { title: 'Stock Movements' },
    { title: 'Customer Keep Stock', href: '/stock-customer' },
    {
        title: props.keep ? 'Edit' : 'Create',
        href: '/stock-customer/create',
    },
];

const form = useForm({
    invoice_id: props.keep?.invoice_id ?? props.invoice?.id ?? '',
    note: props.keep?.note ?? '',
    lines: (props.keep?.lines ?? []) as {
        allocation_key: string;
        item_id: number;
        unit_id: number;
        to_location_id: number | '';
        quantity: number;
        selected: boolean;
    }[],
});
const hydratedDraft = ref(Boolean(props.keep));

const invoiceOptions = computed(() =>
    props.invoices.map((invoice) => ({
        value: invoice.id,
        label: invoice.invoice_no,
        description: invoice.customer_name,
        meta: `${invoice.total_remaining} remaining${invoice.customer_phone ? ` / ${invoice.customer_phone}` : ''}`,
    })),
);

const selectedInvoice = computed(() => {
    return (
        props.invoices.find(
            (invoice) => invoice.id === Number(form.invoice_id),
        ) ??
        props.invoice ??
        null
    );
});

watch(
    selectedInvoice,
    (invoice) => {
        if (hydratedDraft.value) {
            hydratedDraft.value = false;
            return;
        }

        form.lines =
            invoice?.lines.map((line) => ({
                allocation_key: `${line.item_id}-${Date.now()}`,
                item_id: line.item_id,
                unit_id: line.unit_id,
                to_location_id: props.customerLocations[0]?.id ?? '',
                quantity: Number(line.quantity_remaining || 0),
                selected: true,
            })) ?? [];
    },
    { immediate: true },
);

const selectedLines = computed(() =>
    form.lines.filter((line) => line.selected && Number(line.quantity) > 0),
);
const totalQuantity = computed(() =>
    selectedLines.value.reduce(
        (total, line) => total + Number(line.quantity || 0),
        0,
    ),
);
const canSubmit = computed(
    () =>
        Boolean(form.invoice_id) &&
        selectedLines.value.length > 0 &&
        selectedLines.value.every((line) => line.to_location_id) &&
        !form.processing,
);

function invoiceLineFor(itemId: number) {
    return selectedInvoice.value?.lines.find((line) => line.item_id === itemId);
}

function maxQuantityFor(itemId: number) {
    return Number(invoiceLineFor(itemId)?.quantity_remaining ?? 0);
}

function setSelected(allocationKey: string, event: Event) {
    const line = form.lines.find((row) => row.allocation_key === allocationKey);

    if (line) line.selected = (event.target as HTMLInputElement).checked;
}

function setQuantity(allocationKey: string, event: Event) {
    const line = form.lines.find((row) => row.allocation_key === allocationKey);

    if (line) {
        const value = Number((event.target as HTMLInputElement).value || 0);
        line.quantity = Math.min(
            Math.max(0, value),
            maxQuantityFor(line.item_id),
        );
    }
}

function setDestination(allocationKey: string, event: Event) {
    const line = form.lines.find((row) => row.allocation_key === allocationKey);

    if (line) {
        const value = (event.target as HTMLSelectElement).value;
        line.to_location_id = value ? Number(value) : '';
    }
}

function removeLine(allocationKey: string) {
    form.lines = form.lines.filter(
        (line) => line.allocation_key !== allocationKey,
    );
}

function showDashboard() {
    router.visit('/stock-customer');
}

function submit() {
    if (props.keep) {
        form.patch(`/stock-customer/${props.keep.id}`, {
            preserveScroll: true,
        });
        return;
    }

    form.post('/stock-customer', { preserveScroll: true });
}
</script>

<template>
    <Head title="Create Customer Keep Stock" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #actions>
            <div class="flex gap-2">
                <Button
                    type="button"
                    variant="ghost"
                    class="h-9 font-semibold text-slate-600 hover:text-red-500"
                    :disabled="form.processing"
                    @click="showDashboard"
                >
                    Cancel
                </Button>
                <Button
                    type="button"
                    class="h-9 rounded-lg bg-[#007882] px-4 text-xs font-bold text-white shadow-md hover:bg-[#006773]"
                    :disabled="!canSubmit"
                    @click="submit"
                >
                    {{
                        form.processing
                            ? 'Saving...'
                            : keep
                              ? 'Update Draft'
                              : 'Save Draft'
                    }}
                </Button>
            </div>
        </template>

        <form
            class="h-[calc(100dvh-4rem)] w-full [scrollbar-gutter:stable] overflow-y-scroll bg-[#f8fafc] p-4 text-slate-800 md:h-[calc(100dvh-5rem)] md:p-6 xl:p-8 2xl:p-10"
            @submit.prevent="submit"
        >
            <section class="w-full">
                <div class="grid gap-6 xl:grid-cols-4">
                    <div class="space-y-6 xl:order-2 xl:col-span-3">
                        <div
                            class="overflow-hidden rounded-lg border border-slate-100 bg-white shadow-sm"
                        >
                            <div
                                class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50 p-4 md:flex-row md:items-center md:justify-between"
                            >
                                <h2
                                    class="text-xs font-bold tracking-wider text-slate-700 uppercase"
                                >
                                    Invoice Items to Keep
                                </h2>
                                <div
                                    class="flex items-center rounded-full border border-teal-100 bg-teal-50 px-3 py-1 text-[10px] font-bold text-[#007882]"
                                >
                                    <Info class="mr-1.5 h-3.5 w-3.5" />
                                    Items loaded from selected invoice
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table
                                    class="w-full min-w-[920px] text-left text-sm"
                                >
                                    <thead
                                        class="border-b border-slate-100 text-slate-500"
                                    >
                                        <tr>
                                            <th class="w-10 px-6 py-4"></th>
                                            <th
                                                class="min-w-64 px-6 py-4 text-left font-semibold"
                                            >
                                                Item
                                            </th>
                                            <th
                                                class="px-6 py-4 text-center font-semibold"
                                            >
                                                Remaining
                                            </th>
                                            <th
                                                class="px-6 py-4 text-center font-semibold"
                                            >
                                                Keep Qty
                                            </th>
                                            <th
                                                class="min-w-64 px-6 py-4 text-left font-semibold"
                                            >
                                                Customer Location
                                            </th>
                                            <th class="w-12 px-6 py-4"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <tr
                                            v-for="allocation in form.lines"
                                            :key="allocation.allocation_key"
                                            class="transition hover:bg-slate-50/30"
                                        >
                                            <td class="px-6 py-4">
                                                <input
                                                    type="checkbox"
                                                    :checked="
                                                        allocation.selected
                                                    "
                                                    class="rounded border-slate-300 text-[#007882]"
                                                    @change="
                                                        setSelected(
                                                            allocation.allocation_key,
                                                            $event,
                                                        )
                                                    "
                                                />
                                            </td>
                                            <td class="px-6 py-4">
                                                <div
                                                    class="font-bold text-slate-800"
                                                >
                                                    {{
                                                        invoiceLineFor(
                                                            allocation.item_id,
                                                        )?.item_name ?? 'Item'
                                                    }}
                                                </div>
                                                <div
                                                    class="text-[10px] font-bold text-slate-400 uppercase"
                                                >
                                                    SKU:
                                                    {{
                                                        invoiceLineFor(
                                                            allocation.item_id,
                                                        )?.item_code ?? '-'
                                                    }}
                                                    <span
                                                        class="text-[#007882]"
                                                    >
                                                        /
                                                        {{
                                                            invoiceLineFor(
                                                                allocation.item_id,
                                                            )?.unit_code ??
                                                            'UNIT'
                                                        }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td
                                                class="px-6 py-4 text-center font-bold text-slate-400 italic"
                                            >
                                                {{
                                                    invoiceLineFor(
                                                        allocation.item_id,
                                                    )?.quantity_remaining ?? 0
                                                }}
                                                units
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <input
                                                    type="number"
                                                    min="0"
                                                    :max="
                                                        maxQuantityFor(
                                                            allocation.item_id,
                                                        )
                                                    "
                                                    :value="allocation.quantity"
                                                    class="w-24 rounded-lg border border-slate-200 px-3 py-1.5 text-center font-black text-[#007882]"
                                                    @input="
                                                        setQuantity(
                                                            allocation.allocation_key,
                                                            $event,
                                                        )
                                                    "
                                                />
                                            </td>
                                            <td class="px-6 py-4">
                                                <select
                                                    :value="
                                                        allocation.to_location_id
                                                    "
                                                    class="w-full rounded-lg border border-slate-200 bg-slate-50 p-2 text-xs font-bold text-slate-600"
                                                    @change="
                                                        setDestination(
                                                            allocation.allocation_key,
                                                            $event,
                                                        )
                                                    "
                                                >
                                                    <option value="" disabled>
                                                        Select location
                                                    </option>
                                                    <option
                                                        v-for="location in customerLocations"
                                                        :key="location.id"
                                                        :value="location.id"
                                                    >
                                                        {{
                                                            location.code ??
                                                            location.name
                                                        }}
                                                        -
                                                        {{
                                                            location.warehouse_name
                                                        }}
                                                    </option>
                                                </select>
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <button
                                                    type="button"
                                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-300 transition hover:bg-rose-50 hover:text-rose-600"
                                                    title="Remove item"
                                                    @click="
                                                        removeLine(
                                                            allocation.allocation_key,
                                                        )
                                                    "
                                                >
                                                    <Trash2 class="h-4 w-4" />
                                                </button>
                                            </td>
                                        </tr>
                                        <tr
                                            v-if="
                                                !selectedInvoice ||
                                                form.lines.length === 0
                                            "
                                        >
                                            <td
                                                colspan="6"
                                                class="px-6 py-12 text-center text-sm text-slate-500"
                                            >
                                                <template
                                                    v-if="selectedInvoice"
                                                >
                                                    All invoice items were
                                                    removed from this keep stock
                                                    document.
                                                </template>
                                                <template v-else>
                                                    Select an invoice to load
                                                    items for customer keep
                                                    stock.
                                                </template>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <aside class="space-y-6 xl:order-1 xl:col-span-1">
                        <div
                            class="overflow-hidden rounded-lg border border-slate-100 bg-white shadow-sm"
                        >
                            <div class="border-b border-slate-100 p-5">
                                <h2
                                    class="text-xs font-bold tracking-wider text-slate-700 uppercase"
                                >
                                    Document Configuration
                                </h2>
                            </div>

                            <div class="space-y-4 p-5">
                                <div>
                                    <label
                                        class="mb-1.5 block text-xs font-bold text-slate-500 uppercase"
                                    >
                                        Document Number
                                    </label>
                                    <Input
                                        :model-value="
                                            keep?.transfer_no ?? nextTransferNo
                                        "
                                        readonly
                                        class="h-10 rounded-lg border-slate-200 bg-slate-50 font-mono text-slate-500"
                                    />
                                </div>

                                <div>
                                    <label
                                        class="mb-1.5 block text-xs font-bold text-slate-500 uppercase"
                                    >
                                        Select Invoice
                                    </label>
                                    <SearchDropdown
                                        v-model="form.invoice_id"
                                        :options="invoiceOptions"
                                        placeholder="Search and select invoice"
                                        search-placeholder="Search invoice or customer..."
                                        empty-text="No invoice found."
                                    />
                                </div>

                                <div>
                                    <label
                                        class="mb-1.5 block text-xs font-bold text-slate-500 uppercase"
                                    >
                                        Customer
                                    </label>
                                    <div
                                        class="rounded-lg border border-slate-100 bg-slate-50 p-3 text-sm"
                                    >
                                        <div class="font-bold text-[#2a4858]">
                                            {{
                                                selectedInvoice?.customer_name ??
                                                'No invoice selected'
                                            }}
                                        </div>
                                        <div class="text-xs text-slate-400">
                                            {{
                                                selectedInvoice?.customer_phone ??
                                                '-'
                                            }}
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label
                                        class="mb-1.5 block text-xs font-bold text-slate-500 uppercase"
                                    >
                                        Note
                                    </label>
                                    <textarea
                                        v-model="form.note"
                                        rows="3"
                                        placeholder="Customer keep instructions..."
                                        class="w-full resize-none rounded-lg border border-slate-200 bg-slate-50/50 p-3 text-sm"
                                    ></textarea>
                                </div>
                            </div>
                        </div>

                        <div
                            class="rounded-lg bg-[#2a4858] p-6 text-white shadow-lg"
                        >
                            <div class="space-y-4">
                                <div
                                    class="flex items-center gap-3 border-b border-white/10 pb-4"
                                >
                                    <div
                                        class="flex size-11 items-center justify-center rounded-lg bg-white/10"
                                    >
                                        <Boxes class="size-5 text-[#fafa6e]" />
                                    </div>
                                    <div>
                                        <span class="text-lg font-bold">
                                            Keep Summary
                                        </span>
                                        <p class="mt-1 text-sm text-white/60">
                                            Draft status until approved.
                                        </p>
                                    </div>
                                </div>
                                <div
                                    class="flex items-center justify-between border-b border-white/10 pb-3"
                                >
                                    <span
                                        class="text-xs font-medium text-white/70"
                                        >Invoice</span
                                    >
                                    <span class="text-sm font-black">{{
                                        selectedInvoice?.invoice_no ?? '-'
                                    }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span
                                        class="text-xs font-medium text-white/70"
                                        >Total Units</span
                                    >
                                    <span
                                        class="text-2xl font-bold text-[#fafa6e]"
                                        >{{ totalQuantity }}</span
                                    >
                                </div>
                            </div>
                        </div>
                    </aside>
                </div>
            </section>
        </form>
    </AppLayout>
</template>
