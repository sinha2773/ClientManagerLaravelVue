<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Bills Management</h2>
                <PrimaryButton @click="() => $inertia.visit(route('bills.create'))">
                    Create New Bill
                </PrimaryButton>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Filters</h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <InputLabel for="search" value="Search" />
                                <TextInput
                                    id="search"
                                    v-model="filters.search"
                                    type="text"
                                    placeholder="Bill number, description, client..."
                                    class="mt-1 block w-full"
                                    @input="applyFilters"
                                />
                            </div>
                            <div>
                                <InputLabel for="payment_status" value="Payment Status" />
                                <select
                                    id="payment_status"
                                    v-model="filters.payment_status"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    @change="applyFilters"
                                >
                                    <option value="">All Payment Status</option>
                                    <option value="unpaid">Unpaid</option>
                                    <option value="partially_paid">Partially Paid</option>
                                    <option value="paid">Paid</option>
                                </select>
                            </div>
                            <div>
                                <InputLabel for="status" value="Status" />
                                <select
                                    id="status"
                                    v-model="filters.status"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    @change="applyFilters"
                                >
                                    <option value="">All Status</option>
                                    <option value="draft">Draft</option>
                                    <option value="sent">Sent</option>
                                    <option value="overdue">Overdue</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div>
                                <InputLabel for="service_type" value="Service Type" />
                                <select
                                    id="service_type"
                                    v-model="filters.service_type"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    @change="applyFilters"
                                >
                                    <option value="">All Services</option>
                                    <option value="domain">Domain</option>
                                    <option value="hosting">Hosting</option>
                                    <option value="ssl_certificate">SSL Certificate</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bills Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Bill Number
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Client
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Service
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Amount
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Payment Status
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Due Date
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="bill in bills.data" :key="bill.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ bill.bill_number }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ bill.client.name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                :class="getServiceTypeClass(bill.service_type)">
                                                {{ formatServiceType(bill.service_type) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ formatCurrency(bill.amount) }}
                                            <div v-if="bill.paid_amount > 0" class="text-xs text-gray-500">
                                                Paid: {{ formatCurrency(bill.paid_amount) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                :class="getPaymentStatusClass(bill.payment_status)">
                                                {{ formatPaymentStatus(bill.payment_status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                :class="getStatusClass(bill.status)">
                                                {{ formatStatus(bill.status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ formatDate(bill.due_date) }}
                                            <div v-if="isOverdue(bill)" class="text-xs text-red-600 font-medium">
                                                Overdue
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                            <button
                                                @click="() => $inertia.visit(route('bills.show', bill.id))"
                                                class="text-indigo-600 hover:text-indigo-900"
                                            >
                                                View
                                            </button>
                                            <button
                                                v-if="bill.status === 'draft'"
                                                @click="() => $inertia.visit(route('bills.edit', bill.id))"
                                                class="text-blue-600 hover:text-blue-900"
                                            >
                                                Edit
                                            </button>
                                            <button
                                                v-if="canApprove && bill.status === 'draft'"
                                                @click="approveBill(bill.id)"
                                                class="text-green-600 hover:text-green-900"
                                            >
                                                Approve
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="bills.data.length === 0">
                                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                            No bills found
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="bills.links" class="mt-6">
                            <nav class="flex items-center justify-between">
                                <div class="flex justify-between flex-1 sm:hidden">
                                    <a v-if="bills.prev_page_url" :href="bills.prev_page_url"
                                       class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                        Previous
                                    </a>
                                    <a v-if="bills.next_page_url" :href="bills.next_page_url"
                                       class="relative ml-3 inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                        Next
                                    </a>
                                </div>
                                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-sm text-gray-700">
                                            Showing {{ bills.from }} to {{ bills.to }} of {{ bills.total }} results
                                        </p>
                                    </div>
                                    <div>
                                        <span class="relative z-0 inline-flex shadow-sm rounded-md">
                                            <a v-for="link in bills.links" :key="link.label"
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
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import InputLabel from '@/Components/InputLabel.vue'
import { formatCurrency } from '@/utils/currency.js'

const props = defineProps({
    bills: Object,
    filters: Object,
    canApprove: Boolean,
})

const filters = ref({
    search: props.filters.search || '',
    payment_status: props.filters.payment_status || '',
    status: props.filters.status || '',
    service_type: props.filters.service_type || '',
})

const applyFilters = () => {
    router.get(route('bills.index'), filters.value, { preserveState: true })
}

const approveBill = (billId) => {
    if (confirm('Are you sure you want to approve this bill?')) {
        router.patch(route('bills.approve', billId), {}, {
            onSuccess: () => {
                // Handled by the backend redirect
            }
        })
    }
}

const formatServiceType = (type) => {
    const types = {
        domain: 'Domain',
        hosting: 'Hosting',
        ssl_certificate: 'SSL Certificate'
    }
    return types[type] || type
}

const formatPaymentStatus = (status) => {
    const statuses = {
        unpaid: 'Unpaid',
        partially_paid: 'Partially Paid',
        paid: 'Paid'
    }
    return statuses[status] || status
}

const formatStatus = (status) => {
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

const isOverdue = (bill) => {
    const today = new Date()
    const dueDate = new Date(bill.due_date)
    return dueDate < today && bill.payment_status !== 'paid'
}

const getServiceTypeClass = (type) => {
    const classes = {
        domain: 'bg-blue-100 text-blue-800',
        hosting: 'bg-green-100 text-green-800',
        ssl_certificate: 'bg-purple-100 text-purple-800'
    }
    return classes[type] || 'bg-gray-100 text-gray-800'
}

const getPaymentStatusClass = (status) => {
    const classes = {
        unpaid: 'bg-red-100 text-red-800',
        partially_paid: 'bg-yellow-100 text-yellow-800',
        paid: 'bg-green-100 text-green-800'
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}

const getStatusClass = (status) => {
    const classes = {
        draft: 'bg-gray-100 text-gray-800',
        sent: 'bg-blue-100 text-blue-800',
        overdue: 'bg-red-100 text-red-800',
        cancelled: 'bg-red-100 text-red-800'
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}
</script> 