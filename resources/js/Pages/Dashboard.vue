<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Bar, Line, Doughnut } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, LineElement, PointElement, ArcElement } from 'chart.js';
import { ref, onMounted, computed } from 'vue';
import { formatCurrency } from '@/utils/currency.js';

ChartJS.register(CategoryScale, LinearScale, BarElement, LineElement, PointElement, ArcElement, Title, Tooltip, Legend);

const props = defineProps({
    summary: Object,
    expiringDomains: Array,
    expiringSslCertificates: Array,
    dueHostingServices: Array,
    unpaidSummary: Object,
    monthlyRevenue: Object,
    billingReport: Object,
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

// Billing trend chart data
const billingTrendData = computed(() => {
    if (!props.billingReport?.monthly_trend) return null;
    
    return {
        labels: props.billingReport.monthly_trend.map(item => item.month),
        datasets: [
            {
                label: 'Billed Amount',
                backgroundColor: '#3B82F6',
                borderColor: '#3B82F6',
                data: props.billingReport.monthly_trend.map(item => item.billed),
                fill: false,
            },
            {
                label: 'Paid Amount',
                backgroundColor: '#10B981',
                borderColor: '#10B981',
                data: props.billingReport.monthly_trend.map(item => item.paid),
                fill: false,
            },
        ],
    };
});

// Payment status chart data
const paymentStatusData = computed(() => {
    if (!props.billingReport?.overview) return null;
    
    return {
        labels: ['Paid', 'Unpaid', 'Partially Paid'],
        datasets: [
            {
                data: [
                    props.billingReport.overview.paid_bills,
                    props.billingReport.overview.unpaid_bills,
                    props.billingReport.overview.partially_paid_bills,
                ],
                backgroundColor: ['#10B981', '#EF4444', '#F59E0B'],
            },
        ],
    };
});

const chartOptions2 = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom',
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

                <!-- Billing Report -->
                <div v-if="billingReport" class="mt-8 space-y-8">
                    <!-- Billing Overview -->
                    <div class="overflow-hidden rounded-lg bg-white p-6 shadow">
                        <h3 class="text-lg font-medium text-gray-900 mb-6">Billing Overview</h3>
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                            <div class="rounded-lg bg-blue-50 p-4">
                                <div class="text-sm font-medium text-blue-800">Total Billed</div>
                                <div class="mt-2 text-2xl font-bold text-blue-900">
                                    {{ formatCurrency(billingReport.overview.total_billed) }}
                                </div>
                            </div>
                            <div class="rounded-lg bg-green-50 p-4">
                                <div class="text-sm font-medium text-green-800">Total Paid</div>
                                <div class="mt-2 text-2xl font-bold text-green-900">
                                    {{ formatCurrency(billingReport.overview.total_paid) }}
                                </div>
                            </div>
                            <div class="rounded-lg bg-red-50 p-4">
                                <div class="text-sm font-medium text-red-800">Outstanding</div>
                                <div class="mt-2 text-2xl font-bold text-red-900">
                                    {{ formatCurrency(billingReport.overview.total_outstanding) }}
                                </div>
                            </div>
                            <div class="rounded-lg bg-purple-50 p-4">
                                <div class="text-sm font-medium text-purple-800">Collection Rate</div>
                                <div class="mt-2 text-2xl font-bold text-purple-900">
                                    {{ billingReport.overview.collection_rate.toFixed(1) }}%
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Section -->
                    <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                        <!-- Billing Trend Chart -->
                        <div class="overflow-hidden rounded-lg bg-white p-6 shadow">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">6-Month Billing Trend</h3>
                            <div class="h-64">
                                <Line v-if="billingTrendData" :data="billingTrendData" :options="chartOptions2" />
                            </div>
                        </div>

                        <!-- Payment Status Chart -->
                        <div class="overflow-hidden rounded-lg bg-white p-6 shadow">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Payment Status Distribution</h3>
                            <div class="h-64">
                                <Doughnut v-if="paymentStatusData" :data="paymentStatusData" :options="chartOptions2" />
                            </div>
                        </div>
                    </div>

                    <!-- Current vs Last Month -->
                    <div class="overflow-hidden rounded-lg bg-white p-6 shadow">
                        <h3 class="text-lg font-medium text-gray-900 mb-6">Monthly Comparison</h3>
                        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                            <div class="rounded-lg border border-gray-200 p-4">
                                <h4 class="text-base font-medium text-gray-900 mb-4">Current Month</h4>
                                <div class="space-y-3">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Bills Generated:</span>
                                        <span class="font-medium">{{ billingReport.current_month.total_bills }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Total Amount:</span>
                                        <span class="font-medium">{{ formatCurrency(billingReport.current_month.total_amount) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Paid Amount:</span>
                                        <span class="font-medium text-green-600">{{ formatCurrency(billingReport.current_month.total_paid) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="rounded-lg border border-gray-200 p-4">
                                <h4 class="text-base font-medium text-gray-900 mb-4">Last Month</h4>
                                <div class="space-y-3">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Bills Generated:</span>
                                        <span class="font-medium">{{ billingReport.last_month.total_bills }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Total Amount:</span>
                                        <span class="font-medium">{{ formatCurrency(billingReport.last_month.total_amount) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Paid Amount:</span>
                                        <span class="font-medium text-green-600">{{ formatCurrency(billingReport.last_month.total_paid) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Unpaid Bills & Overdue Bills -->
                    <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                        <!-- Recent Unpaid Bills -->
                        <div class="overflow-hidden rounded-lg bg-white p-6 shadow">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Recent Unpaid Bills</h3>
                                <Link :href="route('bills.index')" class="text-sm text-indigo-600 hover:text-indigo-900">
                                    View All
                                </Link>
                            </div>
                            <div class="divide-y divide-gray-200">
                                <div v-for="bill in billingReport.recent_unpaid" :key="bill.id" class="py-3">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ bill.bill_number }}</p>
                                            <p class="text-sm text-gray-500">{{ bill.client_name }}</p>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ formatCurrency(bill.remaining_amount) }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                Due: {{ new Date(bill.due_date).toLocaleDateString() }}
                                            </div>
                                            <span v-if="bill.is_overdue" class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800">
                                                Overdue
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Overdue Bills -->
                        <div class="overflow-hidden rounded-lg bg-white p-6 shadow">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                Overdue Bills
                                <span v-if="billingReport.overdue_bills.length > 0" class="ml-2 rounded-full bg-red-100 px-2 py-1 text-xs text-red-800">
                                    {{ billingReport.overdue_bills.length }}
                                </span>
                            </h3>
                            <div v-if="billingReport.overdue_bills.length === 0" class="text-center py-8 text-gray-500">
                                No overdue bills
                            </div>
                            <div v-else class="divide-y divide-gray-200">
                                <div v-for="bill in billingReport.overdue_bills" :key="bill.id" class="py-3">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ bill.bill_number }}</p>
                                            <p class="text-sm text-gray-500">{{ bill.client_name }}</p>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm font-medium text-red-600">
                                                {{ formatCurrency(bill.remaining_amount) }}
                                            </div>
                                            <div class="text-xs text-red-500">
                                                {{ bill.days_overdue }} days overdue
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
