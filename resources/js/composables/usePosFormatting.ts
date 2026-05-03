export function usePosFormatting() {
    function money(value: number | string | null | undefined) {
        return `$ ${Number(value ?? 0).toFixed(2)}`;
    }

    function imageUrl(path?: string | null) {
        if (!path) return null;
        if (path.startsWith('http')) return path;

        return `/storage/${path}`;
    }

    return {
        imageUrl,
        money,
    };
}
