<template>
    <Head title="Domains" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
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
            <div class="page-container">
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-4">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6">
                            <div>
                                <input
                                    type="text"
                                    v-model="filterForm.search"
                                    placeholder="Search domain, registrar..."
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
                                    <option value="partial">Partial</option>
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
                                    v-model="filterForm.registrar"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    @change="applyFilters"
                                >
                                    <option value="">All Registrars</option>
                                    <option v-for="registrar in registrars" :key="registrar" :value="registrar">
                                        {{ registrar }}
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
                                    <col class="w-[22%]" />
                                    <col class="w-[20%]" />
                                    <col class="w-[16%]" />
                                    <col class="w-[12%]" />
                                    <col class="w-[12%]" />
                                    <col class="w-[18%]" />
                                </colgroup>
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Domain
                                        </th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Client
                                        </th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Expiry
                                        </th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Services
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
                                    <tr v-for="domain in domains" :key="domain.id">
                                        <td data-label="Domain" class="px-3 py-3 align-top">
                                            <div class="font-medium text-gray-900">{{ domain.name }}</div>
                                            <div class="text-sm text-gray-500">{{ domain.registrar }}</div>
                                        </td>
                                        <td data-label="Client" class="px-3 py-3 align-top">
                                            <Link
                                                :href="route('clients.show', domain.client.id)"
                                                class="text-indigo-600 hover:text-indigo-900"
                                            >
                                                {{ domain.client.name }}
                                            </Link>
                                        </td>
                                        <td data-label="Expiry" class="px-3 py-3 align-top">
                                            <div class="text-sm text-gray-900">
                                                {{ formatDate(domain.expiry_date) }}
                                            </div>
                                            <div
                                                v-if="domain.is_expired"
                                                class="mt-1 text-xs font-medium text-red-600"
                                            >
                                                Expired {{ Math.abs(domain.days_until_expiry) }} days ago
                                            </div>
                                            <div
                                                v-else-if="domain.is_expiring_soon"
                                                class="mt-1 text-xs font-medium text-red-600"
                                            >
                                                Expires in {{ domain.days_until_expiry }} days
                                            </div>
                                        </td>
                                        <td data-label="Services" class="px-3 py-3 align-top">
                                            <div class="flex flex-wrap gap-2">
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
                                        <td data-label="Status" class="px-3 py-3 align-top">
                                            <div class="flex flex-col items-start gap-1">
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
                                        <td data-label="Actions" class="px-3 py-3 text-right text-sm font-medium align-top">
                                            <div class="flex flex-wrap gap-x-3 gap-y-2 md:justify-end">
                                                <Link
                                                    v-if="domain.is_expired || domain.days_until_expiry <= 90"
                                                    :href="route('bills.create', { client_id: domain.client_id, service_type: 'domain', service_id: domain.id })"
                                                    class="font-semibold text-green-600 hover:text-green-900"
                                                >
                                                    Generate Bill
                                                </Link>
                                                <Link
                                                    :href="route('domains.edit', domain.id)"
                                                    class="text-indigo-600 hover:text-indigo-900"
                                                >
                                                    Edit
                                                </Link>
                                                <button
                                                    @click="deleteDomain(domain.id)"
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
    domains: Array,
    filters: Object,
    clients: Array,
    registrars: Array,
});

const filterForm = ref({
    search: props.filters?.search || '',
    status: props.filters?.status || '',
    payment_status: props.filters?.payment_status || '',
    client_id: props.filters?.client_id || '',
    registrar: props.filters?.registrar || '',
});

let debounceTimer = null;
const applyFilters = () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        const params = {};
        for (const [key, value] of Object.entries(filterForm.value)) {
            if (value) params[key] = value;
        }
        router.get(route('domains.index'), params, { preserveState: true, preserveScroll: true });
    }, 300);
};

const resetFilters = () => {
    filterForm.value = { search: '', status: '', payment_status: '', client_id: '', registrar: '' };
    router.get(route('domains.index'), {}, { preserveState: true, preserveScroll: true });
};

const deleteDomain = (id) => {
    if (confirm('Are you sure you want to delete this domain?')) {
        router.delete(route('domains.destroy', id));
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString();
};
</script>
