<script setup lang="ts" generic="TRow extends { id: number | string }">
import TablePagination from '@/components/TablePagination.vue';
import { usePagination } from '@/composables/usePagination';

const props = withDefaults(
    defineProps<{
        rows: TRow[];
        emptyText?: string;
        rowsPerPage?: number;
    }>(),
    {
        rowsPerPage: 10,
    },
);

const {
    currentPage,
    totalRows,
    totalPages,
    pageStart,
    pageEnd,
    paginatedRows,
    goToPage,
    pageSize,
    setRowsPerPage,
} = usePagination(
    () => props.rows,
    () => props.rowsPerPage,
);
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
                        v-for="(row, index) in paginatedRows"
                        :key="row.id"
                        class="border-b border-slate-100 transition-colors last:border-b-0 hover:bg-slate-50"
                    >
                        <slot
                            name="row"
                            :row="row"
                            :index="pageStart + index"
                        />
                    </tr>
                    <tr v-if="totalRows === 0">
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
        <TablePagination
            :current-page="currentPage"
            :total-pages="totalPages"
            :total-rows="totalRows"
            :page-start="pageStart"
            :page-end="pageEnd"
            :rows-per-page="pageSize"
            @go-to-page="goToPage"
            @update-rows-per-page="setRowsPerPage"
        />
    </div>
</template>
