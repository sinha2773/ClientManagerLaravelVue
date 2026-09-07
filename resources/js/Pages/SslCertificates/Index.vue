<template>
    <Head title="SSL Certificates" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    SSL Certificates
                </h2>
                <Link
                    :href="route('ssl-certificates.create')"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                >
                    Add New SSL Certificate
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-4">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-7">
                            <div>
                                <input
                                    type="text"
                                    v-model="filterForm.search"
                                    placeholder="Search provider, type..."
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    @input="applyFilters"
                                />
                            </div>
                            <div>
                                <select
                                    v-model="filterForm.status"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    @change="applyFilters"
                                >
                                    <option value="">All Statuses</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div>
                                <select
                                    v-model="filterForm.payment_status"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    @change="applyFilters"
                                >
                                    <option value="">All Payment Statuses</option>
                                    <option value="paid">Paid</option>
                                    <option value="unpaid">Unpaid</option>
                                    <option value="partially_paid">Partially Paid</option>
                                </select>
                            </div>
                            <div>
                                <select
                                    v-model="filterForm.client_id"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    @change="applyFilters"
                                >
                                    <option value="">All Clients</option>
                                    <option v-for="client in clients" :key="client.id" :value="client.id">
                                        {{ client.name }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <select
                                    v-model="filterForm.provider_id"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    @change="applyFilters"
                                >
                                    <option value="">All Providers</option>
                                    <option v-for="provider in providers" :key="provider.id" :value="provider.id">
                                        {{ provider.name }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <select
                                    v-model="filterForm.type"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    @change="applyFilters"
                                >
                                    <option value="">All Types</option>
                                    <option v-for="type in types" :key="type" :value="type">
                                        {{ type }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <button
                                    @click="resetFilters"
                                    class="block w-full rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                                >
                                    Reset Filters
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-0 text-gray-900 sm:p-4">
                        <div class="w-full">
                            <table class="responsive-data-table divide-y divide-gray-200">
                                <colgroup>
                                    <col class="w-[20%]" />
                                    <col class="w-[14%]" />
                                    <col class="w-[8%]" />
                                    <col class="w-[12%]" />
                                    <col class="w-[17%]" />
                                    <col class="w-[10%]" />
                                    <col class="w-[19%]" />
                                </colgroup>
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Domain
                                        </th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Provider
                                        </th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Type
                                        </th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Issue Date
                                        </th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Expiry Date
                                        </th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Status
                                        </th>
                                        <th class="px-3 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <tr v-for="certificate in sslCertificates" :key="certificate.id">
                                        <td data-label="Domain" class="px-3 py-3 align-top">
                                            <Link
                                                :href="route('domains.show', certificate.domain.id)"
                                                class="text-indigo-600 hover:text-indigo-900"
                                            >
                                                {{ certificate.domain.name }}
                                            </Link>
                                        </td>
                                        <td data-label="Provider" class="px-3 py-3 align-top">
                                            {{ certificate.providerRel ? certificate.providerRel.name : certificate.provider }}
                                        </td>
                                        <td data-label="Type" class="px-3 py-3 align-top">
                                            {{ certificate.type }}
                                        </td>
                                        <td data-label="Issue Date" class="px-3 py-3 align-top">
                                            <div class="text-sm text-gray-900">
                                                {{ formatDate(certificate.issue_date) }}
                                            </div>
                                        </td>
                                        <td data-label="Expiry Date" class="px-3 py-3 align-top">
                                            <div class="text-sm text-gray-900">
                                                {{ formatDate(certificate.expiry_date) }}
                                            </div>
                                            <div
                                                v-if="certificate.is_expired"
                                                class="mt-1 text-xs font-medium text-red-600"
                                            >
                                                Expired {{ Math.abs(certificate.days_until_expiry) }} days ago
                                            </div>
                                            <div
                                                v-else-if="isExpiringSoon(certificate.expiry_date)"
                                                class="mt-1 text-xs font-medium text-red-600"
                                            >
                                                Expires in {{ certificate.days_until_expiry }} days
                                            </div>
                                        </td>
                                        <td data-label="Status" class="px-3 py-3 align-top">
                                            <span
                                                :class="{
                                                    'bg-green-100 text-green-800': certificate.status === 'active',
                                                    'bg-red-100 text-red-800': certificate.status === 'inactive'
                                                }"
                                                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                            >
                                                {{ certificate.status }}
                                            </span>
                                        </td>
                                        <td data-label="Actions" class="px-3 py-3 text-right text-sm font-medium align-top">
                                            <div class="flex flex-wrap gap-x-3 gap-y-2 md:justify-end">
                                                <Link
                                                    v-if="certificate.is_expired || certificate.days_until_expiry <= 90"
                                                    :href="route('bills.create', { client_id: certificate.client_id, service_type: 'ssl_certificate', service_id: certificate.id })"
                                                    class="font-semibold text-green-600 hover:text-green-900"
                                                >
                                                    Generate Bill
                                                </Link>
                                                <Link
                                                    :href="route('ssl-certificates.edit', certificate.id)"
                                                    class="text-indigo-600 hover:text-indigo-900"
                                                >
                                                    Edit
                                                </Link>
                                                <button
                                                    @click="deleteCertificate(certificate.id)"
                                                    class="text-red-600 hover:text-red-900"
                                                >
                                                    Delete
                                                </button>
                                            </div>
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
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    sslCertificates: Array,
    filters: Object,
    clients: Array,
    domains: Array,
    providers: Array,
    types: Array,
});

const filterForm = ref({
    search: props.filters?.search || '',
    status: props.filters?.status || '',
    payment_status: props.filters?.payment_status || '',
    client_id: props.filters?.client_id || '',
    provider_id: props.filters?.provider_id || '',
    type: props.filters?.type || '',
});

let debounceTimer = null;
const applyFilters = () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        const params = {};
        for (const [key, value] of Object.entries(filterForm.value)) {
            if (value) params[key] = value;
        }
        router.get(route('ssl-certificates.index'), params, { preserveState: true, preserveScroll: true });
    }, 300);
};

const resetFilters = () => {
    filterForm.value = { search: '', status: '', payment_status: '', client_id: '', provider_id: '', type: '' };
    router.get(route('ssl-certificates.index'), {}, { preserveState: true, preserveScroll: true });
};

const deleteCertificate = (id) => {
    if (confirm('Are you sure you want to delete this SSL certificate?')) {
        router.delete(route('ssl-certificates.destroy', id));
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
