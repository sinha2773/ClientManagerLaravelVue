<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js';
import { ref, onMounted } from 'vue';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

const props = defineProps({
    summary: Object,
    expiringDomains: Array,
    expiringSslCertificates: Array,
    dueHostingServices: Array,
    unpaidSummary: Object,
    monthlyRevenue: Object,
});

const chartData = {
    labels: ['Domains', 'SSL Certificates', 'Hosting'],
    datasets: [
        {
            label: 'Monthly Revenue',
            backgroundColor: ['#3B82F6', '#10B981', '#8B5CF6'],
            data: [
                props.monthlyRevenue.domains,
                props.monthlyRevenue.ssl,
                props.monthlyRevenue.hosting,
            ],
        },
    ],
};

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'top',
        },
        title: {
            display: true,
            text: 'Monthly Revenue',
        },
    },
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="overflow-hidden rounded-lg bg-white p-6 shadow">
                        <div class="text-sm font-medium text-gray-500">Total Clients</div>
                        <div class="mt-2 text-3xl font-bold text-gray-900">{{ summary.total_clients }}</div>
                    </div>
                    <div class="overflow-hidden rounded-lg bg-white p-6 shadow">
                        <div class="text-sm font-medium text-gray-500">Total Domains</div>
                        <div class="mt-2 text-3xl font-bold text-gray-900">{{ summary.total_domains }}</div>
                    </div>
                    <div class="overflow-hidden rounded-lg bg-white p-6 shadow">
                        <div class="text-sm font-medium text-gray-500">Total SSL Certificates</div>
                        <div class="mt-2 text-3xl font-bold text-gray-900">{{ summary.total_ssl_certificates }}</div>
                    </div>
                    <div class="overflow-hidden rounded-lg bg-white p-6 shadow">
                        <div class="text-sm font-medium text-gray-500">Total Hosting Services</div>
                        <div class="mt-2 text-3xl font-bold text-gray-900">{{ summary.total_hosting_services }}</div>
                    </div>
                </div>

                <!-- Revenue Chart -->
                <div class="mt-8 overflow-hidden rounded-lg bg-white p-6 shadow">
                    <h3 class="text-lg font-medium text-gray-900">Revenue Overview</h3>
                    <div class="mt-4 h-64">
                        <Bar :data="chartData" :options="chartOptions" />
                    </div>
                </div>

                <!-- Expiring Items -->
                <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <!-- Expiring Domains -->
                    <div class="overflow-hidden rounded-lg bg-white p-6 shadow">
                        <h3 class="text-lg font-medium text-gray-900">
                            Domains Expiring Soon
                            <span class="ml-2 rounded-full bg-yellow-100 px-2 py-1 text-xs text-yellow-800">
                                {{ summary.expiring_domains_count }}
                            </span>
                        </h3>
                        <div class="mt-4 divide-y divide-gray-200">
                            <div v-for="domain in expiringDomains" :key="domain.id" class="py-3">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ domain.name }}</p>
                                        <p class="text-sm text-gray-500">{{ domain.client.name }}</p>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        Expires: {{ new Date(domain.expiry_date).toLocaleDateString() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Expiring SSL Certificates -->
                    <div class="overflow-hidden rounded-lg bg-white p-6 shadow">
                        <h3 class="text-lg font-medium text-gray-900">
                            SSL Certificates Expiring Soon
                            <span class="ml-2 rounded-full bg-yellow-100 px-2 py-1 text-xs text-yellow-800">
                                {{ summary.expiring_ssl_count }}
                            </span>
                        </h3>
                        <div class="mt-4 divide-y divide-gray-200">
                            <div v-for="ssl in expiringSslCertificates" :key="ssl.id" class="py-3">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ ssl.domain.name }}</p>
                                        <p class="text-sm text-gray-500">{{ ssl.client.name }}</p>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        Expires: {{ new Date(ssl.expiry_date).toLocaleDateString() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Due Hosting Services -->
                    <div class="overflow-hidden rounded-lg bg-white p-6 shadow">
                        <h3 class="text-lg font-medium text-gray-900">
                            Hosting Services Due Soon
                            <span class="ml-2 rounded-full bg-yellow-100 px-2 py-1 text-xs text-yellow-800">
                                {{ summary.due_hosting_count }}
                            </span>
                        </h3>
                        <div class="mt-4 divide-y divide-gray-200">
                            <div v-for="hosting in dueHostingServices" :key="hosting.id" class="py-3">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ hosting.domain.name }}</p>
                                        <p class="text-sm text-gray-500">{{ hosting.client.name }}</p>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        Due: {{ new Date(hosting.renewal_date).toLocaleDateString() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Unpaid Summary -->
                <div class="mt-8 overflow-hidden rounded-lg bg-white p-6 shadow">
                    <h3 class="text-lg font-medium text-gray-900">Unpaid Services</h3>
                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div class="rounded-lg bg-red-50 p-4">
                            <div class="text-sm font-medium text-red-800">Unpaid Domains</div>
                            <div class="mt-2 text-2xl font-bold text-red-900">{{ unpaidSummary.domains }}</div>
                        </div>
                        <div class="rounded-lg bg-red-50 p-4">
                            <div class="text-sm font-medium text-red-800">Unpaid SSL Certificates</div>
                            <div class="mt-2 text-2xl font-bold text-red-900">{{ unpaidSummary.ssl }}</div>
                        </div>
                        <div class="rounded-lg bg-red-50 p-4">
                            <div class="text-sm font-medium text-red-800">Unpaid Hosting Services</div>
                            <div class="mt-2 text-2xl font-bold text-red-900">{{ unpaidSummary.hosting }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
