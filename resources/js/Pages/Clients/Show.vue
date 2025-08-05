<template>
    <Head :title="client.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ client.name }}
                </h2>
                <div class="flex space-x-4">
                    <Link
                        :href="route('clients.edit', client.id)"
                        class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500"
                    >
                        Edit Client
                    </Link>
                    <button
                        @click="deleteClient"
                        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                    >
                        Delete Client
                    </button>
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
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Client Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Client Information</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-600">Email</p>
                                <p class="text-base">{{ client.email }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Phone</p>
                                <p class="text-base">{{ client.phone || '-' }}</p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-sm text-gray-600">Address</p>
                                <p class="text-base whitespace-pre-line">{{ client.address || '-' }}</p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-sm text-gray-600">Notes</p>
                                <p class="text-base whitespace-pre-line">{{ client.notes || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Status</p>
                                <span
                                    :class="[
                                        'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                        client.active
                                            ? 'bg-green-100 text-green-800'
                                            : 'bg-red-100 text-red-800'
                                    ]"
                                >
                                    {{ client.active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Statistics</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-600">Total Spent</p>
                                <p class="text-2xl font-semibold">${{ stats.total_spent.toFixed(2) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Active Services</p>
                                <div class="flex space-x-4 mt-2">
                                    <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                        Domains: {{ stats.active_services.domains }}
                                    </span>
                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                        SSL: {{ stats.active_services.ssl_certificates }}
                                    </span>
                                    <span class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800">
                                        Hosting: {{ stats.active_services.hosting_services }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Domains -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Domains</h3>
                            <Link
                                :href="route('domains.create')"
                                class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700"
                            >
                                Add Domain
                            </Link>
                        </div>
                        <div v-if="client.domains.length === 0" class="text-gray-500 text-center py-4">
                            No domains registered for this client.
                        </div>
                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expiry</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="domain in client.domains" :key="domain.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ domain.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ domain.expiry_date }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                :class="[
                                                    'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                                    domain.status === 'active' ? 'bg-green-100 text-green-800' :
                                                    domain.status === 'expired' ? 'bg-red-100 text-red-800' :
                                                    'bg-yellow-100 text-yellow-800'
                                                ]"
                                            >
                                                {{ domain.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                :class="[
                                                    'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                                    domain.payment_status === 'paid' ? 'bg-green-100 text-green-800' :
                                                    domain.payment_status === 'partial' ? 'bg-yellow-100 text-yellow-800' :
                                                    'bg-red-100 text-red-800'
                                                ]"
                                            >
                                                {{ domain.payment_status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <Link
                                                :href="route('domains.show', domain.id)"
                                                class="text-indigo-600 hover:text-indigo-900 mr-3"
                                            >
                                                View
                                            </Link>
                                            <Link
                                                :href="route('domains.edit', domain.id)"
                                                class="text-yellow-600 hover:text-yellow-900 mr-3"
                                            >
                                                Edit
                                            </Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- SSL Certificates -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">SSL Certificates</h3>
                            <Link
                                :href="route('ssl-certificates.create')"
                                class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700"
                            >
                                Add SSL Certificate
                            </Link>
                        </div>
                        <div v-if="client.ssl_certificates.length === 0" class="text-gray-500 text-center py-4">
                            No SSL certificates registered for this client.
                        </div>
                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Domain</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expiry</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="cert in client.ssl_certificates" :key="cert.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ cert.domain }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ cert.type }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ cert.expiry_date }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                :class="[
                                                    'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                                    cert.status === 'active' ? 'bg-green-100 text-green-800' :
                                                    cert.status === 'expired' ? 'bg-red-100 text-red-800' :
                                                    'bg-yellow-100 text-yellow-800'
                                                ]"
                                            >
                                                {{ cert.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                :class="[
                                                    'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                                    cert.payment_status === 'paid' ? 'bg-green-100 text-green-800' :
                                                    cert.payment_status === 'partial' ? 'bg-yellow-100 text-yellow-800' :
                                                    'bg-red-100 text-red-800'
                                                ]"
                                            >
                                                {{ cert.payment_status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <Link
                                                :href="route('ssl-certificates.show', cert.id)"
                                                class="text-indigo-600 hover:text-indigo-900 mr-3"
                                            >
                                                View
                                            </Link>
                                            <Link
                                                :href="route('ssl-certificates.edit', cert.id)"
                                                class="text-yellow-600 hover:text-yellow-900 mr-3"
                                            >
                                                Edit
                                            </Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Hosting Services -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Hosting Services</h3>
                            <Link
                                :href="route('hosting-services.create')"
                                class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700"
                            >
                                Add Hosting Service
                            </Link>
                        </div>
                        <div v-if="client.hosting_services.length === 0" class="text-gray-500 text-center py-4">
                            No hosting services registered for this client.
                        </div>
                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Provider</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Renewal</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="service in client.hosting_services" :key="service.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ service.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ service.type }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ service.provider }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ service.renewal_date }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                :class="[
                                                    'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                                    service.status === 'active' ? 'bg-green-100 text-green-800' :
                                                    service.status === 'suspended' ? 'bg-red-100 text-red-800' :
                                                    service.status === 'terminated' ? 'bg-gray-100 text-gray-800' :
                                                    'bg-yellow-100 text-yellow-800'
                                                ]"
                                            >
                                                {{ service.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                :class="[
                                                    'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                                    service.payment_status === 'paid' ? 'bg-green-100 text-green-800' :
                                                    service.payment_status === 'partial' ? 'bg-yellow-100 text-yellow-800' :
                                                    'bg-red-100 text-red-800'
                                                ]"
                                            >
                                                {{ service.payment_status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <Link
                                                :href="route('hosting-services.show', service.id)"
                                                class="text-indigo-600 hover:text-indigo-900 mr-3"
                                            >
                                                View
                                            </Link>
                                            <Link
                                                :href="route('hosting-services.edit', service.id)"
                                                class="text-yellow-600 hover:text-yellow-900 mr-3"
                                            >
                                                Edit
                                            </Link>
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
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    client: {
        type: Object,
        required: true
    },
    stats: {
        type: Object,
        required: true
    }
});

function deleteClient() {
    if (confirm(`Are you sure you want to delete ${props.client.name}? This will also delete all associated domains, SSL certificates, and hosting services.`)) {
        router.delete(route('clients.destroy', props.client.id));
    }
}
</script> 