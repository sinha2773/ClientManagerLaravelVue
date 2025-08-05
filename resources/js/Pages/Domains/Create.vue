<template>
    <Head title="Create Domain" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Create Domain
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Client -->
                            <div>
                                <InputLabel for="client_id" value="Client" />
                                <select
                                    id="client_id"
                                    v-model="form.client_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required
                                >
                                    <option value="">Select a client</option>
                                    <option
                                        v-for="client in clients"
                                        :key="client.id"
                                        :value="client.id"
                                    >
                                        {{ client.name }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.client_id" class="mt-2" />
                            </div>

                            <!-- Domain Name -->
                            <div>
                                <InputLabel for="name" value="Domain Name" />
                                <TextInput
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError :message="form.errors.name" class="mt-2" />
                            </div>

                            <!-- Registrar -->
                            <div>
                                <InputLabel for="registrar" value="Registrar" />
                                <TextInput
                                    id="registrar"
                                    v-model="form.registrar"
                                    type="text"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError :message="form.errors.registrar" class="mt-2" />
                            </div>

                            <!-- Registration Date -->
                            <div>
                                <InputLabel for="registration_date" value="Registration Date" />
                                <TextInput
                                    id="registration_date"
                                    v-model="form.registration_date"
                                    type="date"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError :message="form.errors.registration_date" class="mt-2" />
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

                            <!-- Payment Status -->
                            <div>
                                <InputLabel for="payment_status" value="Payment Status" />
                                <select
                                    id="payment_status"
                                    v-model="form.payment_status"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="paid">Paid</option>
                                    <option value="partial">Partial</option>
                                    <option value="unpaid">Unpaid</option>
                                </select>
                                <InputError :message="form.errors.payment_status" class="mt-2" />
                            </div>
                        </div>

                        <!-- Auto Renew -->
                        <div class="mt-6">
                            <label class="flex items-center">
                                <input
                                    type="checkbox"
                                    v-model="form.auto_renew"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                />
                                <span class="ml-2 text-sm text-gray-600">Auto Renew</span>
                            </label>
                            <InputError :message="form.errors.auto_renew" class="mt-2" />
                        </div>

                        <div class="mt-6 flex items-center justify-end gap-4">
                            <a
                                :href="route('domains.index')"
                                class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                            >
                                Cancel
                            </a>
                            <PrimaryButton :disabled="form.processing">
                                Create Domain
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
    clients: Array,
});

const form = useForm({
    client_id: '',
    name: '',
    registrar: '',
    registration_date: new Date().toISOString().split('T')[0],
    expiry_date: '',
    auto_renew: false,
    status: 'active',
    price: '',
    payment_status: 'unpaid',
});

const submit = () => {
    form.post(route('domains.store'));
};
</script> 