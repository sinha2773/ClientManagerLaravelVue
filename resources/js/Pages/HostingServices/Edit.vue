<template>
    <Head title="Edit Hosting Service" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Edit Hosting Service
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

                            <!-- Package -->
                            <div>
                                <InputLabel for="package_name" value="Package" />
                                <TextInput
                                    id="package_name"
                                    v-model="form.package_name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError :message="form.errors.package_name" class="mt-2" />
                            </div>

                            <!-- Start Date -->
                            <div>
                                <InputLabel for="start_date" value="Start Date" />
                                <TextInput
                                    id="start_date"
                                    v-model="form.start_date"
                                    type="date"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError :message="form.errors.start_date" class="mt-2" />
                            </div>

                            <!-- Renewal Date -->
                            <div>
                                <InputLabel for="renewal_date" value="Renewal Date" />
                                <TextInput
                                    id="renewal_date"
                                    v-model="form.renewal_date"
                                    type="date"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError :message="form.errors.renewal_date" class="mt-2" />
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

                            <!-- Payment Status -->
                            <div>
                                <InputLabel for="payment_status" value="Payment Status" />
                                <select
                                    id="payment_status"
                                    v-model="form.payment_status"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="unpaid">Unpaid</option>
                                    <option value="paid">Paid</option>
                                    <option value="partially_paid">Partially Paid</option>
                                </select>
                                <InputError :message="form.errors.payment_status" class="mt-2" />
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

                            <!-- Username -->
                            <div>
                                <InputLabel for="username" value="Username" />
                                <TextInput
                                    id="username"
                                    v-model="form.username"
                                    type="text"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError :message="form.errors.username" class="mt-2" />
                            </div>

                            <!-- Password -->
                            <div>
                                <InputLabel for="password" value="Password" />
                                <TextInput
                                    id="password"
                                    v-model="form.password"
                                    type="text"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError :message="form.errors.password" class="mt-2" />
                            </div>

                            <!-- Server IP (Optional) -->
                            <div>
                                <InputLabel for="server_ip" value="Server IP (Optional)" />
                                <TextInput
                                    id="server_ip"
                                    v-model="form.server_ip"
                                    type="text"
                                    class="mt-1 block w-full"
                                    placeholder="e.g., 192.168.1.1"
                                />
                                <InputError :message="form.errors.server_ip" class="mt-2" />
                            </div>

                            <!-- Control Panel URL (Optional) -->
                            <div>
                                <InputLabel for="control_panel_url" value="Control Panel URL (Optional)" />
                                <TextInput
                                    id="control_panel_url"
                                    v-model="form.control_panel_url"
                                    type="url"
                                    class="mt-1 block w-full"
                                    placeholder="https://cpanel.example.com"
                                />
                                <InputError :message="form.errors.control_panel_url" class="mt-2" />
                            </div>
                        </div>

                        <div class="mt-6 flex items-center justify-end gap-4">
                            <a
                                :href="route('hosting-services.index')"
                                class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                            >
                                Cancel
                            </a>
                            <PrimaryButton :disabled="form.processing">
                                Update Hosting Service
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

const props = defineProps({
    hostingService: Object,
    domains: Array,
});

const form = useForm({
    domain_id: props.hostingService.domain_id,
    provider: props.hostingService.provider,
    package_name: props.hostingService.package_name,
    start_date: props.hostingService.start_date,
    renewal_date: props.hostingService.renewal_date,
    status: props.hostingService.status,
    payment_status: props.hostingService.payment_status,
    price: props.hostingService.price,
    username: props.hostingService.username,
    password: props.hostingService.password,
    server_ip: props.hostingService.server_ip || '',
    control_panel_url: props.hostingService.control_panel_url || '',
});

const submit = () => {
    form.patch(route('hosting-services.update', props.hostingService.id));
};
</script> 