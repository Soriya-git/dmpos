<script setup lang="ts">
import { Settings } from 'lucide-vue-next';
import { computed } from 'vue';

type RoleRow = {
    id: number;
    name: string;
    guard_name: string;
    permissions_count: number;
    permissions: string[];
};

const props = defineProps<{
    roles: RoleRow[];
    search: string;
}>();

const emit = defineEmits<{
    configure: [roleName: string];
}>();

const normalizedSearch = computed(() => props.search.trim().toLowerCase());

const filteredRoles = computed(() => {
    if (!normalizedSearch.value) return props.roles;

    return props.roles.filter((role) =>
        [role.name, role.guard_name, ...role.permissions]
            .join(' ')
            .toLowerCase()
            .includes(normalizedSearch.value),
    );
});
</script>

<template>
    <section
        class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
    >
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead class="sticky top-0 z-10 bg-slate-50">
                    <tr>
                        <th class="w-12 px-4 py-3 text-center">#</th>
                        <th class="px-4 py-3 text-left">Role Name</th>
                        <th class="px-4 py-3 text-left">Guard</th>
                        <th class="px-4 py-3 text-left">
                            Assigned Permissions
                        </th>
                        <th class="px-4 py-3 text-left">Permission Count</th>
                        <th class="w-20 px-4 py-3 text-center">Config</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr
                        v-for="(role, index) in filteredRoles"
                        :key="role.id"
                        class="hover:bg-slate-50"
                    >
                        <td
                            class="px-4 py-3 text-center text-[10px] font-bold text-slate-400"
                        >
                            {{ String(index + 1).padStart(2, '0') }}
                        </td>
                        <td class="px-4 py-3 text-sm font-black text-slate-800">
                            {{ role.name }}
                        </td>
                        <td class="px-4 py-3">
                            <span
                                class="rounded bg-slate-100 px-2 py-1 text-[10px] font-bold text-slate-500"
                            >
                                {{ role.guard_name }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex max-w-xl flex-wrap gap-1">
                                <span
                                    v-for="permission in role.permissions.slice(
                                        0,
                                        5,
                                    )"
                                    :key="permission"
                                    class="rounded bg-teal-50 px-2 py-1 text-[10px] font-bold text-[#007882]"
                                >
                                    {{ permission }}
                                </span>
                                <span
                                    v-if="role.permissions.length > 5"
                                    class="rounded bg-slate-100 px-2 py-1 text-[10px] font-bold text-slate-500"
                                >
                                    +{{ role.permissions.length - 5 }} more
                                </span>
                            </div>
                        </td>
                        <td class="px-4 py-3 font-bold text-slate-600">
                            {{ role.permissions_count }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button
                                type="button"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-300 hover:bg-slate-100 hover:text-slate-600"
                                @click="emit('configure', role.name)"
                            >
                                <Settings class="h-4 w-4" />
                            </button>
                        </td>
                    </tr>
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
