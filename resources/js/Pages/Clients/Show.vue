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
            <div class="page-container space-y-6">
                <!-- Client Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Client Information</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-600">Short Name</p>
                                <p class="text-base">{{ client.short_name || '-' }}</p>
                            </div>
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
                            <div>
                                <p class="text-sm text-gray-600">Client Type</p>
                                <p class="text-base">{{ client.client_type ? formatClientType(client.client_type) : '-' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Monthly Student Fee</p>
                                <p class="text-base">Tk {{ formatCurrency(client.eims_monthly) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bill Actions -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900">Generate Bill</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Start a bill with {{ client.name }} and the selected fee type prefilled.
                        </p>
                        <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                            <Link
                                v-for="action in billActions"
                                :key="action.serviceType"
                                :href="route('bills.create', { client_id: client.id, service_type: action.serviceType })"
                                class="inline-flex items-center justify-center rounded-md px-4 py-2 text-sm font-semibold text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2"
                                :class="action.className"
                            >
                                {{ action.label }}
                            </Link>
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
                                <p class="text-2xl font-semibold">Tk {{ formatCurrency(stats.total_spent) }}</p>
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

                <!-- Billing History -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Billing History</h3>
                                <p class="mt-1 text-sm text-gray-500">Bills generated for {{ client.name }}.</p>
                            </div>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <div>
                                    <label for="billing_academic_year" class="block text-sm font-medium text-gray-700">Academic Year</label>
                                    <select
                                        id="billing_academic_year"
                                        v-model="billingFilterForm.academic_year"
                                        class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        @change="applyBillingFilters"
                                    >
                                        <option value="">All Academic Years</option>
                                        <option v-for="year in academicYears" :key="year" :value="year">{{ year }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="billing_type" class="block text-sm font-medium text-gray-700">Billing Type</label>
                                    <select
                                        id="billing_type"
                                        v-model="billingFilterForm.billing_type"
                                        class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        @change="applyBillingFilters"
                                    >
                                        <option value="">All Billing Types</option>
                                        <option value="domain">Domain</option>
                                        <option value="hosting">Hosting</option>
                                        <option value="ssl_certificate">SSL</option>
                                        <option value="eims_fee">EIMS Fees</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Bill Number</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Academic Year</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Type</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Amount</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Payment</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Created</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <tr v-for="bill in billingHistory.data" :key="bill.id">
                                        <td class="whitespace-nowrap px-4 py-4 text-sm font-medium text-gray-900">{{ bill.bill_number }}</td>
                                        <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-700">{{ bill.academic_year || '-' }}</td>
                                        <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-700">{{ formatBillingType(bill.service_type) }}</td>
                                        <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-medium text-gray-900">Tk {{ formatCurrency(bill.amount) }}</td>
                                        <td class="whitespace-nowrap px-4 py-4">
                                            <span
                                                class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                                :class="bill.payment_status === 'paid' ? 'bg-green-100 text-green-800' : bill.payment_status === 'partially_paid' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'"
                                            >
                                                {{ formatPaymentStatus(bill.payment_status) }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-700">{{ formatDate(bill.created_at) }}</td>
                                        <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-medium">
                                            <Link :href="route('bills.show', bill.id)" class="text-indigo-600 hover:text-indigo-900">View</Link>
                                        </td>
                                    </tr>
                                    <tr v-if="billingHistory.data.length === 0">
                                        <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500">No bills found for these filters.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-if="billingHistory.last_page > 1" class="mt-6 flex flex-wrap gap-1">
                            <Link
                                v-for="link in billingHistory.links"
                                :key="link.label"
                                :href="link.url || '#'"
                                preserve-scroll
                                class="rounded-md border px-3 py-2 text-sm"
                                :class="[
                                    link.active ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-gray-300 bg-white text-gray-600',
                                    !link.url ? 'pointer-events-none opacity-50' : 'hover:bg-gray-50',
                                ]"
                                v-html="link.label"
                            />
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
import { formatClientType } from '@/constants/clientTypes';
import { ref } from 'vue';

function formatCurrency(value) {
    if (value === null || value === undefined || value === '') {
        return '-';
    }

    return Number(value).toFixed(2);
}

const props = defineProps({
    client: {
        type: Object,
        required: true
    },
    stats: {
        type: Object,
        required: true
    },
    billingHistory: {
        type: Object,
        required: true
    },
    billingFilters: {
        type: Object,
        required: true
    },
    academicYears: {
        type: Array,
        required: true
    },
});

const billingFilterForm = ref({
    academic_year: props.billingFilters.academic_year || '',
    billing_type: props.billingFilters.billing_type || '',
});

const billActions = [
    { label: 'Domain Bill', serviceType: 'domain', className: 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500' },
    { label: 'Hosting Bill', serviceType: 'hosting', className: 'bg-purple-600 hover:bg-purple-700 focus:ring-purple-500' },
    { label: 'SSL Bill', serviceType: 'ssl_certificate', className: 'bg-emerald-600 hover:bg-emerald-700 focus:ring-emerald-500' },
    { label: 'EIMS Fees Bill', serviceType: 'eims_fee', className: 'bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500' },
];

function applyBillingFilters() {
    router.get(route('clients.show', props.client.id), billingFilterForm.value, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function formatBillingType(type) {
    return {
        domain: 'Domain',
        hosting: 'Hosting',
        ssl_certificate: 'SSL',
        eims_fee: 'EIMS Fees',
    }[type] || type;
}

function formatPaymentStatus(status) {
    return {
        unpaid: 'Unpaid',
        partially_paid: 'Partially Paid',
        paid: 'Paid',
    }[status] || status;
}

function formatDate(value) {
    return new Date(value).toLocaleDateString();
}

function deleteClient() {
    if (confirm(`Are you sure you want to delete ${props.client.name}? This will also delete all associated domains, SSL certificates, and hosting services.`)) {
        router.delete(route('clients.destroy', props.client.id));
    }
}
</script> 
