<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import {
    Plus,
    Search,
    ShieldHalf,
    SlidersHorizontal,
    UserPen,
    X,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import UserAvatar from '@/components/UserAvatar.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import RoleTab from '@/pages/Users/Role.vue';
import UserPermissionTab from '@/pages/Users/UserPermission.vue';
import type { User as AuthUser } from '@/types';

type UserRow = AuthUser & {
    role: string;
    roles: string[];
    branch: string;
    branches: string[];
    status: 'active' | 'pending';
    permissions: string[];
    permissions_by_action: Record<PermissionAction, boolean>;
};

type PermissionRow = {
    id: number;
    name: string;
    guard_name: string;
    module: string;
    action: string;
};

type RoleRow = {
    id: number;
    name: string;
    guard_name: string;
    permissions_count: number;
    permissions: string[];
};

type BranchRow = {
    id: number;
    name: string;
};

type PermissionAction = 'view' | 'create' | 'update' | 'delete' | 'manage';
type ActiveView = 'user-permission' | 'role' | 'users';
type PanelType = ActiveView | 'user';

const props = defineProps<{
    users: UserRow[];
    roles: RoleRow[];
    permissions: PermissionRow[];
    branches: BranchRow[];
    summary: {
        users: number;
        roles: number;
        permissions: number;
    };
}>();

const activeView = ref<ActiveView>('users');
const search = ref('');
const toastMessage = ref('');
const panelOpen = ref(false);
const panelType = ref<PanelType>('user-permission');
const panelTarget = ref('');
const roleForm = useForm({
    name: '',
    permission_ids: [] as number[],
});

const tabs: { key: ActiveView; label: string }[] = [
    { key: 'users', label: 'Users' },
    { key: 'user-permission', label: 'User-Permission' },
    { key: 'role', label: 'Role' },
];

const normalizedSearch = computed(() => search.value.trim().toLowerCase());

const filteredUsers = computed(() => {
    if (!normalizedSearch.value) return props.users;

    return props.users.filter((user) => {
        return [
            user.name,
            user.email,
            user.role,
            user.branch,
            ...user.branches,
            ...user.roles,
        ]
            .join(' ')
            .toLowerCase()
            .includes(normalizedSearch.value);
    });
});

const panelTitle = computed(() => {
    if (panelType.value === 'role') return 'Role Configuration';
    if (panelType.value === 'user') return 'User Registry Edit';

    return 'User Permissions';
});

const panelSubtitle = computed(() => {
    if (panelType.value === 'role') return 'Assign existing permissions';
    if (panelType.value === 'user') return 'Manage corporate employee profile';

    return 'Direct access mapping overrides';
});

function setActiveView(view: ActiveView) {
    activeView.value = view;
}

function openPanel(type: PanelType, targetName: string) {
    panelType.value = type;
    panelTarget.value = targetName;
    roleForm.clearErrors();

    if (type === 'role') {
        roleForm.name = targetName === 'New Custom Role' ? '' : targetName;
        roleForm.permission_ids = [];
    }

    panelOpen.value = true;
}

function closePanel() {
    panelOpen.value = false;
}

function handleCreateNew() {
    if (activeView.value === 'role') {
        openPanel('role', 'New Custom Role');
        return;
    }

    if (activeView.value === 'users') {
        openPanel('user', 'New User Employee');
        return;
    }

    openPanel('user-permission', 'New Custom Member');
}

function savePanelChanges() {
    if (panelType.value === 'role') {
        roleForm.post('/users/roles', {
            preserveScroll: true,
            onSuccess: () => {
                closePanel();
                showToast('Role created successfully');
            },
        });

        return;
    }

    closePanel();
    showToast('Master records compiled successfully');
}

function toggleRolePermission(permissionId: number) {
    roleForm.permission_ids = roleForm.permission_ids.includes(permissionId)
        ? roleForm.permission_ids.filter((id) => id !== permissionId)
        : [...roleForm.permission_ids, permissionId];
}

function showToast(message: string) {
    toastMessage.value = message;

    window.setTimeout(() => {
        toastMessage.value = '';
    }, 2500);
}
</script>

<template>
    <Head title="Manage Users" />

    <AppLayout>
        <main class="min-h-screen bg-slate-50 p-4 text-slate-700 lg:p-6">
            <div
                v-if="toastMessage"
                class="fixed right-6 bottom-6 z-50 flex items-center gap-3 rounded-2xl bg-slate-900 px-6 py-4 text-white shadow-2xl"
            >
                <div
                    class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-500/20 text-emerald-400"
                >
                    <ShieldHalf class="h-4 w-4" />
                </div>
                <div>
                    <p
                        class="text-xs font-bold tracking-wider text-slate-400 uppercase"
                    >
                        Security Engine
                    </p>
                    <p class="text-sm font-semibold">{{ toastMessage }}</p>
                </div>
            </div>

            <section
                class="mb-6 grid gap-3 sm:grid-cols-3 lg:max-w-3xl"
                aria-label="Access control summary"
            >
                <div class="rounded-xl border border-slate-200 bg-white p-3">
                    <p class="text-[10px] font-black text-slate-400 uppercase">
                        Users
                    </p>
                    <p class="mt-1 text-2xl font-black text-[#2A4858]">
                        {{ summary.users }}
                    </p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-3">
                    <p class="text-[10px] font-black text-slate-400 uppercase">
                        Roles
                    </p>
                    <p class="mt-1 text-2xl font-black text-[#007882]">
                        {{ summary.roles }}
                    </p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-3">
                    <p class="text-[10px] font-black text-slate-400 uppercase">
                        Permissions
                    </p>
                    <p class="mt-1 text-2xl font-black text-[#23AA8F]">
                        {{ summary.permissions }}
                    </p>
                </div>
            </section>

            <div
                class="mb-6 flex flex-col justify-between gap-4 lg:flex-row lg:items-center"
            >
                <div
                    class="flex gap-4 overflow-x-auto border-b border-slate-200 pb-1 whitespace-nowrap lg:border-none"
                >
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        type="button"
                        class="border-b-[3px] px-2 py-2 text-xs tracking-wider uppercase transition hover:text-[#007882]"
                        :class="
                            activeView === tab.key
                                ? 'border-[#23AA8F] font-black text-[#007882]'
                                : 'border-transparent font-bold text-slate-500'
                        "
                        @click="setActiveView(tab.key)"
                    >
                        {{ tab.label }}
                    </button>
                </div>

                <div class="flex items-center gap-2">
                    <div class="relative">
                        <Search
                            class="absolute top-1/2 left-3 h-3.5 w-3.5 -translate-y-1/2 text-slate-400"
                        />
                        <Input
                            v-model="search"
                            type="text"
                            placeholder="Search Master Directory..."
                            class="h-9 w-56 rounded-lg border-slate-200 bg-white pr-4 pl-9 text-xs focus-visible:ring-[#007882]/30 lg:w-64"
                        />
                    </div>
                    <Button
                        type="button"
                        class="h-9 rounded-lg bg-[#007882] px-4 text-xs font-bold text-white shadow-sm hover:bg-[#2A4858]"
                        @click="handleCreateNew"
                    >
                        <Plus class="h-4 w-4" />
                        New Record
                    </Button>
                </div>
            </div>

            <UserPermissionTab
                v-if="activeView === 'user-permission'"
                :search="search"
                :users="users"
                @toast="showToast"
            />

            <RoleTab
                v-if="activeView === 'role'"
                :roles="roles"
                :search="search"
                @configure="openPanel('role', $event)"
            />

            <section
                v-if="activeView === 'users'"
                class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
            >
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead class="sticky top-0 z-10 bg-slate-50">
                            <tr>
                                <th class="w-12 px-4 py-3 text-center">#</th>
                                <th class="px-4 py-3 text-left">
                                    Full Profile
                                </th>
                                <th class="px-4 py-3 text-left">
                                    Security Level
                                </th>
                                <th class="px-4 py-3 text-left">
                                    Main Store / Branch
                                </th>
                                <th class="px-4 py-3 text-left">Status</th>
                                <th class="w-20 px-4 py-3 text-center">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="(user, index) in filteredUsers"
                                :key="user.id"
                                class="hover:bg-slate-50"
                            >
                                <td
                                    class="px-4 py-3 text-center text-[10px] font-bold text-slate-400"
                                >
                                    {{ String(index + 1).padStart(2, '0') }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <UserAvatar
                                            :user="user"
                                            class="h-8 w-8 rounded-full"
                                            fallback-class="text-xs font-bold text-[#007882]"
                                        />
                                        <div>
                                            <div
                                                class="font-black text-slate-800"
                                            >
                                                {{ user.name }}
                                            </div>
                                            <p
                                                class="text-[10px] font-bold text-slate-400"
                                            >
                                                {{ user.email }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td
                                    class="px-4 py-3 font-medium text-slate-600"
                                >
                                    {{ user.role }}
                                </td>
                                <td class="px-4 py-3 text-slate-500">
                                    {{ user.branch }}
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="rounded-full border px-2.5 py-0.5 text-[10px] font-bold"
                                        :class="
                                            user.status === 'active'
                                                ? 'border-emerald-500/20 bg-emerald-500/10 text-[#23AA8F]'
                                                : 'border-yellow-500/20 bg-yellow-500/10 text-yellow-700'
                                        "
                                    >
                                        {{ user.status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button
                                        type="button"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-300 hover:bg-slate-100 hover:text-slate-600"
                                        @click="openPanel('user', user.name)"
                                    >
                                        <UserPen class="h-4 w-4" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <div
                v-if="panelOpen"
                class="fixed inset-0 z-40 bg-[#2A4858]/30 backdrop-blur-[2px]"
                @click="closePanel"
            />
            <aside
                class="fixed top-0 right-0 z-50 flex h-screen w-full max-w-[450px] flex-col bg-white shadow-2xl transition-transform duration-300"
                :class="panelOpen ? 'translate-x-0' : 'translate-x-full'"
            >
                <header class="bg-[#2A4858] p-6 text-white">
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="text-lg font-bold">
                                {{ panelTitle }}
                            </h2>
                            <p
                                class="mt-1 text-[10px] tracking-widest text-white/50 uppercase"
                            >
                                {{ panelSubtitle }}
                            </p>
                        </div>
                        <button
                            type="button"
                            class="flex h-7 w-7 items-center justify-center rounded-full bg-white/10 transition hover:bg-white/20"
                            @click="closePanel"
                        >
                            <X class="h-4 w-4" />
                        </button>
                    </div>
                </header>

                <div class="flex-1 space-y-4 overflow-y-auto p-6">
                    <div class="rounded-xl bg-slate-50 p-4">
                        <span
                            class="text-[10px] font-bold text-slate-400 uppercase"
                        >
                            Target
                        </span>
                        <p class="mt-1 text-sm font-bold text-slate-800">
                            {{ panelTarget }}
                        </p>
                    </div>

                    <div
                        v-if="panelType === 'user-permission'"
                        class="space-y-4 rounded-xl bg-slate-50 p-4"
                    >
                        <label
                            class="flex items-center gap-2 text-xs font-medium text-slate-600"
                        >
                            <input
                                type="checkbox"
                                checked
                                class="rounded border-gray-300 text-[#007882] focus:ring-[#007882]"
                            />
                            Override standard security template for this user
                            session
                        </label>
                        <div>
                            <label
                                class="mb-1 block text-[10px] font-bold text-slate-500 uppercase"
                            >
                                Access Expiration
                            </label>
                            <Input
                                type="date"
                                class="rounded-lg border-slate-200 bg-white text-xs font-bold focus-visible:ring-[#007882]/30"
                            />
                        </div>
                    </div>

                    <div
                        v-else-if="panelType === 'role'"
                        class="space-y-4 rounded-xl bg-slate-50 p-4"
                    >
                        <div>
                            <label
                                class="mb-1 block text-[10px] font-bold text-slate-400 uppercase"
                            >
                                Role Name
                            </label>
                            <Input
                                v-model="roleForm.name"
                                class="rounded-lg border-slate-200 bg-white text-sm font-bold focus-visible:ring-[#007882]/30"
                                placeholder="Example: Floor Manager"
                            />
                            <p
                                v-if="roleForm.errors.name"
                                class="mt-1 text-xs font-bold text-red-600"
                            >
                                {{ roleForm.errors.name }}
                            </p>
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-[10px] font-bold text-slate-400 uppercase"
                            >
                                Assign Existing Permissions
                            </label>
                            <div
                                class="max-h-80 space-y-1 overflow-y-auto rounded-lg border border-slate-200 bg-white p-2"
                            >
                                <label
                                    v-for="permission in permissions"
                                    :key="permission.id"
                                    class="flex cursor-pointer items-center justify-between gap-3 rounded-md px-2 py-2 text-xs hover:bg-slate-50"
                                >
                                    <span class="min-w-0">
                                        <span
                                            class="block truncate font-mono font-bold text-slate-700"
                                        >
                                            {{ permission.name }}
                                        </span>
                                        <span class="text-slate-400">
                                            {{ permission.module }} /
                                            {{ permission.action }}
                                        </span>
                                    </span>
                                    <input
                                        type="checkbox"
                                        class="rounded border-gray-300 text-[#007882] focus:ring-[#007882]"
                                        :checked="
                                            roleForm.permission_ids.includes(
                                                permission.id,
                                            )
                                        "
                                        @change="
                                            toggleRolePermission(permission.id)
                                        "
                                    />
                                </label>
                            </div>
                            <p
                                v-if="roleForm.errors.permission_ids"
                                class="mt-1 text-xs font-bold text-red-600"
                            >
                                {{ roleForm.errors.permission_ids }}
                            </p>
                        </div>
                    </div>

                    <div v-else class="space-y-4 rounded-xl bg-slate-50 p-4">
                        <div>
                            <label
                                class="mb-1 block text-[10px] font-bold text-slate-400 uppercase"
                            >
                                Full Name
                            </label>
                            <Input
                                :model-value="panelTarget"
                                class="rounded-lg border-slate-200 bg-white text-sm focus-visible:ring-[#007882]/30"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-[10px] font-bold text-slate-400 uppercase"
                            >
                                Workstation Branch
                            </label>
                            <select
                                class="h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-[#007882]"
                            >
                                <option
                                    v-for="branch in branches"
                                    :key="branch.id"
                                >
                                    {{ branch.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-[10px] font-bold text-slate-400 uppercase"
                            >
                                Security Level / Role
                            </label>
                            <select
                                class="h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-[#007882]"
                            >
                                <option v-for="role in roles" :key="role.id">
                                    {{ role.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <footer class="flex gap-3 border-t bg-slate-50 p-6">
                    <Button
                        type="button"
                        variant="outline"
                        class="h-10 flex-1 rounded-lg border-slate-200 bg-white text-xs font-bold text-slate-500"
                        @click="closePanel"
                    >
                        Cancel
                    </Button>
                    <Button
                        type="button"
                        class="h-10 flex-1 rounded-lg bg-[#007882] text-xs font-bold text-white shadow-md transition hover:bg-[#2A4858]"
                        :disabled="roleForm.processing"
                        @click="savePanelChanges"
                    >
                        <SlidersHorizontal class="h-4 w-4" />
                        Save Changes
                    </Button>
                </footer>
            </aside>
        </main>
    </AppLayout>
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
