<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { ChevronDown, LoaderCircle, Save, Search } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import type { User as AuthUser } from '@/types';

type PermissionAction = 'view' | 'create' | 'update' | 'delete' | 'manage';

type UserRow = AuthUser & {
    role: string;
    roles: string[];
    branch: string;
    branches: string[];
    status: 'active' | 'pending';
    permissions: string[];
};

type SystemFunction = {
    key: string;
    module: string;
    name: string;
    route: string;
};

type FunctionGroup = {
    module: string;
    items: SystemFunction[];
};

const props = defineProps<{
    search: string;
    users: UserRow[];
}>();

const emit = defineEmits<{
    toast: [message: string];
}>();

const actions: { key: PermissionAction; label: string }[] = [
    { key: 'view', label: 'View' },
    { key: 'create', label: 'Create' },
    { key: 'update', label: 'Update' },
    { key: 'delete', label: 'Delete' },
    { key: 'manage', label: 'Manage' },
];

const systemFunctions: SystemFunction[] = [
    {
        key: 'dashboard',
        module: 'Dashboard',
        name: 'Dashboard',
        route: '/dashboard',
    },
    {
        key: 'orders',
        module: 'Sale Operations',
        name: 'Order',
        route: '/orders',
    },
    {
        key: 'daily-session-stock',
        module: 'Manage Operations',
        name: 'Stock Report',
        route: '/operations-report/daily-session-stock',
    },
    {
        key: 'daily-session-menu',
        module: 'Manage Operations',
        name: 'Menu Report',
        route: '/operations-report/daily-session-menu',
    },
    {
        key: 'sales',
        module: 'Manage Operations',
        name: 'Sales',
        route: '/sales',
    },
    {
        key: 'purchase',
        module: 'Stock Operations',
        name: 'Purchase',
        route: '/purchase',
    },
    {
        key: 'goods-receipts',
        module: 'Stock Operations',
        name: 'Goods Receipt',
        route: '/goods-receipts',
    },
    {
        key: 'putaway',
        module: 'Stock Operations',
        name: 'Putaway',
        route: '/putaway',
    },
    {
        key: 'balance-on-hand',
        module: 'Stock Movements',
        name: 'Balance On Hand',
        route: '/balance-on-hand',
    },
    {
        key: 'stock-settlements',
        module: 'Stock Movements',
        name: 'Sale Settlements',
        route: '/stock-movements/stock-settlements',
    },
    {
        key: 'stock-adjustments',
        module: 'Stock Movements',
        name: 'Stock Adjustments',
        route: '/stock-movements/stock-adjustments',
    },
    {
        key: 'internal-transfer',
        module: 'Stock Movements',
        name: 'Internal Transfer',
        route: '/stock-movements/internal-transfer',
    },
    {
        key: 'stock-customer',
        module: 'Stock Movements',
        name: 'Customer Keep Stock',
        route: '/stock-customer',
    },
    {
        key: 'stock-write-off',
        module: 'Stock Movements',
        name: 'Stock Write-off',
        route: '/stock-movements/write-off',
    },
    {
        key: 'company-branches',
        module: 'Organizations',
        name: 'Our Company',
        route: '/master-data/company-branches',
    },
    {
        key: 'suppliers',
        module: 'Organizations',
        name: 'Suppliers',
        route: '/master-data/suppliers',
    },
    {
        key: 'customers',
        module: 'Organizations',
        name: 'Customers',
        route: '/master-data/customers',
    },
    {
        key: 'membership-cards',
        module: 'Organizations',
        name: 'Membership Cards',
        route: '/membership-cards',
    },
    {
        key: 'products',
        module: 'Stock Master',
        name: 'Items & BOM',
        route: '/master-data/products',
    },
    {
        key: 'menu',
        module: 'Stock Master',
        name: 'Menu',
        route: '/master-data/menu',
    },
    {
        key: 'warehouse-locations',
        module: 'Stock Master',
        name: 'Warehouse & Location',
        route: '/master-data/warehouse-locations',
    },
    {
        key: 'pos-terminals',
        module: 'Dinning Resource',
        name: 'POS',
        route: '/master-data/pos-terminals',
    },
    {
        key: 'seats',
        module: 'Dinning Resource',
        name: 'Seats',
        route: '/master-data/seats',
    },
    {
        key: 'exchange-rates',
        module: 'Finance',
        name: 'Exchange Rate',
        route: '/master-data/exchange-rates',
    },
    {
        key: 'taxes',
        module: 'Finance',
        name: 'Taxes',
        route: '/master-data/taxes',
    },
    {
        key: 'menu-price-lists',
        module: 'Finance',
        name: 'Menu Pricelist',
        route: '/master-data/menu-price-lists',
    },
];

const page = usePage();
const collapsedModules = ref<Record<string, boolean>>({});
const selectedUserId = ref<number | null>(
    Number((page.props.auth as { user?: { id?: number } })?.user?.id) || null,
);
const userSelectorOpen = ref(false);
const userSearch = ref('');
const saving = ref(false);
const permissionOverrides = ref<
    Record<number, Record<string, Record<PermissionAction, boolean>>>
>({});

const normalizedSearch = computed(() => props.search.trim().toLowerCase());
const normalizedUserSearch = computed(() =>
    userSearch.value.trim().toLowerCase(),
);

const selectedUser = computed(() => {
    return (
        props.users.find((user) => user.id === selectedUserId.value) ??
        props.users[0] ??
        null
    );
});

const filteredUsers = computed(() => {
    if (!normalizedUserSearch.value) return props.users;

    return props.users.filter((user) =>
        [
            user.name,
            user.email,
            user.role,
            ...user.roles,
            user.branch,
            ...user.branches,
        ]
            .join(' ')
            .toLowerCase()
            .includes(normalizedUserSearch.value),
    );
});

const filteredSystemFunctions = computed(() => {
    if (!normalizedSearch.value) return systemFunctions;

    return systemFunctions.filter((item) =>
        [item.module, item.name, item.route]
            .join(' ')
            .toLowerCase()
            .includes(normalizedSearch.value),
    );
});

const functionGroups = computed<FunctionGroup[]>(() => {
    const groups = new Map<string, SystemFunction[]>();

    filteredSystemFunctions.value.forEach((item) => {
        groups.set(item.module, [...(groups.get(item.module) ?? []), item]);
    });

    return Array.from(groups, ([module, items]) => ({ module, items }));
});

const permissionScope = computed(() =>
    systemFunctions.flatMap((item) =>
        actions.map((action) => permissionName(item, action.key)),
    ),
);

const selectedUserDraftPermissions = computed(() => {
    if (!selectedUser.value) return [];

    return systemFunctions
        .flatMap((item) =>
            actions.map((action) => ({
                name: permissionName(item, action.key),
                enabled: hasFunctionPermission(item, action.key),
            })),
        )
        .filter((permission) => permission.enabled)
        .map((permission) => permission.name);
});

const selectedUserHasChanges = computed(() => {
    if (!selectedUser.value) return false;

    return Boolean(permissionOverrides.value[selectedUser.value.id]);
});

function toggleModule(module: string) {
    collapsedModules.value[module] = !collapsedModules.value[module];
}

function isModuleCollapsed(module: string) {
    return collapsedModules.value[module] ?? false;
}

function permissionCandidates(item: SystemFunction, action: PermissionAction) {
    const normalizedKey = item.key.replaceAll('-', '_');

    return [
        `${item.key}.${action}`,
        `${normalizedKey}.${action}`,
        `${item.module.toLowerCase().replaceAll(' ', '-')}.${item.key}.${action}`,
        `${item.module.toLowerCase().replaceAll(' ', '_')}.${normalizedKey}.${action}`,
    ];
}

function permissionName(item: SystemFunction, action: PermissionAction) {
    return `${item.key}.${action}`;
}

function hasFunctionPermission(item: SystemFunction, action: PermissionAction) {
    if (!selectedUser.value) return false;

    const override =
        permissionOverrides.value[selectedUser.value.id]?.[item.key]?.[action];

    if (typeof override === 'boolean') {
        return override;
    }

    if (selectedUser.value.roles.includes('System Admin')) {
        return true;
    }

    return permissionCandidates(item, action).some((permission) =>
        selectedUser.value?.permissions.includes(permission),
    );
}

function toggleFunctionPermission(
    item: SystemFunction,
    action: PermissionAction,
) {
    if (!selectedUser.value) return;

    const userId = selectedUser.value.id;
    const userOverrides = permissionOverrides.value[userId] ?? {};
    const functionOverrides = userOverrides[item.key] ?? {};
    const nextValue = !hasFunctionPermission(item, action);

    permissionOverrides.value[userId] = {
        ...userOverrides,
        [item.key]: {
            ...functionOverrides,
            [action]: nextValue,
        },
    };

    emit(
        'toast',
        `Staged ${selectedUser.value.name}'s ${item.name} ${action} permission`,
    );
}

function selectUser(user: UserRow) {
    selectedUserId.value = user.id;
    userSearch.value = '';
    userSelectorOpen.value = false;
}

function savePermissions() {
    if (!selectedUser.value || !selectedUserHasChanges.value) return;

    saving.value = true;

    router.patch(
        `/users/${selectedUser.value.id}/permissions`,
        {
            permission_names: selectedUserDraftPermissions.value,
            permission_scope: permissionScope.value,
        },
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                if (selectedUser.value) {
                    delete permissionOverrides.value[selectedUser.value.id];
                    emit(
                        'toast',
                        `${selectedUser.value.name}'s permissions saved successfully`,
                    );
                }
            },
            onFinish: () => {
                saving.value = false;
            },
        },
    );
}
</script>

<template>
    <section
        class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
    >
        <div
            class="flex flex-col justify-between gap-3 border-b border-slate-200 bg-white px-4 py-3 sm:flex-row sm:items-center"
        >
            <div>
                <p class="text-xs font-bold text-slate-500 uppercase">
                    Selected User
                </p>
                <p class="text-sm font-medium text-slate-700">
                    {{ selectedUser?.email ?? 'No user selected' }}
                </p>
            </div>
            <button
                type="button"
                class="inline-flex h-9 items-center justify-center gap-2 rounded-lg px-4 text-xs font-bold transition"
                :class="
                    selectedUserHasChanges
                        ? 'bg-[#007882] text-white shadow-sm hover:bg-[#2A4858]'
                        : 'bg-slate-100 text-slate-400'
                "
                :disabled="!selectedUserHasChanges || saving"
                @click="savePermissions"
            >
                <LoaderCircle v-if="saving" class="h-4 w-4 animate-spin" />
                <Save v-else class="h-4 w-4" />
                Save
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead class="sticky top-0 z-10 bg-slate-50">
                    <tr>
                        <th class="w-12 px-4 py-3 text-center">#</th>
                        <th class="min-w-72 px-4 py-3 text-left">
                            <div class="relative">
                                <button
                                    type="button"
                                    class="flex h-9 w-full items-center justify-between gap-3 rounded-lg border border-slate-200 bg-white px-3 text-left text-xs font-bold tracking-normal text-slate-700 normal-case"
                                    @click="
                                        userSelectorOpen = !userSelectorOpen
                                    "
                                >
                                    <span class="min-w-0 truncate">
                                        {{
                                            selectedUser
                                                ? selectedUser.name
                                                : 'Select user'
                                        }}
                                    </span>
                                    <ChevronDown
                                        class="h-4 w-4 shrink-0 text-slate-400 transition"
                                        :class="
                                            userSelectorOpen
                                                ? 'rotate-180'
                                                : 'rotate-0'
                                        "
                                    />
                                </button>

                                <div
                                    v-if="userSelectorOpen"
                                    class="absolute top-11 left-0 z-30 w-80 overflow-hidden rounded-lg border border-slate-200 bg-white text-left shadow-xl"
                                >
                                    <div class="relative border-b p-2">
                                        <Search
                                            class="absolute top-1/2 left-5 h-3.5 w-3.5 -translate-y-1/2 text-slate-400"
                                        />
                                        <input
                                            v-model="userSearch"
                                            type="text"
                                            class="h-9 w-full rounded-md border border-slate-200 pr-3 pl-9 text-xs font-medium tracking-normal text-slate-700 normal-case outline-none focus:border-[#007882]"
                                            placeholder="Search user..."
                                        />
                                    </div>
                                    <div class="max-h-64 overflow-y-auto p-1">
                                        <button
                                            v-for="user in filteredUsers"
                                            :key="user.id"
                                            type="button"
                                            class="flex w-full flex-col rounded-md px-3 py-2 text-left tracking-normal normal-case hover:bg-slate-50"
                                            :class="
                                                selectedUser?.id === user.id
                                                    ? 'bg-teal-50 text-[#007882]'
                                                    : 'text-slate-700'
                                            "
                                            @click="selectUser(user)"
                                        >
                                            <span class="text-xs font-bold">
                                                {{ user.name }}
                                            </span>
                                            <span
                                                class="text-[10px] font-medium text-slate-400"
                                            >
                                                {{ user.email }}
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </th>
                        <th
                            v-for="action in actions"
                            :key="action.key"
                            class="w-28 px-4 py-3 text-center"
                        >
                            {{ action.label }}
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <template
                        v-for="group in functionGroups"
                        :key="group.module"
                    >
                        <tr class="bg-slate-100/80">
                            <td colspan="8" class="px-4 py-2">
                                <button
                                    type="button"
                                    class="flex w-full items-center justify-between gap-3 text-left"
                                    @click="toggleModule(group.module)"
                                >
                                    <span
                                        class="text-xs font-black tracking-wider text-[#2A4858] uppercase"
                                    >
                                        {{ group.module }}
                                    </span>
                                    <span
                                        class="flex items-center gap-2 text-[10px] font-bold text-slate-500 uppercase"
                                    >
                                        {{ group.items.length }} functions
                                        <ChevronDown
                                            class="h-4 w-4 transition"
                                            :class="
                                                isModuleCollapsed(group.module)
                                                    ? '-rotate-90'
                                                    : 'rotate-0'
                                            "
                                        />
                                    </span>
                                </button>
                            </td>
                        </tr>
                        <tr
                            v-for="(item, index) in group.items"
                            v-show="!isModuleCollapsed(group.module)"
                            :key="item.key"
                            class="hover:bg-slate-50"
                        >
                            <td
                                class="px-4 py-3 text-center text-[10px] font-bold text-slate-400"
                            >
                                {{ String(index + 1).padStart(2, '0') }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="text-sm text-slate-700">
                                        {{ item.name }}
                                    </span>
                                </div>
                            </td>
                            <td
                                v-for="action in actions"
                                :key="action.key"
                                class="px-4 py-3 text-center"
                            >
                                <button
                                    type="button"
                                    class="relative inline-flex h-6 w-11 items-center rounded-full transition"
                                    :class="
                                        hasFunctionPermission(item, action.key)
                                            ? 'bg-[#23AA8F]'
                                            : 'bg-gray-200'
                                    "
                                    @click="
                                        toggleFunctionPermission(
                                            item,
                                            action.key,
                                        )
                                    "
                                >
                                    <span
                                        class="inline-block h-5 w-5 rounded-full bg-white shadow transition"
                                        :class="
                                            hasFunctionPermission(
                                                item,
                                                action.key,
                                            )
                                                ? 'translate-x-5'
                                                : 'translate-x-0.5'
                                        "
                                    />
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </section>
</template>

<style scoped>
th {
    border-bottom: 2px solid #e2e8f0;
    color: #64748b;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.05em;
    text-transform: uppercase;
}

td {
    border-bottom: 1px solid #f1f5f9;
    color: #334155;
    font-size: 13px;
}
</style>
