<script setup lang="ts">
import type { Component } from 'vue';

type Tab = {
    value: string;
    label: string;
    count?: number;
    icon?: Component;
};

defineProps<{
    tabs: Tab[];
    modelValue: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();
</script>

<template>
    <div class="flex gap-1 rounded-xl border border-gray-100 bg-gray-50 p-1">
        <button
            v-for="tab in tabs"
            :key="tab.value"
            type="button"
            class="flex flex-1 items-center justify-center gap-1.5 rounded-lg px-3 py-1.5 text-[10px] font-bold uppercase transition"
            :class="
                modelValue === tab.value
                    ? 'bg-white font-black text-[#2A4858] shadow-sm'
                    : 'text-gray-400 hover:text-[#2A4858]'
            "
            @click="emit('update:modelValue', tab.value)"
        >
            <component
                :is="tab.icon"
                v-if="tab.icon"
                class="h-3.5 w-3.5 opacity-60"
            />
            {{ tab.label }}
            <span
                v-if="tab.count !== undefined"
                class="rounded-full bg-gray-100 px-1.5 py-0.5 text-[9px]"
            >
                {{ tab.count }}
            </span>
        </button>
    </div>
</template>
