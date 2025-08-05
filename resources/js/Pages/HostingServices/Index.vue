<template>
    <Head title="Hosting Services" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Hosting Services
                </h2>
                <Link
                    :href="route('hosting-services.create')"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                >
                    Add New Hosting Service
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
                                            Provider
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Package
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Renewal Date
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
                                    <tr v-for="service in hostingServices" :key="service.id">
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <Link
                                                :href="route('domains.show', service.domain.id)"
                                                class="text-indigo-600 hover:text-indigo-900"
                                            >
                                                {{ service.domain.name }}
                                            </Link>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            {{ service.provider }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            {{ service.package_name }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <div class="text-sm text-gray-900">
                                                {{ formatDate(service.renewal_date) }}
                                            </div>
                                            <div
                                                v-if="isExpiringSoon(service.renewal_date)"
                                                class="mt-1 text-xs font-medium text-red-600"
                                            >
                                                Expires in {{ getDaysUntilExpiry(service.renewal_date) }} days
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <span
                                                :class="{
                                                    'bg-green-100 text-green-800': service.status === 'active',
                                                    'bg-red-100 text-red-800': service.status === 'inactive'
                                                }"
                                                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                            >
                                                {{ service.status }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                            <Link
                                                :href="route('hosting-services.edit', service.id)"
                                                class="text-indigo-600 hover:text-indigo-900"
                                            >
                                                Edit
                                            </Link>
                                            <button
                                                @click="deleteHostingService(service.id)"
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
import { router } from '@inertiajs/vue3';

const props = defineProps({
    hostingServices: Array,
});

const deleteHostingService = (id) => {
    if (confirm('Are you sure you want to delete this hosting service?')) {
        router.delete(route('hosting-services.destroy', id));
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString();
};

const isExpiringSoon = (date) => {
    const daysUntilExpiry = getDaysUntilExpiry(date);
    return daysUntilExpiry <= 30 && daysUntilExpiry > 0;
};

const getDaysUntilExpiry = (expiryDate) => {
    const days = Math.ceil((new Date(expiryDate) - new Date()) / (1000 * 60 * 60 * 24));
    return days;
};
</script> 