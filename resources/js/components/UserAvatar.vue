<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { useInitials } from '@/composables/useInitials';
import type { User } from '@/types';

type Props = {
    user?: User | null;
    class?: string;
    fallbackClass?: string;
};

const props = defineProps<Props>();
const { getInitials } = useInitials();

const imageFailed = ref(false);

const imageSrc = computed(() => {
    if (imageFailed.value) {
        return null;
    }

    const source =
        typeof props.user?.image_url === 'string' && props.user.image_url
            ? props.user.image_url
            : typeof props.user?.image === 'string' && props.user.image
              ? `/storage/${props.user.image.replace(/^\/?storage\//, '')}`
              : null;

    if (!source) {
        return null;
    }

    const version =
        typeof props.user?.updated_at === 'string' ? props.user.updated_at : '';

    return version
        ? `${source}${source.includes('?') ? '&' : '?'}v=${encodeURIComponent(version)}`
        : source;
});

watch(
    () => [props.user?.image_url, props.user?.image, props.user?.updated_at],
    () => {
        imageFailed.value = false;
    },
);
</script>

<template>
    <span
        :class="[
            'relative flex shrink-0 items-center justify-center overflow-hidden bg-muted',
            props.class,
        ]"
    >
        <img
            v-if="imageSrc"
            :src="imageSrc"
            :alt="user.name"
            class="h-full w-full object-cover"
            @error="imageFailed = true"
        />
        <span
            v-else
            :class="[
                'flex h-full w-full items-center justify-center bg-muted',
                fallbackClass,
            ]"
        >
            {{ getInitials(user?.name) }}
        </span>
    </span>
</template>
