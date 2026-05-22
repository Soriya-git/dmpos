<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import {
    Banknote,
    FileText,
    LogOut,
    Monitor,
    PlayCircle,
    ShieldAlert,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import UserAvatar from '@/components/UserAvatar.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { login } from '@/wayfinder/routes';

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
    total_sales_usd?: number | string;
    total_sales_khr?: number | string;
    total_cash_usd?: number | string;
    total_cash_khr?: number | string;
    total_ebanking_usd?: number | string;
    total_ebanking_khr?: number | string;
    total_pay_later_usd?: number | string;
    total_pay_later_khr?: number | string;
    pos_terminal?: Terminal | null;
    posTerminal?: Terminal | null;
};

type BanknoteOption = {
    id: number;
    currency_type: string;
    denomination: number | string;
};

const props = defineProps<{
    sessions: Session[];
    terminals: Terminal[];
    currentSession?: Session | null;
    banknotes: BanknoteOption[];
}>();

const page = usePage();

const currentUser = computed(() => {
    return (page.props.auth as any)?.user ?? null;
});

const currentUserName = computed(() => {
    return currentUser.value?.name ?? 'Current User';
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

const initialBanknoteCounts = () => {
    return Object.fromEntries(
        props.banknotes.map((banknote) => [String(banknote.id), '']),
    ) as Record<string, string>;
};

const openForm = useForm({
    pos_terminal_id: '',
    opening_banknotes: initialBanknoteCounts(),
    opening_note: '',
});

const closeForm = useForm({
    actual_banknotes: initialBanknoteCounts(),
    closing_note: '',
});

const closeConfirmOpen = ref(false);

const banknotesByCurrency = computed(() => {
    return props.banknotes.reduce<Record<string, BanknoteOption[]>>(
        (groups, banknote) => {
            const currency = banknote.currency_type.toUpperCase();
            groups[currency] = groups[currency] ?? [];
            groups[currency].push(banknote);
            return groups;
        },
        {},
    );
});

const khrBanknotes = computed(() => banknotesByCurrency.value.KHR ?? []);
const usdBanknotes = computed(() => banknotesByCurrency.value.USD ?? []);

const banknoteQuantity = (
    counts: Record<string, string>,
    banknoteId: number,
) => {
    const quantity = Number(counts[String(banknoteId)] || 0);

    return Number.isFinite(quantity) && quantity > 0 ? Math.floor(quantity) : 0;
};

const banknoteTotal = (
    counts: Record<string, string>,
    banknote: BanknoteOption,
) => {
    return (
        Number(banknote.denomination) * banknoteQuantity(counts, banknote.id)
    );
};

const currencyBanknoteTotal = (
    counts: Record<string, string>,
    currency: string,
) => {
    return (banknotesByCurrency.value[currency] ?? []).reduce(
        (total, banknote) => total + banknoteTotal(counts, banknote),
        0,
    );
};

const openingCashKhr = computed(() =>
    currencyBanknoteTotal(openForm.opening_banknotes, 'KHR'),
);
const openingCashUsd = computed(() =>
    currencyBanknoteTotal(openForm.opening_banknotes, 'USD'),
);
const closingCashKhr = computed(() =>
    currencyBanknoteTotal(closeForm.actual_banknotes, 'KHR'),
);
const closingCashUsd = computed(() =>
    currencyBanknoteTotal(closeForm.actual_banknotes, 'USD'),
);

const resetOpeningBanknotes = () => {
    openForm.opening_banknotes = initialBanknoteCounts();
};

const resetClosingBanknotes = () => {
    closeForm.actual_banknotes = initialBanknoteCounts();
};

const submitOpen = () => {
    openForm.clearErrors();

    if (!openForm.pos_terminal_id) {
        openForm.setError('pos_terminal_id', 'Please select POS terminal.');
        return;
    }

    openForm.post('/pos-sessions/open', {
        preserveScroll: true,
        onSuccess: () => {
            openForm.reset();
            resetOpeningBanknotes();
        },
    });
};

const submitClose = (session: Session) => {
    closeForm.post(`/pos-sessions/${session.id}/close`, {
        preserveScroll: true,
        onSuccess: () => {
            closeConfirmOpen.value = false;
            closeForm.reset();
            resetClosingBanknotes();
        },
    });
};

const requestClose = () => {
    closeConfirmOpen.value = true;
};

const returnToCloseForm = () => {
    closeConfirmOpen.value = false;
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
    resetOpeningBanknotes();
    closePopup();
};

const cancelClose = () => {
    closeForm.reset();
    closeForm.clearErrors();
    resetClosingBanknotes();
    closePopup();
};

const logout = () => {
    router.post(
        '/logout',
        {},
        {
            onSuccess: () => router.visit(login().url, { replace: true }),
        },
    );
};

const formatMoney = (value: number | string | null | undefined) => {
    return Number(value ?? 0).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

const numeric = (value: number | string | null | undefined) => {
    return Number(value ?? 0);
};

const expectedCashUsd = computed(() => {
    if (!selectedSession.value) return 0;

    return (
        numeric(selectedSession.value.opening_cash_usd) +
        numeric(selectedSession.value.total_cash_usd)
    );
});

const expectedCashKhr = computed(() => {
    if (!selectedSession.value) return 0;

    return (
        numeric(selectedSession.value.opening_cash_khr) +
        numeric(selectedSession.value.total_cash_khr)
    );
});

const actualCashUsd = computed(() => {
    return closingCashUsd.value;
});

const actualCashKhr = computed(() => {
    return closingCashKhr.value;
});

const varianceUsd = computed(() => actualCashUsd.value - expectedCashUsd.value);
const varianceKhr = computed(() => actualCashKhr.value - expectedCashKhr.value);

const getSessionTerminalName = (session: Session) => {
    return session.posTerminal?.name ?? session.pos_terminal?.name ?? '-';
};

const closeSessionError = computed(() => {
    return (closeForm.errors as Record<string, string>).session;
});

const openingBanknotesError = computed(() => {
    return (openForm.errors as Record<string, string>).opening_banknotes;
});

const closingBanknotesError = computed(() => {
    return (closeForm.errors as Record<string, string>).actual_banknotes;
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

            <div class="mx-auto w-full max-w-5xl">
                <div
                    v-if="!selectedSession"
                    class="overflow-hidden rounded-2xl border border-[#2a4858]/10 bg-white shadow-xl"
                >
                    <div
                        class="flex items-center justify-between gap-4 bg-[#2a4858] px-5 py-4 text-white"
                    >
                        <div class="flex min-w-0 items-center gap-3">
                            <div
                                class="flex size-11 shrink-0 items-center justify-center rounded-xl border border-white/20 bg-white/10"
                            >
                                <UserAvatar
                                    :user="currentUser"
                                    class="size-10 overflow-hidden rounded-xl"
                                    fallback-class="rounded-xl bg-transparent text-sm font-black text-[#fafa6e]"
                                />
                            </div>
                            <div class="min-w-0">
                                <h1 class="truncate text-xl font-bold">
                                    Open POS Shift
                                </h1>
                                <p class="truncate text-xs text-slate-300">
                                    {{ currentUserName }}
                                </p>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="rounded-md p-2 text-white/70 transition hover:bg-white/10 hover:text-white"
                            title="Logout"
                            @click="logout"
                        >
                            <LogOut class="size-5" />
                        </button>
                    </div>

                    <form
                        class="grid gap-4 p-5 lg:grid-cols-[1fr_1.35fr]"
                        @submit.prevent="submitOpen"
                    >
                        <div class="space-y-4">
                            <section
                                class="rounded-xl border border-slate-200 bg-white p-3"
                            >
                                <Label
                                    class="mb-2 block text-xs font-black tracking-wide text-[#2a4858] uppercase"
                                >
                                    POS Number (Terminal)
                                </Label>

                                <div class="relative">
                                    <Monitor
                                        class="absolute top-2.5 left-3 size-4 text-[#007882]/70"
                                    />

                                    <select
                                        v-model="openForm.pos_terminal_id"
                                        class="h-9 w-full appearance-none rounded-lg border border-slate-200 bg-slate-50 py-2 pr-4 pl-9 text-sm transition outline-none focus:border-[#007882] focus:ring-2 focus:ring-teal-500/30 disabled:cursor-not-allowed disabled:opacity-60"
                                        :disabled="
                                            availableTerminals.length === 0
                                        "
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
                                    class="mt-2 text-xs font-medium text-red-600"
                                >
                                    No available POS terminal. All active
                                    terminals are already opened.
                                </p>
                            </section>

                            <section
                                class="rounded-xl border border-emerald-100 bg-emerald-50/60 p-4"
                            >
                                <h2
                                    class="mb-3 text-sm font-black tracking-wide text-[#2a4858] uppercase"
                                >
                                    Opening Cash Total
                                </h2>

                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div class="rounded-lg bg-white p-3">
                                        <p
                                            class="text-xs font-bold text-slate-400 uppercase"
                                        >
                                            KHR Drawer
                                        </p>
                                        <p
                                            class="mt-1 text-xl font-black text-[#007882]"
                                        >
                                            ៛{{ formatMoney(openingCashKhr) }}
                                        </p>
                                    </div>

                                    <div class="rounded-lg bg-white p-3">
                                        <p
                                            class="text-xs font-bold text-slate-400 uppercase"
                                        >
                                            USD Drawer
                                        </p>
                                        <p
                                            class="mt-1 text-xl font-black text-[#007882]"
                                        >
                                            ${{ formatMoney(openingCashUsd) }}
                                        </p>
                                    </div>
                                </div>
                            </section>

                            <section class="space-y-1.5">
                                <Label
                                    class="text-xs font-bold tracking-wider text-[#2a4858] uppercase"
                                >
                                    Opening Note
                                </Label>

                                <div class="relative">
                                    <FileText
                                        class="absolute top-3 left-3 size-5 text-[#007882]/70"
                                    />

                                    <textarea
                                        v-model="openForm.opening_note"
                                        class="min-h-16 w-full rounded-lg border border-slate-200 bg-slate-50 py-3 pr-4 pl-10 text-sm transition outline-none focus:border-[#007882] focus:ring-2 focus:ring-teal-500/30"
                                        placeholder="Optional opening note"
                                    />
                                </div>

                                <InputError
                                    :message="openForm.errors.opening_note"
                                />
                            </section>
                        </div>

                        <div class="space-y-4">
                            <section
                                class="rounded-xl border border-slate-200 bg-slate-50 p-4"
                            >
                                <div
                                    class="mb-3 flex items-center justify-between"
                                >
                                    <h2
                                        class="text-sm font-black tracking-wide text-[#2a4858] uppercase"
                                    >
                                        Banknote Count
                                    </h2>
                                    <span class="text-xs text-slate-500">
                                        Enter number of notes
                                    </span>
                                </div>

                                <div class="grid gap-3 md:grid-cols-2">
                                    <div class="rounded-lg bg-white p-3">
                                        <div
                                            class="mb-2 flex items-center justify-between"
                                        >
                                            <p
                                                class="text-xs font-black text-[#2a4858] uppercase"
                                            >
                                                KHR
                                            </p>
                                            <p
                                                class="text-xs font-black text-[#007882]"
                                            >
                                                ៛{{
                                                    formatMoney(openingCashKhr)
                                                }}
                                            </p>
                                        </div>

                                        <div class="space-y-1.5">
                                            <div
                                                v-for="banknote in khrBanknotes"
                                                :key="banknote.id"
                                                class="grid grid-cols-[1fr_5rem_1fr] items-center gap-2 text-xs"
                                            >
                                                <span
                                                    class="font-semibold text-slate-600"
                                                >
                                                    ៛{{
                                                        formatMoney(
                                                            banknote.denomination,
                                                        )
                                                    }}
                                                </span>
                                                <Input
                                                    v-model="
                                                        openForm
                                                            .opening_banknotes[
                                                            String(banknote.id)
                                                        ]
                                                    "
                                                    type="number"
                                                    min="0"
                                                    step="1"
                                                    inputmode="numeric"
                                                    class="h-8 rounded-lg border-slate-200 bg-slate-50 text-center text-sm"
                                                />
                                                <span
                                                    class="text-right font-bold text-slate-500"
                                                >
                                                    ៛{{
                                                        formatMoney(
                                                            banknoteTotal(
                                                                openForm.opening_banknotes,
                                                                banknote,
                                                            ),
                                                        )
                                                    }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="rounded-lg bg-white p-3">
                                        <div
                                            class="mb-2 flex items-center justify-between"
                                        >
                                            <p
                                                class="text-xs font-black text-[#2a4858] uppercase"
                                            >
                                                USD
                                            </p>
                                            <p
                                                class="text-xs font-black text-[#007882]"
                                            >
                                                ${{
                                                    formatMoney(openingCashUsd)
                                                }}
                                            </p>
                                        </div>

                                        <div class="space-y-1.5">
                                            <div
                                                v-for="banknote in usdBanknotes"
                                                :key="banknote.id"
                                                class="grid grid-cols-[1fr_5rem_1fr] items-center gap-2 text-xs"
                                            >
                                                <span
                                                    class="font-semibold text-slate-600"
                                                >
                                                    ${{
                                                        formatMoney(
                                                            banknote.denomination,
                                                        )
                                                    }}
                                                </span>
                                                <Input
                                                    v-model="
                                                        openForm
                                                            .opening_banknotes[
                                                            String(banknote.id)
                                                        ]
                                                    "
                                                    type="number"
                                                    min="0"
                                                    step="1"
                                                    inputmode="numeric"
                                                    class="h-8 rounded-lg border-slate-200 bg-slate-50 text-center text-sm"
                                                />
                                                <span
                                                    class="text-right font-bold text-slate-500"
                                                >
                                                    ${{
                                                        formatMoney(
                                                            banknoteTotal(
                                                                openForm.opening_banknotes,
                                                                banknote,
                                                            ),
                                                        )
                                                    }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <InputError :message="openingBanknotesError" />
                            </section>

                            <div class="grid grid-cols-2 gap-3">
                                <button
                                    type="button"
                                    class="inline-flex h-11 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 disabled:pointer-events-none disabled:opacity-50"
                                    :disabled="openForm.processing"
                                    @click="cancelForm"
                                >
                                    Cancel
                                </button>

                                <button
                                    type="button"
                                    class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-[#2a4858] px-4 text-sm font-bold text-white shadow-lg transition hover:bg-[#007882] disabled:pointer-events-none disabled:opacity-50"
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
                        </div>
                    </form>
                </div>

                <div
                    v-else
                    class="overflow-hidden rounded-2xl border border-[#2a4858]/10 bg-white shadow-xl"
                >
                    <div
                        class="flex items-center justify-between gap-4 bg-[#2a4858] px-5 py-4 text-white"
                    >
                        <div class="flex min-w-0 items-center gap-3">
                            <div
                                class="flex size-11 shrink-0 items-center justify-center rounded-xl border border-white/20 bg-white/10"
                            >
                                <UserAvatar
                                    :user="currentUser"
                                    class="size-10 overflow-hidden rounded-xl"
                                    fallback-class="rounded-xl bg-transparent text-sm font-black text-[#fafa6e]"
                                />
                            </div>
                            <div class="min-w-0">
                                <h1 class="truncate text-xl font-bold">
                                    Close POS Shift
                                </h1>
                                <p class="truncate text-xs text-slate-300">
                                    {{ selectedSession.session_no }} /
                                    {{
                                        getSessionTerminalName(selectedSession)
                                    }}
                                </p>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="rounded-md p-2 text-white/70 transition hover:bg-white/10 hover:text-white"
                            title="Logout"
                            @click="logout"
                        >
                            <LogOut class="size-5" />
                        </button>
                    </div>

                    <form
                        class="grid gap-4 p-5 lg:grid-cols-[1fr_1.35fr]"
                        @submit.prevent
                    >
                        <div class="space-y-3">
                            <section
                                class="rounded-xl border border-slate-200 bg-slate-50 p-3"
                            >
                                <div
                                    class="mb-2 flex items-center justify-between gap-3"
                                >
                                    <h2
                                        class="text-xs font-black tracking-wide text-[#2a4858] uppercase"
                                    >
                                        Amount by Payment Term
                                    </h2>
                                    <span class="text-xs text-slate-500">
                                        Sales
                                        {{
                                            formatMoney(
                                                selectedSession.total_sales_usd,
                                            )
                                        }}
                                        USD
                                    </span>
                                </div>

                                <div class="grid gap-2 sm:grid-cols-3">
                                    <div class="rounded-lg bg-white p-2.5">
                                        <p
                                            class="text-xs font-bold text-slate-400 uppercase"
                                        >
                                            Cash
                                        </p>
                                        <p
                                            class="mt-0.5 text-sm font-black text-[#2a4858]"
                                        >
                                            ${{
                                                formatMoney(
                                                    selectedSession.total_cash_usd,
                                                )
                                            }}
                                        </p>
                                        <p
                                            class="text-xs font-bold text-slate-500"
                                        >
                                            ៛{{
                                                formatMoney(
                                                    selectedSession.total_cash_khr,
                                                )
                                            }}
                                        </p>
                                    </div>

                                    <div class="rounded-lg bg-white p-2.5">
                                        <p
                                            class="text-xs font-bold text-slate-400 uppercase"
                                        >
                                            E-Banking
                                        </p>
                                        <p
                                            class="mt-0.5 text-sm font-black text-[#2a4858]"
                                        >
                                            ${{
                                                formatMoney(
                                                    selectedSession.total_ebanking_usd,
                                                )
                                            }}
                                        </p>
                                        <p
                                            class="text-xs font-bold text-slate-500"
                                        >
                                            ៛{{
                                                formatMoney(
                                                    selectedSession.total_ebanking_khr,
                                                )
                                            }}
                                        </p>
                                    </div>

                                    <div class="rounded-lg bg-white p-2.5">
                                        <p
                                            class="text-xs font-bold text-slate-400 uppercase"
                                        >
                                            Pay Later
                                        </p>
                                        <p
                                            class="mt-0.5 text-sm font-black text-[#2a4858]"
                                        >
                                            ${{
                                                formatMoney(
                                                    selectedSession.total_pay_later_usd,
                                                )
                                            }}
                                        </p>
                                        <p
                                            class="text-xs font-bold text-slate-500"
                                        >
                                            ៛{{
                                                formatMoney(
                                                    selectedSession.total_pay_later_khr,
                                                )
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </section>

                            <section
                                class="rounded-xl border border-emerald-100 bg-emerald-50/60 p-3"
                            >
                                <h2
                                    class="mb-2 text-xs font-black tracking-wide text-[#2a4858] uppercase"
                                >
                                    Expected Cash in Drawer
                                </h2>

                                <div class="grid gap-2 sm:grid-cols-2">
                                    <div class="rounded-lg bg-white p-2.5">
                                        <p
                                            class="text-xs font-bold text-slate-400 uppercase"
                                        >
                                            USD Drawer
                                        </p>
                                        <p
                                            class="mt-0.5 text-lg font-black text-[#007882]"
                                        >
                                            ${{ formatMoney(expectedCashUsd) }}
                                        </p>
                                        <p class="text-[11px] text-slate-500">
                                            Opening ${{
                                                formatMoney(
                                                    selectedSession.opening_cash_usd,
                                                )
                                            }}
                                            + cash movement ${{
                                                formatMoney(
                                                    selectedSession.total_cash_usd,
                                                )
                                            }}
                                        </p>
                                    </div>

                                    <div class="rounded-lg bg-white p-2.5">
                                        <p
                                            class="text-xs font-bold text-slate-400 uppercase"
                                        >
                                            KHR Drawer
                                        </p>
                                        <p
                                            class="mt-0.5 text-lg font-black text-[#007882]"
                                        >
                                            ៛{{ formatMoney(expectedCashKhr) }}
                                        </p>
                                        <p class="text-[11px] text-slate-500">
                                            Opening ៛{{
                                                formatMoney(
                                                    selectedSession.opening_cash_khr,
                                                )
                                            }}
                                            + cash movement ៛{{
                                                formatMoney(
                                                    selectedSession.total_cash_khr,
                                                )
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </section>

                            <section class="space-y-1.5">
                                <Label
                                    class="text-xs font-bold tracking-wider text-[#2a4858] uppercase"
                                >
                                    Closing Note
                                </Label>

                                <div class="relative">
                                    <FileText
                                        class="absolute top-3 left-3 size-5 text-[#007882]/70"
                                    />

                                    <textarea
                                        v-model="closeForm.closing_note"
                                        class="min-h-16 w-full rounded-lg border border-slate-200 bg-slate-50 py-3 pr-4 pl-10 text-sm transition outline-none focus:border-[#007882] focus:ring-2 focus:ring-teal-500/30"
                                        placeholder="Optional closing note"
                                    />
                                </div>

                                <InputError
                                    :message="closeForm.errors.closing_note"
                                />
                            </section>
                        </div>

                        <div class="space-y-4">
                            <section
                                class="rounded-xl border border-slate-200 bg-white p-3"
                            >
                                <div
                                    class="mb-2 flex items-center justify-between gap-3"
                                >
                                    <h2
                                        class="text-xs font-black tracking-wide text-[#2a4858] uppercase"
                                    >
                                        Actual Cash Count
                                    </h2>
                                    <span class="text-xs text-slate-500">
                                        Count notes in drawer
                                    </span>
                                </div>

                                <div class="grid gap-3 md:grid-cols-2">
                                    <div class="rounded-lg bg-slate-50 p-3">
                                        <div
                                            class="mb-2 flex items-center justify-between"
                                        >
                                            <p
                                                class="text-xs font-black text-[#2a4858] uppercase"
                                            >
                                                KHR
                                            </p>
                                            <p
                                                class="text-xs font-black text-[#007882]"
                                            >
                                                ៛{{
                                                    formatMoney(closingCashKhr)
                                                }}
                                            </p>
                                        </div>

                                        <div class="space-y-1.5">
                                            <div
                                                v-for="banknote in khrBanknotes"
                                                :key="banknote.id"
                                                class="grid grid-cols-[1fr_5rem_1fr] items-center gap-2 text-xs"
                                            >
                                                <span
                                                    class="font-semibold text-slate-600"
                                                >
                                                    ៛{{
                                                        formatMoney(
                                                            banknote.denomination,
                                                        )
                                                    }}
                                                </span>
                                                <Input
                                                    v-model="
                                                        closeForm
                                                            .actual_banknotes[
                                                            String(banknote.id)
                                                        ]
                                                    "
                                                    type="number"
                                                    min="0"
                                                    step="1"
                                                    inputmode="numeric"
                                                    class="h-8 rounded-lg border-slate-200 bg-white text-center text-sm"
                                                />
                                                <span
                                                    class="text-right font-bold text-slate-500"
                                                >
                                                    ៛{{
                                                        formatMoney(
                                                            banknoteTotal(
                                                                closeForm.actual_banknotes,
                                                                banknote,
                                                            ),
                                                        )
                                                    }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="rounded-lg bg-slate-50 p-3">
                                        <div
                                            class="mb-2 flex items-center justify-between"
                                        >
                                            <p
                                                class="text-xs font-black text-[#2a4858] uppercase"
                                            >
                                                USD
                                            </p>
                                            <p
                                                class="text-xs font-black text-[#007882]"
                                            >
                                                ${{
                                                    formatMoney(closingCashUsd)
                                                }}
                                            </p>
                                        </div>

                                        <div class="space-y-1.5">
                                            <div
                                                v-for="banknote in usdBanknotes"
                                                :key="banknote.id"
                                                class="grid grid-cols-[1fr_5rem_1fr] items-center gap-2 text-xs"
                                            >
                                                <span
                                                    class="font-semibold text-slate-600"
                                                >
                                                    ${{
                                                        formatMoney(
                                                            banknote.denomination,
                                                        )
                                                    }}
                                                </span>
                                                <Input
                                                    v-model="
                                                        closeForm
                                                            .actual_banknotes[
                                                            String(banknote.id)
                                                        ]
                                                    "
                                                    type="number"
                                                    min="0"
                                                    step="1"
                                                    inputmode="numeric"
                                                    class="h-8 rounded-lg border-slate-200 bg-white text-center text-sm"
                                                />
                                                <span
                                                    class="text-right font-bold text-slate-500"
                                                >
                                                    ${{
                                                        formatMoney(
                                                            banknoteTotal(
                                                                closeForm.actual_banknotes,
                                                                banknote,
                                                            ),
                                                        )
                                                    }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <InputError :message="closingBanknotesError" />

                                <div class="mt-2 grid gap-2 sm:grid-cols-2">
                                    <div
                                        class="flex items-center justify-between rounded-lg bg-slate-50 px-2.5 py-2 text-xs"
                                    >
                                        <span
                                            class="font-bold text-slate-400 uppercase"
                                        >
                                            Variance USD
                                        </span>
                                        <p
                                            class="text-xs font-black"
                                            :class="
                                                varianceUsd === 0
                                                    ? 'text-[#007882]'
                                                    : 'text-red-600'
                                            "
                                        >
                                            ${{ formatMoney(varianceUsd) }}
                                        </p>
                                    </div>
                                    <div
                                        class="flex items-center justify-between rounded-lg bg-slate-50 px-2.5 py-2 text-xs"
                                    >
                                        <span
                                            class="font-bold text-slate-400 uppercase"
                                        >
                                            Variance KHR
                                        </span>
                                        <p
                                            class="text-xs font-black"
                                            :class="
                                                varianceKhr === 0
                                                    ? 'text-[#007882]'
                                                    : 'text-red-600'
                                            "
                                        >
                                            ៛{{ formatMoney(varianceKhr) }}
                                        </p>
                                    </div>
                                </div>
                            </section>

                            <InputError :message="closeSessionError" />

                            <div class="grid grid-cols-2 gap-3">
                                <button
                                    type="button"
                                    class="inline-flex h-11 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 disabled:pointer-events-none disabled:opacity-50"
                                    :disabled="closeForm.processing"
                                    @click="cancelClose"
                                >
                                    Cancel
                                </button>

                                <button
                                    type="button"
                                    class="inline-flex h-11 items-center justify-center rounded-lg bg-[#2a4858] px-4 text-sm font-bold text-white shadow-lg transition hover:bg-[#007882] disabled:pointer-events-none disabled:opacity-50"
                                    :disabled="closeForm.processing"
                                    @click="requestClose"
                                >
                                    Close POS
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div
                v-if="closeConfirmOpen && selectedSession"
                class="fixed inset-0 z-[90] flex items-center justify-center bg-[#2a4858]/25 p-4 backdrop-blur-sm"
                @click.self="returnToCloseForm"
            >
                <section
                    class="w-full max-w-md overflow-hidden rounded-lg bg-white shadow-2xl"
                >
                    <header class="border-b border-slate-100 p-5">
                        <div class="flex items-start gap-3">
                            <div
                                class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-700"
                            >
                                <ShieldAlert class="size-5" />
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-[#2a4858]">
                                    Close POS?
                                </h2>
                                <p class="mt-1 text-sm text-slate-500">
                                    This will close the current POS session.
                                </p>
                            </div>
                        </div>
                    </header>

                    <footer class="grid grid-cols-2 gap-3 bg-slate-50 p-5">
                        <button
                            type="button"
                            class="inline-flex h-11 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 disabled:pointer-events-none disabled:opacity-50"
                            :disabled="closeForm.processing"
                            @click="returnToCloseForm"
                        >
                            No
                        </button>
                        <button
                            type="button"
                            class="inline-flex h-11 items-center justify-center rounded-lg bg-[#2a4858] px-4 text-sm font-bold text-white shadow-lg transition hover:bg-[#007882] disabled:pointer-events-none disabled:opacity-50"
                            :disabled="closeForm.processing"
                            @click="submitClose(selectedSession)"
                        >
                            Yes
                        </button>
                    </footer>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
