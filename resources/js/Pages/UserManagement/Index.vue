<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">User Management</h2>
                <PrimaryButton v-if="canCreate" @click="() => $inertia.visit(route('user-management.create'))">
                    Create New User
                </PrimaryButton>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Filters</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <InputLabel for="search" value="Search" />
                                <TextInput
                                    id="search"
                                    v-model="filters.search"
                                    type="text"
                                    placeholder="Name or email..."
                                    class="mt-1 block w-full"
                                    @input="applyFilters"
                                />
                            </div>
                            <div>
                                <InputLabel for="user_type" value="User Type" />
                                <select
                                    id="user_type"
                                    v-model="filters.user_type"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    @change="applyFilters"
                                >
                                    <option value="">All User Types</option>
                                    <option value="account_manager">Account Manager</option>
                                    <option value="approver">Approver</option>
                                </select>
                            </div>
                            <div>
                                <InputLabel for="is_active" value="Status" />
                                <select
                                    id="is_active"
                                    v-model="filters.is_active"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    @change="applyFilters"
                                >
                                    <option value="">All Status</option>
                                    <option value="true">Active</option>
                                    <option value="false">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            User
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Email
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            User Type
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Created
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                        <span class="text-sm font-medium text-gray-700">
                                                            {{ user.name.charAt(0).toUpperCase() }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                                                    <div class="text-sm text-gray-500">ID: {{ user.id }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ user.email }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                :class="getUserTypeClass(user.user_type)">
                                                {{ formatUserType(user.user_type) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                :class="getStatusClass(user.is_active)">
                                                {{ user.is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ formatDate(user.created_at) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                            <button
                                                @click="() => $inertia.visit(route('user-management.show', user.id))"
                                                class="text-indigo-600 hover:text-indigo-900"
                                            >
                                                View
                                            </button>
                                            <button
                                                v-if="canCreate"
                                                @click="() => $inertia.visit(route('user-management.edit', user.id))"
                                                class="text-blue-600 hover:text-blue-900"
                                            >
                                                Edit
                                            </button>
                                            <button
                                                v-if="canCreate && user.id !== $page.props.auth.user.id"
                                                @click="toggleUserStatus(user)"
                                                :class="user.is_active ? 'text-orange-600 hover:text-orange-900' : 'text-green-600 hover:text-green-900'"
                                            >
                                                {{ user.is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                            <button
                                                v-if="canCreate && user.id !== $page.props.auth.user.id"
                                                @click="deleteUser(user)"
                                                class="text-red-600 hover:text-red-900"
                                            >
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="users.data.length === 0">
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                            No users found
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="users.links" class="mt-6">
                            <nav class="flex items-center justify-between">
                                <div class="flex justify-between flex-1 sm:hidden">
                                    <a v-if="users.prev_page_url" :href="users.prev_page_url"
                                       class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                        Previous
                                    </a>
                                    <a v-if="users.next_page_url" :href="users.next_page_url"
                                       class="relative ml-3 inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                        Next
                                    </a>
                                </div>
                                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-sm text-gray-700">
                                            Showing {{ users.from }} to {{ users.to }} of {{ users.total }} results
                                        </p>
                                    </div>
                                    <div>
                                        <span class="relative z-0 inline-flex shadow-sm rounded-md">
                                            <a v-for="link in users.links" :key="link.label"
                                               :href="link.url"
                                               v-html="link.label"
                                               :class="[
                                                   'relative inline-flex items-center px-4 py-2 text-sm font-medium border',
                                                   link.active
                                                       ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                                                       : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                                                   !link.url ? 'cursor-not-allowed opacity-50' : 'hover:bg-gray-50'
                                               ]"
                                            />
                                        </span>
                                    </div>
                                </div>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import InputLabel from '@/Components/InputLabel.vue'

const props = defineProps({
    users: Object,
    filters: Object,
    canCreate: Boolean,
})

const filters = ref({
    search: props.filters.search || '',
    user_type: props.filters.user_type || '',
    is_active: props.filters.is_active || '',
})

const applyFilters = () => {
    router.get(route('user-management.index'), filters.value, { preserveState: true })
}

const toggleUserStatus = (user) => {
    const action = user.is_active ? 'deactivate' : 'activate'
    if (confirm(`Are you sure you want to ${action} this user?`)) {
        router.patch(route('user-management.toggle-status', user.id), {}, {
            onSuccess: () => {
                // Handled by backend redirect
            }
        })
    }
}

const deleteUser = (user) => {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        router.delete(route('user-management.destroy', user.id), {
            onSuccess: () => {
                // Handled by backend redirect
            }
        })
    }
}

const formatUserType = (type) => {
    const types = {
        account_manager: 'Account Manager',
        approver: 'Approver'
    }
    return types[type] || type
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString()
}

const getUserTypeClass = (type) => {
    const classes = {
        account_manager: 'bg-blue-100 text-blue-800',
        approver: 'bg-purple-100 text-purple-800'
    }
    return classes[type] || 'bg-gray-100 text-gray-800'
}

const getStatusClass = (isActive) => {
    return isActive ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
}
</script> 