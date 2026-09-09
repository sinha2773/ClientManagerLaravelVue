<template>
    <Head title="Create Client" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Create Client
            </h2>
        </template>

        <div class="py-12">
            <div class="page-container">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Name -->
                            <div>
                                <InputLabel for="name" value="Name" />
                                <TextInput
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError :message="form.errors.name" class="mt-2" />
                            </div>

                            <!-- Short Name -->
                            <div>
                                <InputLabel for="short_name" value="Short Name" />
                                <TextInput
                                    id="short_name"
                                    v-model="form.short_name"
                                    type="text"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="form.errors.short_name" class="mt-2" />
                            </div>

                            <!-- Email -->
                            <div>
                                <InputLabel for="email" value="Email" />
                                <TextInput
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError :message="form.errors.email" class="mt-2" />
                            </div>

                            <!-- Phone -->
                            <div>
                                <InputLabel for="phone" value="Phone" />
                                <TextInput
                                    id="phone"
                                    v-model="form.phone"
                                    type="tel"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="form.errors.phone" class="mt-2" />
                            </div>

                            <!-- Company -->
                            <div>
                                <InputLabel for="company" value="Company" />
                                <TextInput
                                    id="company"
                                    v-model="form.company"
                                    type="text"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="form.errors.company" class="mt-2" />
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

                            <!-- Client Category -->
                            <div>
                                <InputLabel for="client_category_id" value="Client Category" />
                                <select
                                    id="client_category_id"
                                    v-model="form.client_category_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option :value="null">Select Category</option>
                                    <option v-for="category in categories" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.client_category_id" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="client_type" value="Client Type" />
                                <select
                                    id="client_type"
                                    v-model="form.client_type"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option v-for="type in CLIENT_TYPES" :key="type.value" :value="type.value">
                                        {{ type.label }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.client_type" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="marketing_partner_id" value="Marketing Partner" />
                                <select
                                    id="marketing_partner_id"
                                    v-model="form.marketing_partner_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option :value="null">Select Marketing Partner</option>
                                    <option v-for="partner in marketingPartners" :key="partner.id" :value="partner.id">
                                        {{ partner.name }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.marketing_partner_id" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="eims_monthly" value="Monthly Student Fee" />
                                <TextInput
                                    id="eims_monthly"
                                    v-model="form.eims_monthly"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="form.errors.eims_monthly" class="mt-2" />
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="mt-6">
                            <InputLabel for="address" value="Address" />
                            <textarea
                                id="address"
                                v-model="form.address"
                                rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            ></textarea>
                            <InputError :message="form.errors.address" class="mt-2" />
                        </div>

                        <div class="mt-6 flex items-center justify-end gap-4">
                            <a
                                :href="route('clients.index')"
                                class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                            >
                                Cancel
                            </a>
                            <PrimaryButton :disabled="form.processing">
                                Create Client
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
import { CLIENT_TYPES } from '@/constants/clientTypes';
import { ref } from 'vue';

const props = defineProps({
    categories: Array,
    marketingPartners: Array,
});

const form = useForm({
    name: '',
    short_name: '',
    email: '',
    phone: '',
    company: '',
    address: '',
    status: 'active',
    client_category_id: null,
    client_type: 'private',
    marketing_partner_id: null,
    eims_monthly: '',
});

const submit = () => {
    form.post(route('clients.store'));
};
</script> 
