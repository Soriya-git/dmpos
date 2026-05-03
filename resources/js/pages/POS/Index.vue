<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import {
    Banknote,
    Calculator,
    CircleDollarSign,
    FileText,
    LockKeyhole,
    LogOut,
    Monitor,
    PlayCircle,
    UserCircle,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import PosSessionPanel from '@/components/pos/PosSessionPanel.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';

type Terminal = {
    id: number;
    name: string;
    code?: string | null;
    device_type?: string | null;
    is_active: boolean;
    branch?: {
        id: number;
        name: string;
    } | null;
};

type Session = {
    id: number;
    session_no: string;
    status: 'open' | 'closed' | 'cancelled';
    pos_terminal_id: number;
    opening_cash_usd: number | string;
    opening_cash_khr: number | string;
    total_cash_usd?: number | string;
    total_cash_khr?: number | string;
    pos_terminal?: Terminal | null;
    posTerminal?: Terminal | null;
};

const props = defineProps<{
    sessions: Session[];
    terminals: Terminal[];
    currentSession?: Session | null;
}>();

const page = usePage();

const currentUserName = computed(() => {
    return (page.props.auth as any)?.user?.name ?? 'Current User';
});

const openedTerminalIds = computed(() => {
    return props.sessions
        .filter((session) => session.status === 'open')
        .map((session) => session.pos_terminal_id);
});

const availableTerminals = computed(() => {
    return props.terminals.filter((terminal) => {
        return (
            terminal.is_active && !openedTerminalIds.value.includes(terminal.id)
        );
    });
});

const selectedSession = computed(() => {
    return props.currentSession ?? null;
});

const openForm = useForm({
    pos_terminal_id: '',
    opening_cash_khr: '',
    opening_cash_usd: '',
    opening_note: '',
});

const closeForm = useForm({
    actual_cash_khr: '',
    actual_cash_usd: '',
    closing_note: '',
});

const displayOpeningCashKhr = ref('');
const displayOpeningCashUsd = ref('');

const cleanNumber = (value: string) => {
    return value.replace(/[^\d.]/g, '');
};

const formatInputMoney = (value: string) => {
    const clean = cleanNumber(value);

    if (!clean) {
        return '';
    }

    const number = Number(clean);

    if (Number.isNaN(number)) {
        return '';
    }

    return number.toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

const onInputKhr = () => {
    const clean = cleanNumber(displayOpeningCashKhr.value);
    openForm.opening_cash_khr = clean;
    displayOpeningCashKhr.value = clean; // no format while typing
};

const onBlurKhr = () => {
    if (!openForm.opening_cash_khr) return;

    displayOpeningCashKhr.value =
        formatInputMoney(openForm.opening_cash_khr) + ' KHR';
};

const onInputUsd = () => {
    const clean = cleanNumber(displayOpeningCashUsd.value);
    openForm.opening_cash_usd = clean;
    displayOpeningCashUsd.value = clean;
};

const onBlurUsd = () => {
    if (!openForm.opening_cash_usd) return;

    displayOpeningCashUsd.value =
        formatInputMoney(openForm.opening_cash_usd) + ' USD';
};

const submitOpen = () => {
    openForm.clearErrors();

    if (!openForm.pos_terminal_id) {
        openForm.setError('pos_terminal_id', 'Please select POS terminal.');
        return;
    }

    if (!openForm.opening_cash_khr) {
        openForm.setError('opening_cash_khr', 'Start Cash KHR is required.');
        return;
    }

    if (!openForm.opening_cash_usd) {
        openForm.setError('opening_cash_usd', 'Start Cash USD is required.');
        return;
    }

    openForm.post('/pos-sessions/open', {
        preserveScroll: true,
        onSuccess: () => {
            openForm.reset();
            displayOpeningCashKhr.value = '';
            displayOpeningCashUsd.value = '';
        },
    });
};

const submitClose = (session: Session) => {
    closeForm.post(`/pos-sessions/${session.id}/close`, {
        preserveScroll: true,
        onSuccess: () => closeForm.reset(),
    });
};

const closePopup = () => {
    if (window.history.length > 1) {
        window.history.back();
        return;
    }

    router.visit('/dashboard');
};

const cancelForm = () => {
    openForm.reset();
    openForm.clearErrors();
    displayOpeningCashKhr.value = '';
    displayOpeningCashUsd.value = '';
    closePopup();
};

const cancelClose = () => {
    closeForm.reset();
    closeForm.clearErrors();
    closePopup();
};

const logout = () => {
    router.post('/logout');
};

const formatMoney = (value: number | string | null | undefined) => {
    return Number(value ?? 0).toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

const getSessionTerminalName = (session: Session) => {
    return session.posTerminal?.name ?? session.pos_terminal?.name ?? '-';
};

const closeSessionError = computed(() => {
    return (closeForm.errors as Record<string, string>).session;
});
</script>

<template>
    <Head title="POS Session" />

    <AppLayout>
        <div
            class="fixed inset-0 z-40 flex items-center justify-center overflow-y-auto bg-slate-950/45 bg-[radial-gradient(circle_at_top_right,#e0f2f1,transparent_32rem),radial-gradient(circle_at_bottom_left,#d1d5db,transparent_30rem)] p-4 backdrop-blur-sm sm:p-6"
        >
            <div
                v-if="($page.props.flash as any)?.success"
                class="absolute top-4 left-1/2 w-[calc(100%-2rem)] max-w-md -translate-x-1/2 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-[#2a4858]"
            >
                {{ ($page.props.flash as any).success }}
            </div>

            <div
                v-if="($page.props.flash as any)?.error"
                class="absolute top-4 left-1/2 w-[calc(100%-2rem)] max-w-md -translate-x-1/2 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700"
            >
                {{ ($page.props.flash as any).error }}
            </div>

            <div class="mx-auto w-full max-w-md">
                <PosSessionPanel v-if="!selectedSession">
                    <template #header-actions>
                        <button
                            type="button"
                            class="rounded-md p-2 text-white/70 transition hover:bg-white/10 hover:text-white"
                            title="Logout"
                            @click="logout"
                        >
                            <LogOut class="size-5" />
                        </button>
                    </template>

                    <template #icon>
                        <Calculator class="size-8" />
                    </template>

                    <template #title>Open POS Shift</template>

                    <template #description>
                        Ready to start your business day
                    </template>

                    <form class="space-y-5" @submit.prevent="submitOpen">
                        <div class="space-y-1.5">
                            <Label
                                class="text-sm font-bold tracking-wider text-[#2a4858] uppercase"
                            >
                                POS Number (Terminal)
                            </Label>

                            <div class="relative">
                                <Monitor
                                    class="absolute top-3.5 left-3 size-5 text-[#007882]/70"
                                />

                                <select
                                    v-model="openForm.pos_terminal_id"
                                    class="h-12 w-full appearance-none rounded-lg border border-slate-200 bg-slate-50 py-3 pr-4 pl-10 text-sm transition outline-none focus:border-[#007882] focus:ring-2 focus:ring-teal-500/30 disabled:cursor-not-allowed disabled:opacity-60"
                                    :disabled="availableTerminals.length === 0"
                                    required
                                >
                                    <option value="" disabled>
                                        Select Terminal
                                    </option>

                                    <option
                                        v-for="terminal in availableTerminals"
                                        :key="terminal.id"
                                        :value="terminal.id"
                                    >
                                        {{ terminal.name }}
                                        <template v-if="terminal.code">
                                            - {{ terminal.code }}
                                        </template>
                                        <template v-if="terminal.branch">
                                            / {{ terminal.branch.name }}
                                        </template>
                                    </option>
                                </select>
                            </div>

                            <InputError
                                :message="openForm.errors.pos_terminal_id"
                            />

                            <p
                                v-if="availableTerminals.length === 0"
                                class="text-sm font-medium text-red-600"
                            >
                                No available POS terminal. All active terminals
                                are already opened.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="space-y-1.5">
                                <Label
                                    class="text-sm font-bold tracking-wider text-[#2a4858] uppercase"
                                >
                                    Cash (KHR)
                                </Label>

                                <div class="relative">
                                    <Banknote
                                        class="absolute top-3.5 left-3 size-5 text-[#007882]/70"
                                    />

                                    <input
                                        v-model="displayOpeningCashKhr"
                                        type="text"
                                        placeholder="0.00 KHR"
                                        class="h-12 w-full rounded-lg border border-slate-200 bg-slate-50 py-3 pr-4 pl-10 text-sm transition outline-none focus:border-[#007882] focus:ring-2 focus:ring-teal-500/30"
                                        @input="onInputKhr"
                                        @blur="onBlurKhr"
                                    />
                                </div>

                                <InputError
                                    :message="openForm.errors.opening_cash_khr"
                                />
                            </div>

                            <div class="space-y-1.5">
                                <Label
                                    class="text-sm font-bold tracking-wider text-[#2a4858] uppercase"
                                >
                                    Cash (USD)
                                </Label>

                                <div class="relative">
                                    <CircleDollarSign
                                        class="absolute top-3.5 left-3 size-5 text-[#007882]/70"
                                    />

                                    <input
                                        v-model="displayOpeningCashUsd"
                                        type="text"
                                        placeholder="0.00 USD"
                                        class="h-12 w-full rounded-lg border border-slate-200 bg-slate-50 py-3 pr-4 pl-10 text-sm transition outline-none focus:border-[#007882] focus:ring-2 focus:ring-teal-500/30"
                                        @input="onInputUsd"
                                        @blur="onBlurUsd"
                                    />
                                </div>

                                <InputError
                                    :message="openForm.errors.opening_cash_usd"
                                />
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <Label
                                class="text-sm font-bold tracking-wider text-[#2a4858] uppercase"
                            >
                                Username
                            </Label>

                            <div class="relative">
                                <UserCircle
                                    class="absolute top-3.5 left-3 size-5 text-slate-400"
                                />

                                <Input
                                    :model-value="currentUserName"
                                    readonly
                                    class="h-12 rounded-lg border-slate-200 bg-slate-100 pl-10 text-slate-500 italic"
                                />
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <Label
                                class="text-sm font-bold tracking-wider text-[#2a4858] uppercase"
                            >
                                Opening Note
                            </Label>

                            <div class="relative">
                                <FileText
                                    class="absolute top-3.5 left-3 size-5 text-[#007882]/70"
                                />

                                <textarea
                                    v-model="openForm.opening_note"
                                    class="min-h-24 w-full rounded-lg border border-slate-200 bg-slate-50 py-3 pr-4 pl-10 text-sm transition outline-none focus:border-[#007882] focus:ring-2 focus:ring-teal-500/30"
                                    placeholder="Optional note"
                                />
                            </div>

                            <InputError
                                :message="openForm.errors.opening_note"
                            />
                        </div>

                        <div class="grid grid-cols-2 gap-3 pt-2">
                            <button
                                type="button"
                                class="inline-flex h-12 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 disabled:pointer-events-none disabled:opacity-50"
                                :disabled="openForm.processing"
                                @click="cancelForm"
                            >
                                Cancel
                            </button>

                            <button
                                type="button"
                                class="inline-flex h-12 items-center justify-center gap-2 rounded-lg bg-[#007882] px-4 text-sm font-bold text-white shadow-lg transition hover:bg-[#2a4858] disabled:pointer-events-none disabled:opacity-50"
                                :disabled="
                                    openForm.processing ||
                                    availableTerminals.length === 0
                                "
                                @click="submitOpen"
                            >
                                <PlayCircle class="size-5" />
                                Open POS
                            </button>
                        </div>
                    </form>
                </PosSessionPanel>

                <PosSessionPanel v-else>
                    <template #header-actions>
                        <button
                            type="button"
                            class="rounded-md p-2 text-white/70 transition hover:bg-white/10 hover:text-white"
                            title="Logout"
                            @click="logout"
                        >
                            <LogOut class="size-5" />
                        </button>
                    </template>

                    <template #icon>
                        <LockKeyhole class="size-8" />
                    </template>

                    <template #title>Close POS Shift</template>

                    <template #description>
                        Count and close the active cashier session
                    </template>

                    <form class="space-y-5" @submit.prevent>
                        <div
                            class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm"
                        >
                            <div class="font-bold text-[#2a4858]">
                                {{ selectedSession.session_no }}
                            </div>

                            <div class="mt-1 text-slate-500">
                                Terminal:
                                {{ getSessionTerminalName(selectedSession) }}
                            </div>

                            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                <div
                                    class="rounded-lg bg-white p-3 text-slate-600"
                                >
                                    <p class="text-xs uppercase">Opening USD</p>
                                    <strong class="text-[#2a4858]">
                                        ${{
                                            formatMoney(
                                                selectedSession.opening_cash_usd,
                                            )
                                        }}
                                    </strong>
                                </div>

                                <div
                                    class="rounded-lg bg-white p-3 text-slate-600"
                                >
                                    <p class="text-xs uppercase">Opening KHR</p>
                                    <strong class="text-[#2a4858]">
                                        ៛{{
                                            formatMoney(
                                                selectedSession.opening_cash_khr,
                                            )
                                        }}
                                    </strong>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="space-y-1.5">
                                <Label
                                    class="text-sm font-bold tracking-wider text-[#2a4858] uppercase"
                                >
                                    Actual KHR
                                </Label>

                                <div class="relative">
                                    <Banknote
                                        class="absolute top-3.5 left-3 size-5 text-[#007882]/70"
                                    />

                                    <Input
                                        v-model="closeForm.actual_cash_khr"
                                        type="number"
                                        min="0"
                                        step="100"
                                        placeholder="0"
                                        class="h-12 rounded-lg border-slate-200 bg-slate-50 pl-10"
                                    />
                                </div>

                                <InputError
                                    :message="closeForm.errors.actual_cash_khr"
                                />
                            </div>

                            <div class="space-y-1.5">
                                <Label
                                    class="text-sm font-bold tracking-wider text-[#2a4858] uppercase"
                                >
                                    Actual USD
                                </Label>

                                <div class="relative">
                                    <CircleDollarSign
                                        class="absolute top-3.5 left-3 size-5 text-[#007882]/70"
                                    />

                                    <Input
                                        v-model="closeForm.actual_cash_usd"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        placeholder="0.00"
                                        class="h-12 rounded-lg border-slate-200 bg-slate-50 pl-10"
                                    />
                                </div>

                                <InputError
                                    :message="closeForm.errors.actual_cash_usd"
                                />
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <Label
                                class="text-sm font-bold tracking-wider text-[#2a4858] uppercase"
                            >
                                Closing Note
                            </Label>

                            <div class="relative">
                                <FileText
                                    class="absolute top-3.5 left-3 size-5 text-[#007882]/70"
                                />

                                <textarea
                                    v-model="closeForm.closing_note"
                                    class="min-h-24 w-full rounded-lg border border-slate-200 bg-slate-50 py-3 pr-4 pl-10 text-sm transition outline-none focus:border-[#007882] focus:ring-2 focus:ring-teal-500/30"
                                    placeholder="Optional closing note"
                                />
                            </div>

                            <InputError
                                :message="closeForm.errors.closing_note"
                            />
                        </div>

                        <InputError :message="closeSessionError" />

                        <div class="grid grid-cols-2 gap-3">
                            <button
                                type="button"
                                class="inline-flex h-12 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 disabled:pointer-events-none disabled:opacity-50"
                                :disabled="closeForm.processing"
                                @click="cancelClose"
                            >
                                Cancel
                            </button>

                            <button
                                type="button"
                                class="inline-flex h-12 items-center justify-center rounded-lg bg-[#2a4858] px-4 text-sm font-bold text-white shadow-lg transition hover:bg-[#007882] disabled:pointer-events-none disabled:opacity-50"
                                :disabled="closeForm.processing"
                                @click="submitClose(selectedSession)"
                            >
                                Close POS
                            </button>
                        </div>
                    </form>
                </PosSessionPanel>
            </div>
        </div>
    </AppLayout>
</template>
