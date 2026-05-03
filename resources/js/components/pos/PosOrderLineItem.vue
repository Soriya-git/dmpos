<script setup lang="ts">
import { Minus, Pencil, Plus, Trash2, Utensils } from 'lucide-vue-next';

type CartLine = {
    id: number;
    menu_name: string;
    qty: number;
    unit_price: number;
    total_amount: number;
    note?: string | null;
};

defineProps<{
    line: CartLine;
    unitPrice: string;
    totalPrice: string;
    imageSrc?: string | null;
    editable?: boolean;
}>();

const emit = defineEmits<{
    decrease: [];
    increase: [];
    note: [];
    remove: [];
}>();
</script>

<template>
    <div
        class="flex gap-3 rounded-xl border border-gray-50 bg-white p-2 shadow-sm"
    >
        <div class="h-12 w-12 shrink-0 overflow-hidden rounded-lg bg-gray-100">
            <img
                v-if="imageSrc"
                :src="imageSrc"
                :alt="line.menu_name"
                class="h-full w-full object-cover"
            />
            <div
                v-else
                class="flex h-full w-full items-center justify-center text-gray-300"
            >
                <Utensils class="h-5 w-5" />
            </div>
        </div>

        <div class="min-w-0 flex-1">
            <div class="flex items-start justify-between gap-2">
                <h4 class="truncate text-[11px] font-bold text-[#2A4858]">
                    {{ line.menu_name }}
                </h4>
                <button
                    v-if="editable"
                    type="button"
                    class="rounded-md p-1 text-gray-300 hover:bg-red-50 hover:text-red-500"
                    @click="emit('remove')"
                >
                    <Trash2 class="h-3.5 w-3.5" />
                </button>
            </div>

            <div class="mt-1 flex items-center justify-between">
                <span class="text-[10px] font-black text-[#23AA8F]">
                    {{ unitPrice }}
                    <span class="ml-1 font-normal text-gray-300"
                        >{{ line.qty }}x</span
                    >
                </span>
                <span class="text-[10px] font-black text-[#2A4858]">{{
                    totalPrice
                }}</span>
            </div>

            <div v-if="editable" class="mt-2 flex items-center gap-1.5">
                <div
                    class="flex w-24 items-center justify-between rounded-lg border border-gray-100 bg-gray-50 p-0.5"
                >
                    <button
                        type="button"
                        class="flex h-5 w-5 items-center justify-center rounded-md text-[#23AA8F] hover:bg-white"
                        @click="emit('decrease')"
                    >
                        <Minus class="h-3 w-3" />
                    </button>
                    <span class="text-[11px] font-black text-[#2A4858]">{{
                        line.qty
                    }}</span>
                    <button
                        type="button"
                        class="flex h-5 w-5 items-center justify-center rounded-md text-[#23AA8F] hover:bg-white"
                        @click="emit('increase')"
                    >
                        <Plus class="h-3 w-3" />
                    </button>
                </div>

                <div
                    class="flex h-6 min-w-0 flex-1 items-center gap-1 rounded-lg border border-gray-100 px-2"
                    :class="line.note ? 'bg-[#23AA8F]/10' : 'bg-white'"
                >
                    <span
                        class="min-w-0 flex-1 truncate text-[10px] font-bold"
                        :class="line.note ? 'text-[#007882]' : 'text-gray-300'"
                    >
                        {{ line.note || '-' }}
                    </span>
                    <button
                        type="button"
                        class="flex h-4 w-4 shrink-0 items-center justify-center rounded text-gray-400 hover:bg-white hover:text-[#007882]"
                        title="Edit note"
                        @click="emit('note')"
                    >
                        <Pencil class="h-3 w-3" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
