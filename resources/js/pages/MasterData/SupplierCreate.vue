<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Building2, Phone } from 'lucide-vue-next';
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

const emit = defineEmits<{
    success: [];
}>();

const form = useForm({
    code: '',
    name: '',
    contact_person: '',
    phone: '',
    email: '',
    address: '',
    note: '',
    status: 'pending',
});

const canSubmit = computed(() => form.name.trim().length > 0);

function submit() {
    form.clearErrors();

    if (!canSubmit.value) {
        form.setError('name', 'Supplier name is required.');
        return;
    }

    form.post('/master-data/suppliers', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            emit('success');
        },
    });
}

defineExpose({ submit, isProcessing: computed(() => form.processing) });
</script>

<template>
    <div class="w-full">
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-4 2xl:gap-8">
            <!-- Left: Basic Info -->
            <div class="space-y-6 xl:col-span-1">
                <div
                    class="rounded-lg border border-slate-100 bg-white p-6 shadow-sm"
                >
                    <h3
                        class="mb-4 flex items-center text-sm font-bold text-slate-700 uppercase"
                    >
                        <Building2 class="mr-2 size-4 text-[#007882]" />
                        Basic Info
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <label
                                class="mb-1 block text-xs font-bold text-slate-500 uppercase"
                            >
                                Supplier Code
                            </label>
                            <Input
                                v-model="form.code"
                                class="h-10 rounded-lg border-slate-200 font-mono"
                                placeholder="e.g. SUP-001"
                            />
                            <InputError :message="form.errors.code" />
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-xs font-bold text-slate-500 uppercase"
                            >
                                Supplier Name
                            </label>
                            <Input
                                v-model="form.name"
                                class="h-10 rounded-lg border-slate-200"
                                placeholder="Enter supplier name"
                            />
                            <InputError :message="form.errors.name" />
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-xs font-bold text-slate-500 uppercase"
                            >
                                Status
                            </label>
                            <select
                                v-model="form.status"
                                class="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-600 outline-none focus:border-[#007882] focus:ring-2 focus:ring-[#007882]/20"
                            >
                                <option value="draft">Draft</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                            </select>
                            <InputError :message="form.errors.status" />
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-xs font-bold text-slate-500 uppercase"
                            >
                                Note
                            </label>
                            <textarea
                                v-model="form.note"
                                class="min-h-20 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#007882] focus:ring-2 focus:ring-[#007882]/20"
                                placeholder="Optional note"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Contact Details -->
            <div class="space-y-6 xl:col-span-3">
                <div
                    class="overflow-hidden rounded-lg border border-slate-100 bg-white shadow-sm"
                >
                    <div
                        class="flex items-center border-b border-slate-100 bg-slate-50 p-4"
                    >
                        <Phone class="mr-2 size-4 text-[#007882]" />
                        <h3
                            class="text-xs font-bold tracking-wider text-slate-700 uppercase"
                        >
                            Contact Details
                        </h3>
                    </div>

                    <div class="grid grid-cols-1 gap-6 p-6 md:grid-cols-2">
                        <div>
                            <label
                                class="mb-1 block text-xs font-bold text-slate-500 uppercase"
                            >
                                Contact Person
                            </label>
                            <Input
                                v-model="form.contact_person"
                                class="h-10 rounded-lg border-slate-200"
                                placeholder="Full name"
                            />
                            <InputError :message="form.errors.contact_person" />
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-xs font-bold text-slate-500 uppercase"
                            >
                                Phone
                            </label>
                            <Input
                                v-model="form.phone"
                                class="h-10 rounded-lg border-slate-200"
                                placeholder="+66 ..."
                            />
                            <InputError :message="form.errors.phone" />
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-xs font-bold text-slate-500 uppercase"
                            >
                                Email
                            </label>
                            <Input
                                v-model="form.email"
                                type="email"
                                class="h-10 rounded-lg border-slate-200"
                                placeholder="supplier@example.com"
                            />
                            <InputError :message="form.errors.email" />
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-xs font-bold text-slate-500 uppercase"
                            >
                                Address
                            </label>
                            <Input
                                v-model="form.address"
                                class="h-10 rounded-lg border-slate-200"
                                placeholder="Street, city, country"
                            />
                            <InputError :message="form.errors.address" />
                        </div>
                    </div>
                </div>

                <!-- Summary bar matching CreatePO grand total style -->
                <div
                    class="rounded-lg bg-[#2a4858] p-6 text-white shadow-lg"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase text-white/50">
                                Registering as
                            </p>
                            <p class="mt-0.5 text-lg font-bold">
                                {{
                                    form.name.trim() || 'New Supplier'
                                }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-bold uppercase text-white/50">
                                Status
                            </p>
                            <p class="mt-0.5 text-lg font-bold text-[#fafa6e] capitalize">
                                {{ form.status }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
