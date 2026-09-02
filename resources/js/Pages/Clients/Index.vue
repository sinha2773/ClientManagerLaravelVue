<template>
    <Head title="Clients" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Clients
                </h2>
                <Link
                    :href="route('clients.create')"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                >
                    Add New Client
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-4">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                            <div>
                                <input
                                    type="text"
                                    v-model="filterForm.search"
                                    placeholder="Search name, email, phone..."
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
                                    v-model="filterForm.client_type"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    @change="applyFilters"
                                >
                                    <option value="">All Types</option>
                                    <option v-for="type in CLIENT_TYPES" :key="type.value" :value="type.value">
                                        {{ type.label }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <select
                                    v-model="filterForm.client_category_id"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    @change="applyFilters"
                                >
                                    <option value="">All Categories</option>
                                    <option v-for="category in categories" :key="category.id" :value="category.id">
                                        {{ category.name }}
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
                    <div class="p-6 text-gray-900">
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[60rem] table-fixed divide-y divide-gray-200">
                                <colgroup>
                                    <col class="w-56" />
                                    <col class="w-56" />
                                    <col class="w-60" />
                                    <col class="w-28" />
                                    <col class="w-40" />
                                </colgroup>
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Name
                                        </th>
                                        <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Contact
                                        </th>
                                        <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Services
                                        </th>
                                        <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Status
                                        </th>
                                        <th class="px-3 py-2 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <tr v-for="client in clients" :key="client.id">
                                         <td class="px-3 py-2 align-top">
                                             <div class="break-words font-medium leading-snug text-gray-900">{{ client.name }}</div>
                                             <div v-if="client.short_name" class="mt-1 break-words text-sm text-gray-400">{{ client.short_name }}</div>
                                             <div class="break-words text-sm text-gray-500">{{ client.company }}</div>
                                             <div v-if="client.category" class="text-xs text-indigo-600 mt-1">
                                                  {{ client.category.name }}
                                              </div>
                                              <div v-if="client.client_type" class="text-xs text-gray-400 mt-1">
                                                  {{ formatClientType(client.client_type) }}
                                              </div>
                                         </td>
                                        <td class="px-3 py-2 align-top">
                                            <div class="break-all text-sm text-gray-900">{{ client.email }}</div>
                                            <div class="text-sm text-gray-500">{{ client.phone }}</div>
                                        </td>
                                        <td class="px-3 py-2 align-top">
                                            <div class="flex flex-wrap gap-1">
                                                <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">
                                                    {{ client.domains_count }} Domains
                                                </span>
                                                <span class="inline-flex items-center rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-medium text-purple-800">
                                                    {{ client.hosting_count }} Hosting
                                                </span>
                                                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                                    {{ client.ssl_count }} SSL
                                                </span>
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-2 align-top">
                                            <span
                                                :class="{
                                                    'bg-green-100 text-green-800': client.status === 'active',
                                                    'bg-red-100 text-red-800': client.status === 'inactive'
                                                }"
                                                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                            >
                                                {{ client.status }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2 align-top text-right text-sm font-medium">
                                            <div class="flex flex-wrap justify-end gap-x-3 gap-y-1">
                                                <Link
                                                    :href="route('clients.show', client.id)"
                                                    class="text-blue-600 hover:text-blue-900"
                                                >
                                                    View
                                                </Link>
                                                <Link
                                                    :href="route('clients.edit', client.id)"
                                                    class="text-indigo-600 hover:text-indigo-900"
                                                >
                                                    Edit
                                                </Link>
                                                <button
                                                    @click="deleteClient(client.id)"
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
import { CLIENT_TYPES, formatClientType } from '@/constants/clientTypes';
import { ref } from 'vue';

const props = defineProps({
    clients: Array,
    filters: Object,
    categories: Array,
});

const filterForm = ref({
    search: props.filters?.search || '',
    status: props.filters?.status || '',
    client_type: props.filters?.client_type || '',
    client_category_id: props.filters?.client_category_id || '',
});

let debounceTimer = null;
const applyFilters = () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        const params = {};
        for (const [key, value] of Object.entries(filterForm.value)) {
            if (value) params[key] = value;
        }
        router.get(route('clients.index'), params, { preserveState: true, preserveScroll: true });
    }, 300);
};

const resetFilters = () => {
    filterForm.value = { search: '', status: '', client_type: '', client_category_id: '' };
    router.get(route('clients.index'), {}, { preserveState: true, preserveScroll: true });
};

const deleteClient = (id) => {
    if (confirm('Are you sure you want to delete this client?')) {
        router.delete(route('clients.destroy', id));
    }
};
</script> 
