<template>
    <Head title="Payroll Management" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Payroll Management
                </h2>
                <div class="flex space-x-3">
                    <Link
                        :href="route('payroll.report')"
                        class="rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500"
                    >
                        View Reports
                    </Link>
                    <button
                        @click="showModal = true"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                    >
                        Record Salary Payment
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Employee
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Month/Year
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Amount
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Source
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <tr v-for="salary in paySalaries" :key="salary.id">
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <div class="font-medium text-gray-900">{{ salary.employee.name }}</div>
                                            <div class="text-sm text-gray-500">{{ salary.employee.designation }}</div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <div class="text-sm text-gray-900">{{ formatMonthYear(salary.month_year) }}</div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <div class="text-sm text-gray-900">{{ formatCurrency(salary.salary_amount) }}</div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">
                                                {{ salary.salary_source }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <div class="flex flex-wrap gap-1">
                                                <span v-if="salary.is_paid" class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">
                                                    Paid
                                                </span>
                                                <span v-if="salary.is_partial" class="inline-flex items-center rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-800">
                                                    Partial
                                                </span>
                                                <span v-if="salary.is_due" class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800">
                                                    Due
                                                </span>
                                                <span v-if="salary.is_advance" class="inline-flex items-center rounded-full bg-purple-100 px-2 py-0.5 text-xs font-medium text-purple-800">
                                                    Advance
                                                </span>
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                            <button
                                                @click="editSalary(salary)"
                                                class="text-indigo-600 hover:text-indigo-900"
                                            >
                                                Edit
                                            </button>
                                            <button
                                                @click="deleteSalary(salary.id)"
                                                class="ml-4 text-red-600 hover:text-red-900"
                                            >
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeModal"></div>
                
                <div class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
                    <form @submit.prevent="submitSalary">
                        <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            <h3 class="mb-4 text-lg font-medium leading-6 text-gray-900">
                                {{ editMode ? 'Edit Salary Payment' : 'Record Salary Payment' }}
                            </h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Employee</label>
                                    <select v-model="form.employee_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                        <option value="">Select Employee</option>
                                        <option v-for="employee in employees" :key="employee.id" :value="employee.id">
                                            {{ employee.name }} - {{ employee.designation }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.employee_id" class="mt-1 text-sm text-red-600">{{ form.errors.employee_id }}</div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Salary Amount</label>
                                    <input type="number" v-model="form.salary_amount" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
                                    <div v-if="form.errors.salary_amount" class="mt-1 text-sm text-red-600">{{ form.errors.salary_amount }}</div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Month/Year</label>
                                    <input type="month" v-model="form.month_year" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
                                    <div v-if="form.errors.month_year" class="mt-1 text-sm text-red-600">{{ form.errors.month_year }}</div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Salary Source</label>
                                    <select v-model="form.salary_source" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                        <option value="">Select Source</option>
                                        <option value="CodeGaon">CodeGaon</option>
                                        <option value="MSBJBD">MSBJBD</option>
                                        <option value="SinhdBD">SinhdBD</option>
                                    </select>
                                    <div v-if="form.errors.salary_source" class="mt-1 text-sm text-red-600">{{ form.errors.salary_source }}</div>
                                </div>

                                <div class="flex flex-wrap gap-4">
                                    <label class="flex items-center">
                                        <input type="checkbox" v-model="form.is_paid" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                                        <span class="ml-2 text-sm text-gray-700">Paid</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" v-model="form.is_partial" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                                        <span class="ml-2 text-sm text-gray-700">Partial</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" v-model="form.is_due" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                                        <span class="ml-2 text-sm text-gray-700">Due</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" v-model="form.is_advance" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                                        <span class="ml-2 text-sm text-gray-700">Advance</span>
                                    </label>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Notes</label>
                                    <textarea v-model="form.notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                            <button type="submit" :disabled="form.processing" class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 sm:ml-3 sm:w-auto sm:text-sm">
                                {{ editMode ? 'Update' : 'Save' }}
                            </button>
                            <button type="button" @click="closeModal" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:ml-3 sm:mt-0 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    paySalaries: Array,
    employees: Array,
});

const showModal = ref(false);
const editMode = ref(false);
const editingId = ref(null);

const form = useForm({
    employee_id: '',
    salary_amount: '',
    month_year: '',
    salary_source: '',
    is_paid: false,
    is_partial: false,
    is_due: false,
    is_advance: false,
    notes: '',
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'BDT',
    }).format(amount);
};

const formatMonthYear = (monthYear) => {
    const [year, month] = monthYear.split('-');
    return new Date(year, month - 1).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
    });
};

const closeModal = () => {
    showModal.value = false;
    editMode.value = false;
    editingId.value = null;
    form.reset();
};

const editSalary = (salary) => {
    editMode.value = true;
    editingId.value = salary.id;
    form.employee_id = salary.employee_id;
    form.salary_amount = salary.salary_amount;
    form.month_year = salary.month_year;
    form.salary_source = salary.salary_source;
    form.is_paid = salary.is_paid;
    form.is_partial = salary.is_partial;
    form.is_due = salary.is_due;
    form.is_advance = salary.is_advance;
    form.notes = salary.notes;
    showModal.value = true;
};

const submitSalary = () => {
    if (editMode.value) {
        form.put(route('payroll.update', editingId.value), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('payroll.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteSalary = (id) => {
    if (confirm('Are you sure you want to delete this salary record?')) {
        router.delete(route('payroll.destroy', id));
    }
};
</script>
