<script setup lang="ts">
import {
    Ban,
    CheckCircle2,
    Eye,
    MoreVertical,
    Pencil,
    XCircle,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

type ApprovalStatus =
    | 'draft'
    | 'pending'
    | 'approved'
    | 'rejected'
    | 'cancelled';

const props = defineProps<{
    status: ApprovalStatus;
}>();

const emit = defineEmits<{
    view: [];
    approve: [];
    reject: [];
    cancel: [];
}>();

const isFinal = computed(() =>
    ['approved', 'rejected', 'cancelled'].includes(props.status),
);
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button
                variant="ghost"
                size="icon"
                class="size-8 text-slate-500 hover:text-[#007882]"
            >
                <MoreVertical class="size-4" />
                <span class="sr-only">Open actions</span>
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" class="w-40">
            <DropdownMenuItem @select="emit('view')">
                <Eye v-if="isFinal" class="size-4" />
                <Pencil v-else class="size-4" />
                {{ isFinal ? 'View' : 'Edit' }}
            </DropdownMenuItem>
            <DropdownMenuSeparator />
            <DropdownMenuItem :disabled="isFinal" @select="emit('approve')">
                <CheckCircle2 class="size-4 text-emerald-600" />
                Approve
            </DropdownMenuItem>
            <DropdownMenuItem :disabled="isFinal" @select="emit('reject')">
                <XCircle class="size-4 text-rose-600" />
                Reject
            </DropdownMenuItem>
            <DropdownMenuItem
                :disabled="isFinal"
                variant="destructive"
                @select="emit('cancel')"
            >
                <Ban class="size-4" />
                Cancel
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
