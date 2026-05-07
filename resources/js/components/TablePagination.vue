<script setup lang="ts">
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

defineProps<{
    currentPage: number;
    totalPages: number;
    totalRows: number;
    pageStart: number;
    pageEnd: number;
    rowsPerPage: number;
}>();

const emit = defineEmits<{
    goToPage: [page: number];
    updateRowsPerPage: [rowsPerPage: number];
}>();

const rowOptions = [10, 20, 30, 50];
</script>

<template>
    <div
        class="flex flex-col gap-3 border-t border-slate-100 px-4 py-3 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between"
    >
        <div>
            Showing
            <span class="font-bold text-slate-700">
                {{ totalRows === 0 ? 0 : pageStart + 1 }}
            </span>
            -
            <span class="font-bold text-slate-700">{{ pageEnd }}</span>
            of
            <span class="font-bold text-slate-700">{{ totalRows }}</span>
            rows
        </div>

        <div class="flex items-center gap-2">
            <select
                :value="rowsPerPage"
                class="h-8 rounded-lg border border-slate-200 bg-white px-2 text-xs font-bold text-slate-600 outline-none focus:border-[#007882] focus:ring-2 focus:ring-[#007882]/20"
                @change="
                    emit(
                        'updateRowsPerPage',
                        Number(($event.target as HTMLSelectElement).value),
                    )
                "
            >
                <option
                    v-for="option in rowOptions"
                    :key="option"
                    :value="option"
                >
                    {{ option }} / page
                </option>
            </select>
            <span class="font-medium text-slate-400">
                Page {{ currentPage }} of {{ totalPages }}
            </span>
            <Button
                variant="outline"
                size="icon"
                class="size-8 rounded-lg"
                :disabled="currentPage === 1"
                @click="emit('goToPage', currentPage - 1)"
            >
                <ChevronLeft class="size-4" />
                <span class="sr-only">Previous page</span>
            </Button>
            <Button
                variant="outline"
                size="icon"
                class="size-8 rounded-lg"
                :disabled="currentPage === totalPages"
                @click="emit('goToPage', currentPage + 1)"
            >
                <ChevronRight class="size-4" />
                <span class="sr-only">Next page</span>
            </Button>
        </div>
    </div>
</template>
