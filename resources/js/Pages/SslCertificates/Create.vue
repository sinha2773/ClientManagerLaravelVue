<template>
    <Head title="Create SSL Certificate" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Create SSL Certificate
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Domain -->
                            <div>
                                <InputLabel for="domain_id" value="Domain" />
                                <select
                                    id="domain_id"
                                    v-model="form.domain_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required
                                >
                                    <option value="">Select a domain</option>
                                    <option
                                        v-for="domain in domains"
                                        :key="domain.id"
                                        :value="domain.id"
                                    >
                                        {{ domain.name }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.domain_id" class="mt-2" />
                            </div>

                            <!-- Provider -->
                            <div>
                                <InputLabel for="provider" value="Provider" />
                                <TextInput
                                    id="provider"
                                    v-model="form.provider"
                                    type="text"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError :message="form.errors.provider" class="mt-2" />
                            </div>

                            <!-- Type -->
                            <div>
                                <InputLabel for="type" value="Type" />
                                <select
                                    id="type"
                                    v-model="form.type"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required
                                >
                                    <option value="">Select a type</option>
                                    <option value="DV">Domain Validation (DV)</option>
                                    <option value="OV">Organization Validation (OV)</option>
                                    <option value="EV">Extended Validation (EV)</option>
                                </select>
                                <InputError :message="form.errors.type" class="mt-2" />
                            </div>

                            <!-- Expiry Date -->
                            <div>
                                <InputLabel for="expiry_date" value="Expiry Date" />
                                <TextInput
                                    id="expiry_date"
                                    v-model="form.expiry_date"
                                    type="date"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError :message="form.errors.expiry_date" class="mt-2" />
                            </div>

                            <!-- Price -->
                            <div>
                                <InputLabel for="price" value="Price" />
                                <TextInput
                                    id="price"
                                    v-model="form.price"
                                    type="number"
                                    step="0.01"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError :message="form.errors.price" class="mt-2" />
                            </div>

                            <!-- Status -->
                            <div>
                                <InputLabel for="status" value="Status" />
                                <select
                                    id="status"
                                    v-model="form.status"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                                <InputError :message="form.errors.status" class="mt-2" />
                            </div>
                        </div>

                        <div class="mt-6 flex items-center justify-end gap-4">
                            <a
                                :href="route('ssl-certificates.index')"
                                class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                            >
                                Cancel
                            </a>
                            <PrimaryButton :disabled="form.processing">
                                Create SSL Certificate
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { ref } from 'vue';

const props = defineProps({
    domains: Array,
});

const form = useForm({
    domain_id: '',
    provider: '',
    type: '',
    expiry_date: new Date().toISOString().split('T')[0],
    status: 'active',
    price: '',
});

const submit = () => {
    form.post(route('ssl-certificates.store'));
};
</script> 