<template>
    <Head title="Hosting Services" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
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
            <div class="page-container">
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-4">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6">
                            <div>
                                <input
                                    type="text"
                                    v-model="filterForm.search"
                                    placeholder="Search provider, package, IP..."
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
                                    <col class="w-[23%]" />
                                    <col class="w-[16%]" />
                                    <col class="w-[15%]" />
                                    <col class="w-[18%]" />
                                    <col class="w-[10%]" />
                                    <col class="w-[18%]" />
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
                                            Package
                                        </th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Renewal Date
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
                                    <tr v-for="service in hostingServices" :key="service.id">
                                        <td data-label="Domain" class="px-3 py-3 align-top">
                                            <Link
                                                :href="route('domains.show', service.domain.id)"
                                                class="text-indigo-600 hover:text-indigo-900"
                                            >
                                                {{ service.domain.name }}
                                            </Link>
                                        </td>
                                        <td data-label="Provider" class="px-3 py-3 align-top">
                                            {{ service.providerRel ? service.providerRel.name : service.provider }}
                                        </td>
                                        <td data-label="Package" class="px-3 py-3 align-top">
                                            {{ service.package_name }}
                                        </td>
                                        <td data-label="Renewal Date" class="px-3 py-3 align-top">
                                            <div class="text-sm text-gray-900">
                                                {{ formatDate(service.renewal_date) }}
                                            </div>
                                            <div
                                                v-if="service.is_expired"
                                                class="mt-1 text-xs font-medium text-red-600"
                                            >
                                                Expired {{ Math.abs(service.days_until_expiry) }} days ago
                                            </div>
                                            <div
                                                v-else-if="isExpiringSoon(service.renewal_date)"
                                                class="mt-1 text-xs font-medium text-red-600"
                                            >
                                                Expires in {{ service.days_until_expiry }} days
                                            </div>
                                        </td>
                                        <td data-label="Status" class="px-3 py-3 align-top">
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
                                        <td data-label="Actions" class="px-3 py-3 text-right text-sm font-medium align-top">
                                            <div class="flex flex-wrap gap-x-3 gap-y-2 md:justify-end">
                                                <Link
                                                    v-if="service.is_expired || service.days_until_expiry <= 90"
                                                    :href="route('bills.create', { client_id: service.client_id, service_type: 'hosting', service_id: service.id })"
                                                    class="font-semibold text-green-600 hover:text-green-900"
                                                >
                                                    Generate Bill
                                                </Link>
                                                <Link
                                                    :href="route('hosting-services.edit', service.id)"
                                                    class="text-indigo-600 hover:text-indigo-900"
                                                >
                                                    Edit
                                                </Link>
                                                <button
                                                    @click="deleteHostingService(service.id)"
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
    hostingServices: Array,
    filters: Object,
    clients: Array,
    domains: Array,
    providers: Array,
});

const filterForm = ref({
    search: props.filters?.search || '',
    status: props.filters?.status || '',
    payment_status: props.filters?.payment_status || '',
    client_id: props.filters?.client_id || '',
    provider_id: props.filters?.provider_id || '',
});

let debounceTimer = null;
const applyFilters = () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        const params = {};
        for (const [key, value] of Object.entries(filterForm.value)) {
            if (value) params[key] = value;
        }
        router.get(route('hosting-services.index'), params, { preserveState: true, preserveScroll: true });
    }, 300);
};

const resetFilters = () => {
    filterForm.value = { search: '', status: '', payment_status: '', client_id: '', provider_id: '' };
    router.get(route('hosting-services.index'), {}, { preserveState: true, preserveScroll: true });
};

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
