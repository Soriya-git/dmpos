<script setup lang="ts">
import { Crown, RotateCw } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';

type CardStatus = 'active' | 'inactive' | 'blocked' | 'expired' | 'cancelled';

const props = withDefaults(
    defineProps<{
        cardNo?: string | null;
        cardName?: string | null;
        customerName?: string | null;
        companyBranch?: string | null;
        issuedDate?: string | null;
        expiredDate?: string | null;
        remark?: string | null;
        status?: CardStatus | string | null;
        showFlipButton?: boolean;
    }>(),
    {
        cardNo: 'MC-8820-9921',
        cardName: 'VIP Platinum Member',
        customerName: 'Guest Member',
        companyBranch: 'DM Group / Central Branch',
        issuedDate: '2026-05-23',
        expiredDate: '2028-05-23',
        remark: 'Non-transferable. Present at checkout to redeem membership privileges. Subject to terms and conditions.',
        status: 'active',
        showFlipButton: true,
    },
);

const flipped = ref(false);

const displayCardNo = computed(() => props.cardNo?.trim() || 'MC-XXXX-XXXX');
const barcodeLabel = computed(() =>
    displayCardNo.value.replace(/[^a-zA-Z0-9]/g, '').slice(0, 16),
);

const displayStatus = computed(() => props.status?.toString() || 'active');

const statusClass = computed(() => {
    const classes: Record<string, string> = {
        active: 'border-emerald-500/20 bg-emerald-500/10 text-emerald-300',
        inactive: 'border-slate-500/25 bg-slate-500/10 text-slate-300',
        blocked: 'border-red-500/25 bg-red-500/10 text-red-300',
        expired: 'border-amber-500/25 bg-amber-500/10 text-amber-300',
        cancelled: 'border-slate-600/30 bg-slate-600/15 text-slate-400',
    };

    return classes[displayStatus.value] ?? classes.inactive;
});

function flipCard() {
    flipped.value = !flipped.value;
}
</script>

<template>
    <div class="flex w-full flex-col items-center gap-4">
        <button
            type="button"
            class="membership-card-container relative h-[250px] w-full max-w-[420px] text-left"
            :class="{ flipped }"
            @click="flipCard"
        >
            <span class="sr-only">Flip membership card</span>

            <div class="membership-card-inner">
                <div
                    class="membership-card-face border border-white/10 bg-gradient-to-br from-[#2A4858] via-[#1F3643] to-[#12222B] text-white"
                >
                    <div class="membership-card-shimmer"></div>

                    <div class="relative z-10 flex items-start justify-between">
                        <div class="min-w-0">
                            <h4
                                class="truncate text-xs font-black tracking-widest text-[#FAFA6E] uppercase"
                            >
                                {{ cardName || 'Member Privilege' }}
                            </h4>
                            <p
                                class="mt-1 truncate text-[9px] font-bold tracking-wider text-white/50"
                            >
                                {{ companyBranch }}
                            </p>
                        </div>
                        <Crown
                            class="size-5 shrink-0 text-[#FAFA6E] drop-shadow"
                        />
                    </div>

                    <div class="relative z-10 my-auto">
                        <p
                            class="font-mono text-2xl font-black tracking-widest text-white drop-shadow-sm"
                        >
                            {{ displayCardNo }}
                        </p>
                    </div>

                    <div class="relative z-10 flex items-end justify-between">
                        <div class="min-w-0 pr-3">
                            <span
                                class="block text-[8px] font-bold text-white/40 uppercase"
                            >
                                Card Holder
                            </span>
                            <h3 class="truncate text-sm font-black">
                                {{ customerName || 'Guest Member' }}
                            </h3>
                        </div>

                        <div
                            class="flex shrink-0 flex-col items-center rounded-lg bg-white/95 p-1.5 shadow-inner"
                        >
                            <svg
                                class="h-6 w-20"
                                viewBox="0 0 100 30"
                                xmlns="http://www.w3.org/2000/svg"
                                aria-hidden="true"
                            >
                                <rect x="2" width="4" height="30" fill="black" />
                                <rect x="8" width="2" height="30" fill="black" />
                                <rect x="12" width="6" height="30" fill="black" />
                                <rect x="20" width="2" height="30" fill="black" />
                                <rect x="24" width="4" height="30" fill="black" />
                                <rect x="30" width="8" height="30" fill="black" />
                                <rect x="40" width="2" height="30" fill="black" />
                                <rect x="44" width="6" height="30" fill="black" />
                                <rect x="52" width="2" height="30" fill="black" />
                                <rect x="56" width="8" height="30" fill="black" />
                                <rect x="66" width="4" height="30" fill="black" />
                                <rect x="72" width="2" height="30" fill="black" />
                                <rect x="76" width="6" height="30" fill="black" />
                                <rect x="84" width="4" height="30" fill="black" />
                                <rect x="90" width="8" height="30" fill="black" />
                            </svg>
                            <span
                                class="mt-0.5 font-mono text-[7px] tracking-widest text-slate-700"
                            >
                                {{ barcodeLabel }}
                            </span>
                        </div>
                    </div>
                </div>

                <div
                    class="membership-card-face membership-card-back border border-white/5 bg-gradient-to-br from-[#1F2E37] to-[#0E171C] text-white"
                >
                    <div
                        class="absolute top-6 right-0 left-0 h-10 bg-slate-950/90 shadow-inner"
                    ></div>

                    <div
                        class="relative z-10 mt-10 flex flex-1 flex-col justify-between pt-4"
                    >
                        <div
                            class="grid grid-cols-2 gap-4 border-b border-white/5 pb-3"
                        >
                            <div>
                                <span
                                    class="block text-[8px] font-bold text-white/40 uppercase"
                                >
                                    Issued Date
                                </span>
                                <span class="font-mono text-xs font-bold">
                                    {{ issuedDate || 'YYYY-MM-DD' }}
                                </span>
                            </div>
                            <div>
                                <span
                                    class="block text-[8px] font-bold text-white/40 uppercase"
                                >
                                    Expiry Date
                                </span>
                                <span
                                    class="font-mono text-xs font-bold text-amber-300"
                                >
                                    {{ expiredDate || 'YYYY-MM-DD' }}
                                </span>
                            </div>
                        </div>

                        <div class="flex-1 py-2">
                            <span
                                class="mb-0.5 block text-[8px] font-bold text-white/40 uppercase"
                            >
                                Remarks / Rules
                            </span>
                            <p class="line-clamp-3 text-[9px] leading-normal text-white/60">
                                {{ remark || 'Insert custom card terms and privileges.' }}
                            </p>
                        </div>

                        <div
                            class="flex items-center justify-between border-t border-white/5 pt-2"
                        >
                            <span class="font-mono text-[8px] text-white/30 uppercase">
                                Membership Pass
                            </span>
                            <span
                                class="rounded border px-2.5 py-0.5 text-[9px] font-black uppercase"
                                :class="statusClass"
                            >
                                {{ displayStatus }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </button>

        <Button
            v-if="showFlipButton"
            type="button"
            variant="outline"
            class="h-8 rounded-lg text-xs font-bold text-slate-500 hover:text-[#007882]"
            @click="flipCard"
        >
            <RotateCw class="size-3.5" />
            Flip Card View
        </Button>
    </div>
</template>

<style scoped>
.membership-card-container {
    perspective: 1000px;
}

.membership-card-inner {
    position: relative;
    width: 100%;
    height: 100%;
    transition: transform 0.8s cubic-bezier(0.4, 0, 0.2, 1);
    transform-style: preserve-3d;
}

.membership-card-container.flipped .membership-card-inner {
    transform: rotateY(180deg);
}

.membership-card-face {
    position: absolute;
    display: flex;
    width: 100%;
    height: 100%;
    flex-direction: column;
    justify-content: space-between;
    overflow: hidden;
    border-radius: 20px;
    padding: 24px;
    backface-visibility: hidden;
    box-shadow: 0 20px 40px rgba(42, 72, 88, 0.15);
}

.membership-card-back {
    transform: rotateY(180deg);
}

.membership-card-shimmer {
    position: absolute;
    inset: -50%;
    background: linear-gradient(
        135deg,
        rgba(255, 255, 255, 0) 30%,
        rgba(255, 255, 255, 0.16) 50%,
        rgba(255, 255, 255, 0) 70%
    );
    transform: rotate(30deg);
    transition: transform 0.6s ease;
}

.membership-card-container:hover .membership-card-shimmer {
    transform: rotate(30deg) translate(20%, 20%);
}
</style>
