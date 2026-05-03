<script setup lang="ts">
import { Card } from '@/components/ui/card';
</script>

<template>
    <Card
        class="overflow-hidden rounded-2xl border-[#2a4858]/10 bg-white py-0 shadow-xl"
    >
        <div class="relative bg-[#2a4858] px-6 py-7 text-center text-white">
            <div class="absolute top-4 right-4">
                <slot name="header-actions" />
            </div>

            <div
                class="mx-auto mb-4 inline-flex size-16 items-center justify-center rounded-full border border-white/20 bg-white/10 text-[#fafa6e]"
            >
                <slot name="icon" />
            </div>

            <h1 class="text-2xl font-bold tracking-tight">
                <slot name="title" />
            </h1>

            <p class="mt-1 text-sm text-slate-300">
                <slot name="description" />
            </p>
        </div>

        <div class="p-6 sm:p-8">
            <slot />
        </div>
    </Card>
</template>
