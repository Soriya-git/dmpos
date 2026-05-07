import { computed, ref, type MaybeRefOrGetter, toValue, watch } from 'vue';

export function usePagination<T>(
    rows: MaybeRefOrGetter<T[]>,
    rowsPerPage: MaybeRefOrGetter<number> = 10,
) {
    const currentPage = ref(1);
    const selectedRowsPerPage = ref(toValue(rowsPerPage));

    const totalRows = computed(() => toValue(rows).length);
    const pageSize = computed(() => selectedRowsPerPage.value);
    const totalPages = computed(() =>
        Math.max(1, Math.ceil(totalRows.value / pageSize.value)),
    );
    const pageStart = computed(() => (currentPage.value - 1) * pageSize.value);
    const pageEnd = computed(() =>
        Math.min(pageStart.value + pageSize.value, totalRows.value),
    );
    const paginatedRows = computed(() =>
        toValue(rows).slice(pageStart.value, pageEnd.value),
    );

    watch(
        () => [toValue(rows), pageSize.value] as const,
        () => {
            currentPage.value = 1;
        },
    );

    function goToPage(page: number) {
        currentPage.value = Math.min(Math.max(page, 1), totalPages.value);
    }

    function setRowsPerPage(value: number) {
        selectedRowsPerPage.value = value;
        currentPage.value = 1;
    }

    return {
        currentPage,
        totalRows,
        totalPages,
        pageSize,
        pageStart,
        pageEnd,
        paginatedRows,
        goToPage,
        setRowsPerPage,
    };
}
