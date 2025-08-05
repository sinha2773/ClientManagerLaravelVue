<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    User Details - {{ user.name }}
                </h2>
                <div class="flex space-x-2">
                    <SecondaryButton @click="() => $inertia.visit(route('user-management.index'))">
                        Back to Users
                    </SecondaryButton>
                    <PrimaryButton 
                        v-if="canEdit"
                        @click="() => $inertia.visit(route('user-management.edit', user.id))"
                    >
                        Edit User
                    </PrimaryButton>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- User Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-6">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0 h-16 w-16">
                                    <div class="h-16 w-16 rounded-full bg-gray-300 flex items-center justify-center">
                                        <span class="text-xl font-medium text-gray-700">
                                            {{ user.name.charAt(0).toUpperCase() }}
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900">{{ user.name }}</h3>
                                    <p class="text-sm text-gray-500">{{ user.email }}</p>
                                    <div class="mt-2 flex items-center space-x-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                            :class="getUserTypeClass(user.user_type)">
                                            {{ formatUserType(user.user_type) }}
                                        </span>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                            :class="getStatusClass(user.is_active)">
                                            {{ user.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-500">User ID</p>
                                <p class="text-lg font-medium text-gray-900">#{{ user.id }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Account Created</label>
                                <p class="mt-1 text-sm text-gray-900">{{ formatDateTime(user.created_at) }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Last Updated</label>
                                <p class="mt-1 text-sm text-gray-900">{{ formatDateTime(user.updated_at) }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email Verified</label>
                                <p class="mt-1 text-sm text-gray-900">
                                    {{ user.email_verified_at ? formatDateTime(user.email_verified_at) : 'Not verified' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Activity Summary -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Activity Summary</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Bills Created -->
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <h4 class="text-lg font-medium text-blue-900">Bills Created</h4>
                                        <p class="text-3xl font-bold text-blue-600">{{ user.created_bills.length }}</p>
                                        <p class="text-sm text-blue-700">Total bills created by this user</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Bills Approved -->
                            <div class="bg-green-50 p-4 rounded-lg">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <h4 class="text-lg font-medium text-green-900">Bills Approved</h4>
                                        <p class="text-3xl font-bold text-green-600">{{ user.approved_bills.length }}</p>
                                        <p class="text-sm text-green-700">Total bills approved by this user</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Bills Created -->
                <div v-if="user.created_bills.length > 0" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Bills Created</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Bill Number
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Amount
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Created
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="bill in user.created_bills.slice(0, 5)" :key="bill.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ bill.bill_number }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            ${{ Number(bill.amount).toFixed(2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                :class="getBillStatusClass(bill.status)">
                                                {{ formatBillStatus(bill.status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ formatDate(bill.created_at) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-if="user.created_bills.length > 5" class="mt-4 text-center">
                            <button 
                                @click="() => $inertia.visit(route('bills.index', { created_by: user.id }))"
                                class="text-indigo-600 hover:text-indigo-900 text-sm font-medium"
                            >
                                View all {{ user.created_bills.length }} bills →
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Recent Bills Approved -->
                <div v-if="user.approved_bills.length > 0" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Bills Approved</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Bill Number
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Amount
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Approved
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="bill in user.approved_bills.slice(0, 5)" :key="bill.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ bill.bill_number }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            ${{ Number(bill.amount).toFixed(2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ formatDateTime(bill.approved_at) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-if="user.approved_bills.length > 5" class="mt-4 text-center">
                            <button 
                                @click="() => $inertia.visit(route('bills.index', { approved_by: user.id }))"
                                class="text-indigo-600 hover:text-indigo-900 text-sm font-medium"
                            >
                                View all {{ user.approved_bills.length }} approved bills →
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'

const props = defineProps({
    user: Object,
    canEdit: Boolean,
})

const formatUserType = (type) => {
    const types = {
        account_manager: 'Account Manager',
        approver: 'Approver'
    }
    return types[type] || type
}

const formatBillStatus = (status) => {
    const statuses = {
        draft: 'Draft',
        sent: 'Sent',
        overdue: 'Overdue',
        cancelled: 'Cancelled'
    }
    return statuses[status] || status
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString()
}

const formatDateTime = (datetime) => {
    return new Date(datetime).toLocaleString()
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

const getBillStatusClass = (status) => {
    const classes = {
        draft: 'bg-gray-100 text-gray-800',
        sent: 'bg-blue-100 text-blue-800',
        overdue: 'bg-red-100 text-red-800',
        cancelled: 'bg-red-100 text-red-800'
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}
</script> 