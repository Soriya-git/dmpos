<script setup lang="ts">
import { computed } from 'vue';
import { Utensils } from 'lucide-vue-next';

type MenuItem = {
    id: number;
    name: string;
    image?: string | null;
    category_name?: string | null;
    unit_price: number;
};

const props = defineProps<{
    menu: MenuItem;
    imageSrc?: string | null;
    price: string;
    quantity?: number;
    note?: string;
    processing?: boolean;
}>();

const emit = defineEmits<{
    add: [menu: MenuItem];
    commitNote: [menu: MenuItem];
    updateNote: [value: string];
}>();

const isSelected = computed(() => Number(props.quantity ?? 0) > 0);
</script>

<template>
    <article
        class="flex flex-col rounded-xl border bg-white p-2.5 shadow-sm transition hover:border-[#23AA8F] hover:bg-[#fcfdfd]"
        :class="
            isSelected ? 'border-[#23AA8F] bg-teal-50/60' : 'border-gray-100'
        "
    >
        <div
            class="relative mb-2 aspect-[4/3] overflow-hidden rounded-lg bg-gray-100"
        >
            <img
                v-if="imageSrc"
                :src="imageSrc"
                :alt="menu.name"
                class="h-full w-full object-cover"
            />

            <div
                v-else
                class="flex h-full w-full items-center justify-center text-gray-300"
            >
                <Utensils class="h-9 w-9" />
            </div>
        </div>

        <h3 class="mb-0.5 truncate text-xs font-bold text-[#2A4858]">
            {{ menu.name }}
        </h3>

        <div class="mt-auto flex items-center justify-between gap-2">
            <span class="text-xs font-black text-[#007882]">{{ price }}</span>
            <span class="truncate text-[8px] font-bold text-gray-400 uppercase">
                {{ menu.category_name ?? 'Main' }}
            </span>
        </div>

        <div class="mt-2 grid grid-cols-[1fr_auto] items-center gap-1.5">
            <input
                :value="note"
                type="text"
                placeholder="Note"
                class="h-7 min-w-0 rounded-lg border border-gray-100 bg-white px-2 text-[10px] font-medium text-[#2A4858] transition outline-none placeholder:text-gray-400 focus:border-[#23AA8F]/60 focus:bg-[#23AA8F]/5"
                @input="
                    emit(
                        'updateNote',
                        ($event.target as HTMLInputElement).value,
                    )
                "
                @change="emit('commitNote', menu)"
                @keyup.enter="emit('commitNote', menu)"
            />

            <button
                type="button"
                class="h-7 rounded-lg bg-[#86D780]/20 px-3 text-[10px] font-bold text-[#2A4858] transition hover:bg-[#86D780] hover:text-white disabled:opacity-60"
                :disabled="processing"
                @click="emit('add', menu)"
            >
                Add
            </button>
        </div>
    </article>
</template>
