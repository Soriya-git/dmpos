<script setup lang="ts" generic="TRow extends { id: number | string }">
defineProps<{
    rows: TRow[];
    emptyText?: string;
}>();
</script>

<template>
    <div
        class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
    >
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] border-collapse">
                <thead class="bg-slate-50">
                    <tr>
                        <slot name="head" />
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(row, index) in rows"
                        :key="row.id"
                        class="border-b border-slate-100 transition-colors last:border-b-0 hover:bg-slate-50"
                    >
                        <slot name="row" :row="row" :index="index" />
                    </tr>
                    <tr v-if="rows.length === 0">
                        <td
                            class="px-4 py-10 text-center text-sm text-slate-400"
                            colspan="10"
                        >
                            {{ emptyText ?? 'No data found.' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
