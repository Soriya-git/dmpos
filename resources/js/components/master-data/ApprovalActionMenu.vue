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
    | 'created'
    | 'submitted'
    | 'approved'
    | 'received'
    | 'closed'
    | 'rejected'
    | 'cancelled';

const props = withDefaults(
    defineProps<{
        status: ApprovalStatus | string;
        viewLabel?: string;
        actionableStatuses?: string[];
    }>(),
    {
        viewLabel: undefined,
        actionableStatuses: () => ['draft', 'pending', 'created', 'submitted'],
    },
);

const emit = defineEmits<{
    view: [];
    approve: [];
    reject: [];
    cancel: [];
}>();

const canAct = computed(() =>
    props.actionableStatuses.includes(String(props.status)),
);

const viewText = computed(
    () => props.viewLabel ?? (canAct.value ? 'Edit' : 'View'),
);
</script>

<template>
    <DropdownMenu :modal="false">
        <DropdownMenuTrigger as-child>
            <Button
                type="button"
                variant="ghost"
                size="icon"
                class="mx-auto flex size-8 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-[#007882]"
                title="Actions"
            >
                <MoreVertical class="size-4" />
                <span class="sr-only">Open actions</span>
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" class="z-[80] w-40">
            <DropdownMenuItem @select="emit('view')">
                <Eye v-if="viewText === 'View'" class="size-4 text-[#007882]" />
                <Pencil v-else class="size-4 text-[#007882]" />
                {{ viewText }}
            </DropdownMenuItem>
            <DropdownMenuSeparator />
            <DropdownMenuItem :disabled="!canAct" @select="emit('approve')">
                <CheckCircle2 class="size-4 text-emerald-600" />
                Approve
            </DropdownMenuItem>
            <DropdownMenuItem :disabled="!canAct" @select="emit('reject')">
                <XCircle class="size-4 text-rose-600" />
                Reject
            </DropdownMenuItem>
            <DropdownMenuItem
                :disabled="!canAct"
                variant="destructive"
                @select="emit('cancel')"
            >
                <Ban class="size-4" />
                Cancel
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
