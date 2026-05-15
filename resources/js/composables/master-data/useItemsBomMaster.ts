import { computed, reactive, ref } from 'vue';

export type MasterDataView = 'items' | 'bom' | 'units';
export type ApprovalStatus =
    | 'draft'
    | 'pending'
    | 'approved'
    | 'rejected'
    | 'cancelled';

export type ItemRecord = {
    id: number;
    code: string;
    name: string;
    category: string;
    primaryUnit: string;
    status: ApprovalStatus;
};

export type BomRecord = {
    id: number;
    code: string;
    targetProduct: string;
    components: string;
    status: ApprovalStatus;
};

export type UnitRecord = {
    id: number;
    code: string;
    name: string;
    category: string;
    type: string;
    ratio: string;
    status: ApprovalStatus;
};

export type PanelKind = MasterDataView;

export function useItemsBomMaster(
    initialItems: ItemRecord[] = [],
    initialBom: BomRecord[] = [],
    initialUnits: UnitRecord[] = [],
) {
    const activeView = ref<MasterDataView>('items');
    const search = ref('');
    const panelOpen = ref(false);
    const panelKind = ref<PanelKind>('items');
    const selectedRecord = ref<ItemRecord | BomRecord | UnitRecord | null>(
        null,
    );
    const items = reactive([...initialItems]);
    const bom = reactive([...initialBom]);
    const units = reactive([...initialUnits]);

    const activeRows = computed(() => {
        const term = search.value.trim().toLowerCase();
        const rows =
            activeView.value === 'items'
                ? items
                : activeView.value === 'bom'
                  ? bom
                  : units;

        if (!term) {
            return rows;
        }

        return rows.filter((row) =>
            Object.values(row).some((value) =>
                String(value).toLowerCase().includes(term),
            ),
        );
    });

    const panelTitle = computed(() => {
        const prefix = selectedRecord.value ? 'Edit' : 'New';

        if (panelKind.value === 'items') {
            return `${prefix} Item Registry`;
        }

        if (panelKind.value === 'bom') {
            return `${prefix} Recipe (BOM)`;
        }

        return `${prefix} Unit / Package`;
    });

    const panelSubtitle = computed(() => {
        if (panelKind.value === 'items') {
            return 'Add Product to Master Data';
        }

        if (panelKind.value === 'bom') {
            return 'Define Production Requirements';
        }

        return 'Ratio & Conversion Management';
    });

    function switchView(view: MasterDataView) {
        activeView.value = view;
    }

    function openPanel(
        kind = activeView.value,
        record: typeof selectedRecord.value = null,
    ) {
        panelKind.value = kind;
        selectedRecord.value = record;
        panelOpen.value = true;
    }

    function closePanel() {
        panelOpen.value = false;
        selectedRecord.value = null;
    }

    function setStatus(
        collection: ItemRecord[] | BomRecord[] | UnitRecord[],
        id: number,
        status: ApprovalStatus,
    ) {
        const record = collection.find((row) => row.id === id);

        if (
            record &&
            !['approved', 'rejected', 'cancelled'].includes(record.status)
        ) {
            record.status = status;
        }
    }

    return {
        activeView,
        search,
        panelOpen,
        panelKind,
        selectedRecord,
        items,
        bom,
        units,
        activeRows,
        panelTitle,
        panelSubtitle,
        switchView,
        openPanel,
        closePanel,
        setStatus,
    };
}
