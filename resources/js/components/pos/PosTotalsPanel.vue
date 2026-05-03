<script setup lang="ts">
defineProps<{
    subtotal: string;
    tax?: string;
    total?: string;
    discount?: string;
    finalAmount?: string;
    actionLabel?: string;
    showAction?: boolean;
    variant?: 'cart' | 'bill';
    processing?: boolean;
}>();

const emit = defineEmits<{
    action: [];
}>();
</script>

<template>
    <div class="border-t border-gray-100 bg-gray-50/50 p-4">
        <div
            class="mb-4 space-y-1.5 rounded-2xl border border-gray-100 bg-white p-3 shadow-sm"
        >
            <div class="flex justify-between text-[10px] font-bold">
                <span class="text-gray-400">Sub Total</span>
                <span class="text-[#2A4858]">{{ subtotal }}</span>
            </div>
            <div
                v-if="variant === 'bill'"
                class="flex justify-between text-[10px] font-bold"
            >
                <span class="text-gray-400">Discount</span>
                <span class="text-[#2A4858]">{{ discount }}</span>
            </div>
            <div
                v-if="variant === 'bill'"
                class="flex justify-between text-[10px] font-bold"
            >
                <span class="text-gray-400">Total</span>
                <span class="text-[#2A4858]">{{ total }}</span>
            </div>
            <div
                v-if="variant === 'bill'"
                class="flex justify-between text-[10px] font-bold"
            >
                <span class="text-gray-400">Tax Amount</span>
                <span class="text-[#2A4858]">{{ tax }}</span>
            </div>
            <div
                v-if="variant === 'bill'"
                class="flex items-center justify-between border-t border-dashed pt-1.5"
            >
                <span class="text-xs font-black text-[#2A4858]"
                    >Final Amount</span
                >
                <span class="text-base font-black text-[#007882]">{{
                    finalAmount
                }}</span>
            </div>
        </div>

        <button
            v-if="showAction ?? true"
            type="button"
            class="w-full rounded-xl bg-[#23AA8F] py-3 text-xs font-black text-white shadow-lg shadow-[#23AA8F]/20 transition hover:bg-[#007882] disabled:opacity-60"
            :disabled="processing"
            @click="emit('action')"
        >
            {{ actionLabel ?? 'PLACE ORDER' }}
        </button>
    </div>
</template>
