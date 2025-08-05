<template>
    <Head title="Domains" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Domains
                </h2>
                <Link
                    :href="route('domains.create')"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                >
                    Add New Domain
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Domain
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Client
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Expiry
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Services
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <tr v-for="domain in domains" :key="domain.id">
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <div class="font-medium text-gray-900">{{ domain.name }}</div>
                                            <div class="text-sm text-gray-500">{{ domain.registrar }}</div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <Link
                                                :href="route('clients.show', domain.client.id)"
                                                class="text-indigo-600 hover:text-indigo-900"
                                            >
                                                {{ domain.client.name }}
                                            </Link>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <div class="text-sm text-gray-900">
                                                {{ formatDate(domain.expiry_date) }}
                                            </div>
                                            <div
                                                v-if="domain.is_expiring_soon"
                                                class="mt-1 text-xs font-medium text-red-600"
                                            >
                                                Expires in {{ getDaysUntilExpiry(domain.expiry_date) }} days
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <div class="space-x-2">
                                                <span
                                                    v-if="domain.has_hosting"
                                                    class="inline-flex items-center rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-medium text-purple-800"
                                                >
                                                    Hosting
                                                </span>
                                                <span
                                                    v-if="domain.has_ssl"
                                                    class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800"
                                                >
                                                    SSL
                                                </span>
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <div class="flex flex-col space-y-1">
                                                <span
                                                    :class="{
                                                        'bg-green-100 text-green-800': domain.status === 'active',
                                                        'bg-red-100 text-red-800': domain.status === 'inactive'
                                                    }"
                                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                                >
                                                    {{ domain.status }}
                                                </span>
                                                <span
                                                    :class="{
                                                        'bg-green-100 text-green-800': domain.payment_status === 'paid',
                                                        'bg-yellow-100 text-yellow-800': domain.payment_status === 'partial',
                                                        'bg-red-100 text-red-800': domain.payment_status === 'unpaid'
                                                    }"
                                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                                >
                                                    {{ domain.payment_status }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                            <Link
                                                :href="route('domains.edit', domain.id)"
                                                class="text-indigo-600 hover:text-indigo-900"
                                            >
                                                Edit
                                            </Link>
                                            <button
                                                @click="deleteDomain(domain.id)"
                                                class="ml-4 text-red-600 hover:text-red-900"
                                            >
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    domains: Array,
});

const deleteDomain = (id) => {
    if (confirm('Are you sure you want to delete this domain?')) {
        router.delete(route('domains.destroy', id));
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString();
};

const getDaysUntilExpiry = (expiryDate) => {
    const days = Math.ceil((new Date(expiryDate) - new Date()) / (1000 * 60 * 60 * 24));
    return days;
};
</script> 