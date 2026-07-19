<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import ITModel from './ITModel.vue';

defineProps<{
    transfer: Record<string, any>;
    locations: Array<Record<string, any>>;
    canManageDestinationPutaway: boolean;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Stock Operations' },
    { title: 'Stock Movements' },
    { title: 'Internal Transfer', href: '/stock-movements/internal-transfer' },
    { title: 'Transfer Detail' },
];
</script>

<template>
    <Head :title="`Internal Transfer ${transfer.code}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #actions>
            <Button
                type="button"
                variant="ghost"
                class="h-9 font-semibold text-slate-600"
                @click="router.visit('/stock-movements/internal-transfer')"
            >
                <ArrowLeft class="size-4" />
                Back
            </Button>
        </template>

        <main
            class="min-h-[calc(100dvh-4rem)] bg-[#f8fafc] p-4 md:p-6 xl:p-8 2xl:p-10"
        >
            <ITModel
                :transfer="transfer as any"
                :locations="locations as any"
                :can-manage-destination-putaway="canManageDestinationPutaway"
                page
                @close="router.visit('/stock-movements/internal-transfer')"
            />
        </main>
    </AppLayout>
</template>
