<template>
    <Head title="Edit Employee" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Edit Employee
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit">
                            <div class="space-y-6">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                                    <input
                                        type="text"
                                        id="name"
                                        v-model="form.name"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    />
                                    <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</div>
                                </div>

                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                    <input
                                        type="email"
                                        id="email"
                                        v-model="form.email"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    />
                                    <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</div>
                                </div>

                                <div>
                                    <label for="designation" class="block text-sm font-medium text-gray-700">Designation</label>
                                    <input
                                        type="text"
                                        id="designation"
                                        v-model="form.designation"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    />
                                    <div v-if="form.errors.designation" class="mt-1 text-sm text-red-600">{{ form.errors.designation }}</div>
                                </div>

                                <div>
                                    <label for="salary" class="block text-sm font-medium text-gray-700">Salary</label>
                                    <input
                                        type="number"
                                        id="salary"
                                        v-model="form.salary"
                                        step="0.01"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    />
                                    <div v-if="form.errors.salary" class="mt-1 text-sm text-red-600">{{ form.errors.salary }}</div>
                                </div>

                                <div>
                                    <label for="join_date" class="block text-sm font-medium text-gray-700">Join Date</label>
                                    <input
                                        type="date"
                                        id="join_date"
                                        v-model="form.join_date"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    />
                                    <div v-if="form.errors.join_date" class="mt-1 text-sm text-red-600">{{ form.errors.join_date }}</div>
                                </div>

                                <div>
                                    <label for="end_date" class="block text-sm font-medium text-gray-700">End Date (Optional)</label>
                                    <input
                                        type="date"
                                        id="end_date"
                                        v-model="form.end_date"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    <div v-if="form.errors.end_date" class="mt-1 text-sm text-red-600">{{ form.errors.end_date }}</div>
                                </div>

                                <div class="flex items-center justify-end space-x-4">
                                    <Link
                                        :href="route('employees.index')"
                                        class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300"
                                    >
                                        Cancel
                                    </Link>
                                    <button
                                        type="submit"
                                        :disabled="form.processing"
                                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 disabled:opacity-50"
                                    >
                                        Update Employee
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    employee: Object,
});

const form = useForm({
    name: props.employee.name,
    email: props.employee.email,
    designation: props.employee.designation,
    salary: props.employee.salary,
    join_date: props.employee.join_date,
    end_date: props.employee.end_date,
});

const submit = () => {
    form.put(route('employees.update', props.employee.id));
};
</script>
