<script setup lang="ts">
import { Grid2X2, Soup } from 'lucide-vue-next';

type Category = {
    id: number;
    name: string;
};

defineProps<{
    categories: Category[];
    modelValue: string | number | null;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string | number];
    change: [];
}>();

function selectCategory(value: string | number) {
    emit('update:modelValue', value);
    emit('change');
}
</script>

<template>
    <div
        class="flex gap-2 overflow-x-auto pb-1 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
    >
        <button
            type="button"
            class="flex min-w-[70px] flex-col items-center justify-center rounded-xl border p-2 transition"
            :class="
                modelValue === '' || modelValue === null
                    ? 'border-[#23AA8F] bg-emerald-50 text-[#2A4858]'
                    : 'border-gray-100 bg-white text-gray-400 hover:border-[#23AA8F]/50'
            "
            @click="selectCategory('')"
        >
            <span
                class="mb-1 flex h-8 w-8 items-center justify-center rounded-lg bg-gray-100"
            >
                <Grid2X2 class="h-3.5 w-3.5" />
            </span>
            <span class="text-[10px] font-bold uppercase">All</span>
        </button>

        <button
            v-for="category in categories"
            :key="category.id"
            type="button"
            class="flex min-w-[70px] flex-col items-center justify-center rounded-xl border p-2 transition"
            :class="
                String(modelValue) === String(category.id)
                    ? 'border-[#23AA8F] bg-emerald-50 text-[#2A4858]'
                    : 'border-gray-100 bg-white text-gray-400 hover:border-[#23AA8F]/50'
            "
            @click="selectCategory(category.id)"
        >
            <span
                class="mb-1 flex h-8 w-8 items-center justify-center rounded-lg bg-gray-50 text-[#23AA8F]"
            >
                <Soup class="h-3.5 w-3.5" />
            </span>
            <span class="max-w-[58px] truncate text-[10px] font-bold uppercase">
                {{ category.name }}
            </span>
        </button>
    </div>
</template>
