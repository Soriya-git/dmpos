<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, CheckCircle, Info, Trash2 } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import SearchDropdown from '@/components/SearchDropdown.vue';
import AppLayout from '@/layouts/AppLayout.vue';

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

    <AppLayout>
        <form
            class="w-full bg-slate-100 p-4 text-slate-800 md:p-8"
            @submit.prevent="submit"
        >
            <header
                class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-4">
                    <Link
                        href="/stock-customer"
                        class="flex h-10 w-10 items-center justify-center rounded-full border bg-white text-slate-400 transition hover:text-[#007882]"
                    >
                        <ArrowLeft class="h-5 w-5" />
                    </Link>
                    <div>
                        <h1
                            class="text-2xl leading-tight font-black text-[#2a4858]"
                        >
                            {{
                                keep
                                    ? 'Edit Customer Keep Stock'
                                    : 'Create Customer Keep Stock'
                            }}
                        </h1>
                        <p class="text-sm font-medium text-slate-500">
                            Keep customer-owned leftover stock from invoice
                        </p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <Link
                        href="/stock-customer"
                        class="px-6 py-2.5 font-bold text-slate-500 transition hover:text-slate-800"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="!canSubmit"
                        class="rounded-lg bg-[#007882] px-8 py-2.5 font-black text-white shadow-lg transition hover:brightness-110"
                        :class="{ 'cursor-not-allowed opacity-60': !canSubmit }"
                    >
                        {{
                            form.processing
                                ? 'Saving...'
                                : keep
                                  ? 'Update Draft'
                                  : 'Save Draft'
                        }}
                    </button>
                </div>
            </header>

            <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
                <section class="xl:col-span-8">
                    <div
                        class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
                    >
                        <div
                            class="flex flex-col gap-3 border-b bg-slate-50 p-4 md:flex-row md:items-center md:justify-between"
                        >
                            <h2
                                class="text-xs font-black tracking-widest text-slate-500 uppercase"
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
                                class="w-full min-w-[860px] text-left text-sm"
                            >
                                <thead
                                    class="border-b bg-slate-50/50 text-[10px] font-black tracking-widest text-slate-400 uppercase"
                                >
                                    <tr>
                                        <th class="w-10 px-6 py-4"></th>
                                        <th class="px-6 py-4">Item</th>
                                        <th class="px-6 py-4 text-center">
                                            Remaining
                                        </th>
                                        <th class="px-6 py-4 text-center">
                                            Keep Qty
                                        </th>
                                        <th class="px-6 py-4">
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
                                                :checked="allocation.selected"
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
                                                <span class="text-[#007882]">
                                                    /
                                                    {{
                                                        invoiceLineFor(
                                                            allocation.item_id,
                                                        )?.unit_code ?? 'UNIT'
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
                                            <template v-if="selectedInvoice">
                                                All invoice items were removed
                                                from this keep stock document.
                                            </template>
                                            <template v-else>
                                                Select an invoice to load items
                                                for customer keep stock.
                                            </template>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <aside class="space-y-6 xl:col-span-4">
                    <div
                        class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm"
                    >
                        <h2
                            class="mb-5 text-xs font-black tracking-widest text-slate-400 uppercase"
                        >
                            Document Configuration
                        </h2>

                        <div class="space-y-5">
                            <div>
                                <label
                                    class="mb-1.5 block text-[10px] font-black tracking-wider text-slate-500 uppercase"
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
                                    class="mb-1.5 block text-[10px] font-black tracking-wider text-slate-500 uppercase"
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
                                    class="mb-1.5 block text-[10px] font-black tracking-wider text-slate-500 uppercase"
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
                        class="relative overflow-hidden rounded-xl bg-[#2a4858] p-6 text-white shadow-xl"
                    >
                        <h2
                            class="mb-5 text-[10px] font-black tracking-widest uppercase opacity-60"
                        >
                            Keep Summary
                        </h2>
                        <div class="mb-8 space-y-4">
                            <div
                                class="flex items-center justify-between border-b border-white/10 pb-3"
                            >
                                <span class="text-xs font-medium opacity-80"
                                    >Document No:</span
                                >
                                <span class="text-sm font-black">{{
                                    keep?.transfer_no ?? nextTransferNo
                                }}</span>
                            </div>
                            <div
                                class="flex items-center justify-between border-b border-white/10 pb-3"
                            >
                                <span class="text-xs font-medium opacity-80"
                                    >Invoice:</span
                                >
                                <span class="text-sm font-black">{{
                                    selectedInvoice?.invoice_no ?? '-'
                                }}</span>
                            </div>
                            <div
                                class="flex items-center justify-between border-b border-white/10 pb-3"
                            >
                                <span class="text-xs font-medium opacity-80"
                                    >Total Units:</span
                                >
                                <span class="text-sm font-black">{{
                                    totalQuantity
                                }}</span>
                            </div>
                        </div>
                        <button
                            type="submit"
                            :disabled="!canSubmit"
                            class="flex w-full items-center justify-center rounded-lg bg-[#23aa8f] py-3.5 text-sm font-black shadow-2xl transition hover:brightness-110"
                            :class="{
                                'cursor-not-allowed opacity-60': !canSubmit,
                            }"
                        >
                            <CheckCircle class="mr-2 h-4 w-4" />
                            AUTHORIZE KEEP STOCK
                        </button>
                    </div>
                </aside>
            </div>
        </form>
    </AppLayout>
</template>
