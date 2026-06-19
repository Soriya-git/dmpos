<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Cable, MonitorCog, Network } from 'lucide-vue-next';
import { computed, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';

type BranchOption = {
    id: number;
    name: string;
    code: string | null;
};

export type PrinterRecord = {
    id: number;
    branchId: number;
    branch: string;
    code: string | null;
    name: string;
    printerType: 'receipt' | 'invoice' | 'kitchen' | 'general';
    printerRole: 'cashier' | 'kitchen' | 'stock' | 'bar' | 'general';
    connectionType: 'native_app' | 'usb_bridge' | 'network' | 'browser_dialog';
    networkProtocol: string;
    ipAddress: string | null;
    hostName: string | null;
    port: number | null;
    timeoutMs: number;
    paperSize: string;
    isDefault: boolean;
    isActive: boolean;
    description: string | null;
};

const props = defineProps<{
    branchOptions: BranchOption[];
    defaultBranchId: number | null;
    printer?: PrinterRecord | null;
}>();

const emit = defineEmits<{
    success: [];
}>();

const form = useForm({
    branch_id: '',
    name: '',
    code: '',
    printer_type: 'receipt',
    printer_role: 'cashier',
    connection_type: 'browser_dialog',
    network_protocol: 'browser',
    ip_address: '',
    host_name: '',
    port: '',
    timeout_ms: '5000',
    paper_size: '80mm',
    is_default: false,
    is_active: true,
    description: '',
});

const isEditing = computed(() => Boolean(props.printer));
const canSubmit = computed(
    () => form.branch_id !== '' && form.name.trim().length > 0,
);

const connectionSummary = computed(() => {
    if (form.connection_type === 'browser_dialog') {
        return 'Uses the browser print dialog on this workstation.';
    }

    if (form.connection_type === 'network') {
        const host = form.ip_address || form.host_name || 'network host';
        return `${host}:${form.port || '9100'} by ${form.network_protocol || 'raw_tcp'}.`;
    }

    if (form.connection_type === 'usb_bridge') {
        return 'Routes through the installed USB bridge service.';
    }

    return 'Routes through the native printer helper application.';
});

watch(
    () => props.printer,
    () => resetForm(),
    { immediate: true },
);

watch(
    () => form.connection_type,
    (connectionType) => {
        if (connectionType === 'browser_dialog') {
            form.network_protocol = 'browser';
            form.port = '';
            form.ip_address = '';
            form.host_name = '';
            return;
        }

        if (connectionType === 'network') {
            form.network_protocol =
                form.network_protocol === 'browser'
                    ? 'raw_tcp'
                    : form.network_protocol || 'raw_tcp';
            form.port = form.port || '9100';
        }
    },
);

function resetForm() {
    form.clearErrors();

    if (props.printer) {
        form.branch_id = String(props.printer.branchId);
        form.name = props.printer.name;
        form.code = props.printer.code ?? '';
        form.printer_type = props.printer.printerType;
        form.printer_role = props.printer.printerRole;
        form.connection_type = props.printer.connectionType;
        form.network_protocol = props.printer.networkProtocol;
        form.ip_address = props.printer.ipAddress ?? '';
        form.host_name = props.printer.hostName ?? '';
        form.port = props.printer.port ? String(props.printer.port) : '';
        form.timeout_ms = String(props.printer.timeoutMs);
        form.paper_size = props.printer.paperSize;
        form.is_default = props.printer.isDefault;
        form.is_active = props.printer.isActive;
        form.description = props.printer.description ?? '';
        return;
    }

    form.reset();
    form.branch_id = props.defaultBranchId
        ? String(props.defaultBranchId)
        : props.branchOptions[0]
          ? String(props.branchOptions[0].id)
          : '';
    form.printer_type = 'receipt';
    form.printer_role = 'cashier';
    form.connection_type = 'browser_dialog';
    form.network_protocol = 'browser';
    form.timeout_ms = '5000';
    form.paper_size = '80mm';
    form.is_active = true;
}

function submit() {
    form.clearErrors();

    if (!canSubmit.value) {
        if (!form.branch_id) {
            form.setError('branch_id', 'Branch is required.');
        }

        if (!form.name.trim()) {
            form.setError('name', 'Printer name is required.');
        }

        return;
    }

    const options = {
        preserveScroll: true,
        onSuccess: () => {
            resetForm();
            emit('success');
        },
    };

    if (props.printer) {
        form.patch(`/access-control/printers/${props.printer.id}`, options);
        return;
    }

    form.post('/access-control/printers', options);
}

defineExpose({ submit, isProcessing: computed(() => form.processing) });
</script>

<template>
    <div class="w-full">
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-4 2xl:gap-8">
            <div class="space-y-6 xl:col-span-1">
                <div
                    class="rounded-lg border border-slate-100 bg-white p-6 shadow-sm"
                >
                    <h3
                        class="mb-4 flex items-center text-sm font-bold text-slate-700 uppercase"
                    >
                        <MonitorCog class="mr-2 size-4 text-[#007882]" />
                        Printer Info
                    </h3>

                    <div class="space-y-4">
                        <div>
                            <label
                                class="mb-1 block text-xs font-bold text-slate-500 uppercase"
                            >
                                Branch
                            </label>
                            <select
                                v-model="form.branch_id"
                                class="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-600 outline-none focus:border-[#007882] focus:ring-2 focus:ring-[#007882]/20"
                            >
                                <option value="" disabled>Select branch</option>
                                <option
                                    v-for="branch in branchOptions"
                                    :key="branch.id"
                                    :value="String(branch.id)"
                                >
                                    {{ branch.name }}
                                </option>
                            </select>
                            <InputError :message="form.errors.branch_id" />
                        </div>

                        <div>
                            <label
                                class="mb-1 block text-xs font-bold text-slate-500 uppercase"
                            >
                                Printer Name
                            </label>
                            <Input
                                v-model="form.name"
                                class="h-10 rounded-lg border-slate-200"
                                placeholder="Kitchen Printer"
                            />
                            <InputError :message="form.errors.name" />
                        </div>

                        <div>
                            <label
                                class="mb-1 block text-xs font-bold text-slate-500 uppercase"
                            >
                                Printer Code
                            </label>
                            <Input
                                v-model="form.code"
                                class="h-10 rounded-lg border-slate-200 font-mono"
                                placeholder="PRN-KITCHEN-01"
                            />
                            <InputError :message="form.errors.code" />
                        </div>

                        <div>
                            <label
                                class="mb-1 block text-xs font-bold text-slate-500 uppercase"
                            >
                                Note
                            </label>
                            <textarea
                                v-model="form.description"
                                class="min-h-20 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#007882] focus:ring-2 focus:ring-[#007882]/20"
                                placeholder="Location, device model, or setup note"
                            />
                            <InputError :message="form.errors.description" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6 xl:col-span-3">
                <div
                    class="overflow-hidden rounded-lg border border-slate-100 bg-white shadow-sm"
                >
                    <div
                        class="flex items-center border-b border-slate-100 bg-slate-50 p-4"
                    >
                        <Cable class="mr-2 size-4 text-[#007882]" />
                        <h3
                            class="text-xs font-bold tracking-wider text-slate-700 uppercase"
                        >
                            Routing & Connection
                        </h3>
                    </div>

                    <div class="grid grid-cols-1 gap-6 p-6 md:grid-cols-2">
                        <div>
                            <label
                                class="mb-1 block text-xs font-bold text-slate-500 uppercase"
                            >
                                Printer Type
                            </label>
                            <select
                                v-model="form.printer_type"
                                class="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-600 outline-none focus:border-[#007882] focus:ring-2 focus:ring-[#007882]/20"
                            >
                                <option value="receipt">Receipt</option>
                                <option value="invoice">Invoice</option>
                                <option value="kitchen">Kitchen</option>
                                <option value="general">General</option>
                            </select>
                            <InputError :message="form.errors.printer_type" />
                        </div>

                        <div>
                            <label
                                class="mb-1 block text-xs font-bold text-slate-500 uppercase"
                            >
                                Printer Role
                            </label>
                            <select
                                v-model="form.printer_role"
                                class="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-600 outline-none focus:border-[#007882] focus:ring-2 focus:ring-[#007882]/20"
                            >
                                <option value="cashier">Cashier</option>
                                <option value="kitchen">Kitchen</option>
                                <option value="stock">Stock</option>
                                <option value="bar">Bar</option>
                                <option value="general">General</option>
                            </select>
                            <InputError :message="form.errors.printer_role" />
                        </div>

                        <div>
                            <label
                                class="mb-1 block text-xs font-bold text-slate-500 uppercase"
                            >
                                Connection Type
                            </label>
                            <select
                                v-model="form.connection_type"
                                class="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-600 outline-none focus:border-[#007882] focus:ring-2 focus:ring-[#007882]/20"
                            >
                                <option value="browser_dialog">
                                    Browser Dialog
                                </option>
                                <option value="network">Network</option>
                                <option value="usb_bridge">USB Bridge</option>
                                <option value="native_app">Native App</option>
                            </select>
                            <InputError
                                :message="form.errors.connection_type"
                            />
                        </div>

                        <div>
                            <label
                                class="mb-1 block text-xs font-bold text-slate-500 uppercase"
                            >
                                Network Protocol
                            </label>
                            <Input
                                v-model="form.network_protocol"
                                class="h-10 rounded-lg border-slate-200"
                                placeholder="raw_tcp"
                            />
                            <InputError
                                :message="form.errors.network_protocol"
                            />
                        </div>

                        <div>
                            <label
                                class="mb-1 block text-xs font-bold text-slate-500 uppercase"
                            >
                                IP Address
                            </label>
                            <Input
                                v-model="form.ip_address"
                                class="h-10 rounded-lg border-slate-200 font-mono"
                                placeholder="192.168.1.100"
                            />
                            <InputError :message="form.errors.ip_address" />
                        </div>

                        <div>
                            <label
                                class="mb-1 block text-xs font-bold text-slate-500 uppercase"
                            >
                                Host Name
                            </label>
                            <Input
                                v-model="form.host_name"
                                class="h-10 rounded-lg border-slate-200"
                                placeholder="kitchen-printer.local"
                            />
                            <InputError :message="form.errors.host_name" />
                        </div>

                        <div>
                            <label
                                class="mb-1 block text-xs font-bold text-slate-500 uppercase"
                            >
                                Port
                            </label>
                            <Input
                                v-model="form.port"
                                type="number"
                                class="h-10 rounded-lg border-slate-200 font-mono"
                                placeholder="9100"
                            />
                            <InputError :message="form.errors.port" />
                        </div>

                        <div>
                            <label
                                class="mb-1 block text-xs font-bold text-slate-500 uppercase"
                            >
                                Timeout (ms)
                            </label>
                            <Input
                                v-model="form.timeout_ms"
                                type="number"
                                class="h-10 rounded-lg border-slate-200 font-mono"
                                placeholder="5000"
                            />
                            <InputError :message="form.errors.timeout_ms" />
                        </div>

                        <div>
                            <label
                                class="mb-1 block text-xs font-bold text-slate-500 uppercase"
                            >
                                Paper Size
                            </label>
                            <select
                                v-model="form.paper_size"
                                class="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-600 outline-none focus:border-[#007882] focus:ring-2 focus:ring-[#007882]/20"
                            >
                                <option value="58mm">58mm</option>
                                <option value="80mm">80mm</option>
                                <option value="A4">A4</option>
                            </select>
                            <InputError :message="form.errors.paper_size" />
                        </div>

                        <div class="space-y-3">
                            <label
                                class="flex items-center gap-3 rounded-lg border border-slate-100 bg-slate-50 px-4 py-3"
                            >
                                <input
                                    v-model="form.is_default"
                                    type="checkbox"
                                    class="size-4 rounded border-slate-300 text-[#007882] focus:ring-[#007882]/30"
                                />
                                <span>
                                    <span
                                        class="block text-xs font-bold text-slate-600 uppercase"
                                    >
                                        Default for role
                                    </span>
                                    <span class="text-xs text-slate-400">
                                        Replaces the current branch default for
                                        this role.
                                    </span>
                                </span>
                            </label>

                            <label
                                class="flex items-center gap-3 rounded-lg border border-slate-100 bg-slate-50 px-4 py-3"
                            >
                                <input
                                    v-model="form.is_active"
                                    type="checkbox"
                                    class="size-4 rounded border-slate-300 text-[#007882] focus:ring-[#007882]/30"
                                />
                                <span>
                                    <span
                                        class="block text-xs font-bold text-slate-600 uppercase"
                                    >
                                        Active
                                    </span>
                                    <span class="text-xs text-slate-400">
                                        Available for print routing.
                                    </span>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-[#2a4858] p-6 text-white shadow-lg">
                    <div
                        class="flex flex-col justify-between gap-4 md:flex-row md:items-center"
                    >
                        <div>
                            <p
                                class="text-xs font-bold text-white/50 uppercase"
                            >
                                {{ isEditing ? 'Updating' : 'Registering' }}
                            </p>
                            <p class="mt-0.5 text-lg font-bold">
                                {{ form.name.trim() || 'New Printer' }}
                            </p>
                        </div>
                        <div class="flex items-center gap-3 md:text-right">
                            <Network class="size-5 text-[#fafa6e]" />
                            <div>
                                <p
                                    class="text-xs font-bold text-white/50 uppercase"
                                >
                                    Connection
                                </p>
                                <p
                                    class="mt-0.5 max-w-lg text-sm font-semibold text-[#fafa6e]"
                                >
                                    {{ connectionSummary }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
