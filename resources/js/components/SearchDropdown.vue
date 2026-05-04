<script setup lang="ts">
import { onClickOutside } from '@vueuse/core';
import { Check, ChevronDown, Search, X } from 'lucide-vue-next';
import {
    computed,
    nextTick,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
} from 'vue';

type OptionValue = string | number;

export type SearchDropdownOption = {
    value: OptionValue;
    label: string;
    description?: string | null;
    meta?: string | null;
    disabled?: boolean;
};

const props = withDefaults(
    defineProps<{
        modelValue?: OptionValue | null;
        options: SearchDropdownOption[];
        placeholder?: string;
        searchPlaceholder?: string;
        emptyText?: string;
        allowCustom?: boolean;
        disabled?: boolean;
        inputClass?: string;
    }>(),
    {
        modelValue: null,
        placeholder: 'Select option',
        searchPlaceholder: 'Search...',
        emptyText: 'No results found.',
        allowCustom: false,
        disabled: false,
        inputClass: '',
    },
);

const emit = defineEmits<{
    'update:modelValue': [value: OptionValue | null];
    select: [option: SearchDropdownOption | null];
}>();

const root = ref<HTMLElement | null>(null);
const dropdown = ref<HTMLElement | null>(null);
const isOpen = ref(false);
const query = ref('');
const dropdownStyle = ref({
    top: '0px',
    left: '0px',
    width: '0px',
});

const selectedOption = computed(() => {
    if (props.modelValue === null || props.modelValue === undefined) {
        return null;
    }

    return (
        props.options.find((option) => {
            return String(option.value) === String(props.modelValue);
        }) ?? null
    );
});

const filteredOptions = computed(() => {
    const search = query.value.trim().toLowerCase();

    if (!search) {
        return props.options.slice(0, 60);
    }

    return props.options
        .filter((option) => {
            return [option.label, option.description, option.meta]
                .filter(Boolean)
                .some((value) => String(value).toLowerCase().includes(search));
        })
        .slice(0, 60);
});

watch(
    () => props.modelValue,
    (value) => {
        if (props.allowCustom) {
            query.value = selectedOption.value?.label ?? String(value ?? '');
            return;
        }

        query.value = selectedOption.value?.label ?? '';
    },
    { immediate: true },
);

onClickOutside([root, dropdown], () => {
    isOpen.value = false;

    if (!props.allowCustom) {
        query.value = selectedOption.value?.label ?? '';
    }
});

function updateDropdownPosition() {
    const rect = root.value?.getBoundingClientRect();

    if (!rect) return;

    dropdownStyle.value = {
        top: `${rect.bottom + 4}px`,
        left: `${rect.left}px`,
        width: `${rect.width}px`,
    };
}

async function openDropdown() {
    if (props.disabled) return;

    isOpen.value = true;
    await nextTick();
    updateDropdownPosition();
}

function updateQuery(value: string) {
    query.value = value;
    void openDropdown();

    if (props.allowCustom) {
        emit('update:modelValue', value);
        emit('select', selectedOption.value);
    }
}

function selectOption(option: SearchDropdownOption) {
    if (option.disabled) return;

    emit('update:modelValue', option.value);
    emit('select', option);
    query.value = option.label;
    isOpen.value = false;
}

function clearSelection() {
    query.value = '';
    emit('update:modelValue', '');
    emit('select', null);
    isOpen.value = false;
}

watch(isOpen, async (open) => {
    if (!open) return;

    await nextTick();
    updateDropdownPosition();
});

onMounted(() => {
    window.addEventListener('resize', updateDropdownPosition);
    window.addEventListener('scroll', updateDropdownPosition, true);
});

onBeforeUnmount(() => {
    window.removeEventListener('resize', updateDropdownPosition);
    window.removeEventListener('scroll', updateDropdownPosition, true);
});
</script>

<template>
    <div ref="root" class="relative">
        <div class="relative">
            <Search
                class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"
            />
            <input
                :value="query"
                type="text"
                :placeholder="placeholder || searchPlaceholder"
                :disabled="disabled"
                :class="[
                    'h-10 w-full rounded-lg border border-slate-200 bg-white py-2 pr-16 pl-10 text-sm transition outline-none focus:border-[#007882] focus:ring-2 focus:ring-[#007882]/20 disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-400',
                    inputClass,
                ]"
                autocomplete="off"
                @focus="openDropdown"
                @click="openDropdown"
                @input="updateQuery(($event.target as HTMLInputElement).value)"
                @keydown.down.prevent="openDropdown"
                @keydown.esc.prevent="isOpen = false"
            />
            <button
                v-if="query && !disabled"
                type="button"
                class="absolute top-1/2 right-8 flex size-6 -translate-y-1/2 items-center justify-center rounded text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                title="Clear"
                @click="clearSelection"
            >
                <X class="size-3.5" />
            </button>
            <button
                type="button"
                class="absolute top-1/2 right-2 flex size-6 -translate-y-1/2 items-center justify-center rounded text-slate-400 hover:bg-slate-100 hover:text-slate-600 disabled:pointer-events-none disabled:opacity-50"
                :disabled="disabled"
                title="Open options"
                @click="isOpen ? (isOpen = false) : openDropdown()"
            >
                <ChevronDown
                    class="size-4 transition-transform"
                    :class="isOpen ? 'rotate-180' : ''"
                />
            </button>
        </div>

        <Teleport to="body">
            <div
                v-if="isOpen"
                ref="dropdown"
                class="fixed z-[9999] max-h-72 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-2xl"
                :style="dropdownStyle"
            >
                <div class="max-h-72 overflow-y-auto p-1">
                    <button
                        v-for="option in filteredOptions"
                        :key="String(option.value)"
                        type="button"
                        class="flex w-full items-start gap-3 rounded-md px-3 py-2 text-left text-sm transition hover:bg-[#23aa8f]/10 disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="option.disabled"
                        @click="selectOption(option)"
                    >
                        <span class="min-w-0 flex-1">
                            <span
                                class="block truncate font-semibold text-slate-800"
                            >
                                {{ option.label }}
                            </span>
                            <span
                                v-if="option.description || option.meta"
                                class="mt-0.5 block truncate text-xs text-slate-500"
                            >
                                {{ option.description }}
                                <template
                                    v-if="option.description && option.meta"
                                >
                                    /
                                </template>
                                {{ option.meta }}
                            </span>
                        </span>
                        <Check
                            v-if="
                                selectedOption &&
                                String(selectedOption.value) ===
                                    String(option.value)
                            "
                            class="mt-0.5 size-4 text-[#007882]"
                        />
                    </button>

                    <div
                        v-if="filteredOptions.length === 0"
                        class="px-3 py-6 text-center text-sm font-medium text-slate-400"
                    >
                        {{ emptyText }}
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>
