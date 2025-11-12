<template>
    <Head title="Payroll Reports" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Payroll Reports
                </h2>
                <Link
                    :href="route('payroll.index')"
                    class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300"
                >
                    Back to Payroll
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="mb-4 text-lg font-medium text-gray-900">Filters</h3>
                        <form @submit.prevent="applyFilters" class="grid grid-cols-1 gap-4 md:grid-cols-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Month/Year</label>
                                <input
                                    type="month"
                                    v-model="filterForm.month_year"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Employee</label>
                                <select v-model="filterForm.employee_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">All Employees</option>
                                    <option v-for="employee in employees" :key="employee.id" :value="employee.id">
                                        {{ employee.name }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Source</label>
                                <select v-model="filterForm.salary_source" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">All Sources</option>
                                    <option value="CodeGaon">CodeGaon</option>
                                    <option value="MSBJBD">MSBJBD</option>
                                    <option value="SinhdBD">SinhdBD</option>
                                </select>
                            </div>
                            <div class="flex items-end">
                                <button
                                    type="submit"
                                    class="w-full rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500"
                                >
                                    Apply Filters
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 gap-6 md:grid-cols-5">
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h4 class="text-sm font-medium text-gray-500">Total Amount</h4>
                            <p class="mt-2 text-2xl font-bold text-gray-900">{{ formatCurrency(summary.total_amount) }}</p>
                        </div>
                    </div>
                    <div class="overflow-hidden bg-green-50 shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h4 class="text-sm font-medium text-green-700">Paid Amount</h4>
                            <p class="mt-2 text-2xl font-bold text-green-900">{{ formatCurrency(summary.paid_amount) }}</p>
                        </div>
                    </div>
                    <div class="overflow-hidden bg-red-50 shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h4 class="text-sm font-medium text-red-700">Due Amount</h4>
                            <p class="mt-2 text-2xl font-bold text-red-900">{{ formatCurrency(summary.due_amount) }}</p>
                        </div>
                    </div>
                    <div class="overflow-hidden bg-yellow-50 shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h4 class="text-sm font-medium text-yellow-700">Partial Amount</h4>
                            <p class="mt-2 text-2xl font-bold text-yellow-900">{{ formatCurrency(summary.partial_amount) }}</p>
                        </div>
                    </div>
                    <div class="overflow-hidden bg-purple-50 shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h4 class="text-sm font-medium text-purple-700">Advance Amount</h4>
                            <p class="mt-2 text-2xl font-bold text-purple-900">{{ formatCurrency(summary.advance_amount) }}</p>
                        </div>
                    </div>
                </div>

                <!-- By Source -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="mb-4 text-lg font-medium text-gray-900">By Salary Source</h3>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div v-for="(data, source) in bySource" :key="source" class="rounded-lg border border-gray-200 p-4">
                                <h4 class="text-sm font-medium text-gray-700">{{ source }}</h4>
                                <p class="mt-1 text-xl font-bold text-gray-900">{{ formatCurrency(data.total) }}</p>
                                <p class="text-sm text-gray-500">{{ data.count }} payments</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- By Month -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="mb-4 text-lg font-medium text-gray-900">Monthly Breakdown</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Month</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Payments</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Total Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <tr v-for="(data, month) in byMonth" :key="month">
                                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">{{ formatMonthYear(month) }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ data.count }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ formatCurrency(data.total) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Detailed Records -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="mb-4 text-lg font-medium text-gray-900">Detailed Records</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Employee</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Month</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Amount</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Source</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <tr v-for="salary in paySalaries" :key="salary.id">
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <div class="font-medium text-gray-900">{{ salary.employee.name }}</div>
                                            <div class="text-sm text-gray-500">{{ salary.employee.designation }}</div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ formatMonthYear(salary.month_year) }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ formatCurrency(salary.salary_amount) }}</td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">
                                                {{ salary.salary_source }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <div class="flex flex-wrap gap-1">
                                                <span v-if="salary.is_paid" class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">Paid</span>
                                                <span v-if="salary.is_partial" class="inline-flex items-center rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-800">Partial</span>
                                                <span v-if="salary.is_due" class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800">Due</span>
                                                <span v-if="salary.is_advance" class="inline-flex items-center rounded-full bg-purple-100 px-2 py-0.5 text-xs font-medium text-purple-800">Advance</span>
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
import { reactive } from 'vue';

const props = defineProps({
    paySalaries: Array,
    summary: Object,
    bySource: Object,
    byMonth: Object,
    employees: Array,
    filters: Object,
});

const filterForm = reactive({
    month_year: props.filters?.month_year || '',
    employee_id: props.filters?.employee_id || '',
    salary_source: props.filters?.salary_source || '',
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'BDT',
    }).format(amount || 0);
};

const formatMonthYear = (monthYear) => {
    const [year, month] = monthYear.split('-');
    return new Date(year, month - 1).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
    });
};

const applyFilters = () => {
    router.get(route('payroll.report'), filterForm, {
        preserveState: true,
    });
};
</script>
