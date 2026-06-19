<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Master Data' },
    { title: 'Customers', href: '/master-data/customers' },
    { title: 'Create' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <main class="h-[calc(100dvh-4rem)] w-full bg-[#f8fafc] p-4 md:h-[calc(100dvh-5rem)] md:p-6 xl:p-8">
            <p class="text-sm text-slate-400">Customer create — coming soon.</p>
        </main>
    </AppLayout>
</template>
