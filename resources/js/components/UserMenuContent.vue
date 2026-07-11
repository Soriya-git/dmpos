<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import {
    Camera,
    LogOut,
    Monitor,
    Moon,
    Palette,
    Sun,
    UserCog,
} from 'lucide-vue-next';
import { ref } from 'vue';
import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuSub,
    DropdownMenuSubContent,
    DropdownMenuSubTrigger,
} from '@/components/ui/dropdown-menu';
import UserInfo from '@/components/UserInfo.vue';
import { useAppearance } from '@/composables/useAppearance';
import type { User } from '@/types';
import { logout } from '@/routes';

type Props = {
    user: User;
};

const { appearance, updateAppearance } = useAppearance();
const imageInput = ref<HTMLInputElement | null>(null);

const handleLogout = (event: Event) => {
    event.preventDefault();

    router.post(logout().url, {
        replace: true,
    });
};

const openImagePicker = () => {
    imageInput.value?.click();
};

const openUsers = () => {
    router.visit('/users');
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
        <DropdownMenuItem @click="openUsers">
            <UserCog class="mr-2 h-4 w-4" />
            Manage User
        </DropdownMenuItem>
        <input
            ref="imageInput"
            type="file"
            accept="image/jpeg,image/png,image/webp"
            class="hidden"
            @change="updatePicture"
        />
        <DropdownMenuSub>
            <DropdownMenuSubTrigger>
                <Palette class="mr-2 h-4 w-4" />
                Theme
            </DropdownMenuSubTrigger>
            <DropdownMenuSubContent>
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
            </DropdownMenuSubContent>
        </DropdownMenuSub>
    </DropdownMenuGroup>
    <DropdownMenuSeparator />
    <DropdownMenuItem>
        <button
            type="button"
            class="flex w-full items-center text-left"
            @click="handleLogout"
            data-test="logout-button"
        >
            <LogOut class="mr-2 h-4 w-4" />
            Log out
        </button>
    </DropdownMenuItem>
</template>
