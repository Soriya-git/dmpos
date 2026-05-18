<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Camera, LogOut, Monitor, Moon, Palette, Sun } from 'lucide-vue-next';
import { ref } from 'vue';
import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import UserInfo from '@/components/UserInfo.vue';
import { useAppearance } from '@/composables/useAppearance';
import type { User } from '@/types';
import { login, logout } from '@/wayfinder/routes';

type Props = {
    user: User;
};

const { appearance, updateAppearance } = useAppearance();
const imageInput = ref<HTMLInputElement | null>(null);

const handleLogout = () => {
    router.cancelAll();
    router.post(
        logout.url(),
        {},
        {
            onSuccess: () => router.visit(login(), { replace: true }),
        },
    );
};

const openImagePicker = () => {
    imageInput.value?.click();
};

const updatePicture = (event: Event) => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];

    if (!file) return;

    router.post(
        '/profile/picture',
        { image: file },
        {
            forceFormData: true,
            preserveScroll: true,
            onFinish: () => {
                input.value = '';
            },
        },
    );
};

defineProps<Props>();
</script>

<template>
    <DropdownMenuLabel class="p-0 font-normal">
        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
            <UserInfo :user="user" :show-email="true" />
        </div>
    </DropdownMenuLabel>
    <DropdownMenuSeparator />
    <DropdownMenuGroup>
        <DropdownMenuItem @click="openImagePicker">
            <Camera class="mr-2 h-4 w-4" />
            Update picture
        </DropdownMenuItem>
        <input
            ref="imageInput"
            type="file"
            accept="image/jpeg,image/png,image/webp"
            class="hidden"
            @change="updatePicture"
        />
        <DropdownMenuItem @click="updateAppearance('light')">
            <Sun class="mr-2 h-4 w-4" />
            Light
            <span
                v-if="appearance === 'light'"
                class="ml-auto text-xs text-muted-foreground"
                >&#10003;</span
            >
        </DropdownMenuItem>
        <DropdownMenuItem @click="updateAppearance('dark')">
            <Moon class="mr-2 h-4 w-4" />
            Dark
            <span
                v-if="appearance === 'dark'"
                class="ml-auto text-xs text-muted-foreground"
                >&#10003;</span
            >
        </DropdownMenuItem>
        <DropdownMenuItem @click="updateAppearance('system')">
            <Monitor class="mr-2 h-4 w-4" />
            System
            <span
                v-if="appearance === 'system'"
                class="ml-auto text-xs text-muted-foreground"
                >&#10003;</span
            >
        </DropdownMenuItem>
        <DropdownMenuItem @click="updateAppearance('oe')">
            <Palette class="mr-2 h-4 w-4" />
            OE Theme
            <span
                v-if="appearance === 'oe'"
                class="ml-auto text-xs text-muted-foreground"
                >&#10003;</span
            >
        </DropdownMenuItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator />
    <DropdownMenuItem
        class="cursor-pointer"
        data-test="logout-button"
        @click="handleLogout"
    >
        <LogOut class="mr-2 h-4 w-4" />
        Log out
    </DropdownMenuItem>
</template>
