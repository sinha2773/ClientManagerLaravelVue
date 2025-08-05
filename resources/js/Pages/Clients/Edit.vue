<template>
    <Head title="Edit Client" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Edit Client
                </h2>
                <div class="flex space-x-4">
                    <Link
                        :href="route('clients.show', client.id)"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        View Client
                    </Link>
                    <Link
                        :href="route('clients.index')"
                        class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                    >
                        Back to Clients
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div>
                                    <InputLabel for="name" value="Name" />
                                    <TextInput
                                        id="name"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.name"
                                        required
                                        autofocus
                                    />
                                    <InputError class="mt-2" :message="form.errors.name" />
                                </div>

                                <div>
                                    <InputLabel for="email" value="Email" />
                                    <TextInput
                                        id="email"
                                        type="email"
                                        class="mt-1 block w-full"
                                        v-model="form.email"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.email" />
                                </div>

                                <div>
                                    <InputLabel for="phone" value="Phone" />
                                    <TextInput
                                        id="phone"
                                        type="tel"
                                        class="mt-1 block w-full"
                                        v-model="form.phone"
                                    />
                                    <InputError class="mt-2" :message="form.errors.phone" />
                                </div>

                                <div>
                                    <InputLabel for="company" value="Company" />
                                    <TextInput
                                        id="company"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.company"
                                    />
                                    <InputError class="mt-2" :message="form.errors.company" />
                                </div>

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
                                    <InputError class="mt-2" :message="form.errors.status" />
                                </div>
                            </div>

                            <div>
                                <InputLabel for="address" value="Address" />
                                <TextArea
                                    id="address"
                                    class="mt-1 block w-full"
                                    v-model="form.address"
                                    rows="3"
                                />
                                <InputError class="mt-2" :message="form.errors.address" />
                            </div>

                            <div>
                                <InputLabel for="notes" value="Notes" />
                                <TextArea
                                    id="notes"
                                    class="mt-1 block w-full"
                                    v-model="form.notes"
                                    rows="3"
                                />
                                <InputError class="mt-2" :message="form.errors.notes" />
                            </div>

                            <div class="flex items-center">
                                <InputLabel for="active" class="mr-2" value="Active" />
                                <Checkbox
                                    id="active"
                                    v-model:checked="form.active"
                                />
                                <InputError class="mt-2" :message="form.errors.active" />
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <PrimaryButton
                                    class="ml-4"
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing"
                                >
                                    Update Client
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import TextArea from '@/Components/TextArea.vue';
import Checkbox from '@/Components/Checkbox.vue';

const props = defineProps({
    client: {
        type: Object,
        required: true
    }
});

const form = useForm({
    name: props.client.name,
    email: props.client.email,
    phone: props.client.phone,
    company: props.client.company,
    address: props.client.address,
    status: props.client.status,
    active: props.client.active,
    notes: props.client.notes
});

function submit() {
    form.put(route('clients.update', props.client.id));
}
</script> 