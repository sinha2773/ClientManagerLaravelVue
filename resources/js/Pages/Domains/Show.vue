<template>
    <Head title="View Domain" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Domain: {{ domain.name }}
                </h2>
                <div class="flex space-x-4">
                    <Link
                        :href="route('domains.edit', domain.id)"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Edit Domain
                    </Link>
                    <button
                        @click="deleteDomain"
                        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                    >
                        Delete Domain
                    </button>
                    <Link
                        :href="route('domains.index')"
                        class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                    >
                        Back to Domains
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Domain Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Domain Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Client</p>
                                <p class="mt-1">
                                    <Link :href="route('clients.show', domain.client.id)" class="text-indigo-600 hover:text-indigo-900">
                                        {{ domain.client.name }}
                                    </Link>
                                </p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Registration Date</p>
                                <p class="mt-1">{{ formatDate(domain.registration_date) }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Expiry Date</p>
                                <p class="mt-1" :class="{'text-red-600': isExpired(), 'text-yellow-600': isExpiringSoon()}">
                                    {{ formatDate(domain.expiry_date) }}
                                    <span v-if="isExpired()" class="text-red-600">(Expired)</span>
                                    <span v-else-if="isExpiringSoon()" class="text-yellow-600">(Expiring Soon)</span>
                                </p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Auto Renew</p>
                                <p class="mt-1">{{ domain.auto_renew ? 'Yes' : 'No' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Financial Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Financial Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Cost</p>
                                <p class="mt-1">${{ domain.cost }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Price</p>
                                <p class="mt-1">${{ domain.price }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Payment Status</p>
                                <p class="mt-1">
                                    <span :class="{
                                        'px-2 py-1 text-sm rounded-full': true,
                                        'bg-green-100 text-green-800': domain.payment_status === 'paid',
                                        'bg-red-100 text-red-800': domain.payment_status === 'unpaid',
                                        'bg-yellow-100 text-yellow-800': domain.payment_status === 'partial'
                                    }">
                                        {{ domain.payment_status.charAt(0).toUpperCase() + domain.payment_status.slice(1) }}
                                    </span>
                                </p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Status</p>
                                <p class="mt-1">
                                    <span :class="{
                                        'px-2 py-1 text-sm rounded-full': true,
                                        'bg-green-100 text-green-800': domain.status === 'active',
                                        'bg-red-100 text-red-800': domain.status === 'expired',
                                        'bg-yellow-100 text-yellow-800': domain.status === 'pending'
                                    }">
                                        {{ domain.status.charAt(0).toUpperCase() + domain.status.slice(1) }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Approval Status -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Payment Approval Status</h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Level 1 Approval</p>
                                    <p class="mt-1">
                                        <span :class="{
                                            'px-2 py-1 text-sm rounded-full': true,
                                            'bg-green-100 text-green-800': domain.payment_approved_level1,
                                            'bg-red-100 text-red-800': !domain.payment_approved_level1
                                        }">
                                            {{ domain.payment_approved_level1 ? 'Approved' : 'Pending' }}
                                        </span>
                                    </p>
                                </div>
                                <button
                                    v-if="!domain.payment_approved_level1"
                                    @click="approvePaymentLevel1"
                                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                >
                                    Approve Level 1
                                </button>
                            </div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Level 2 Approval</p>
                                    <p class="mt-1">
                                        <span :class="{
                                            'px-2 py-1 text-sm rounded-full': true,
                                            'bg-green-100 text-green-800': domain.payment_approved_level2,
                                            'bg-red-100 text-red-800': !domain.payment_approved_level2
                                        }">
                                            {{ domain.payment_approved_level2 ? 'Approved' : 'Pending' }}
                                        </span>
                                    </p>
                                </div>
                                <button
                                    v-if="!domain.payment_approved_level2 && domain.payment_approved_level1"
                                    @click="approvePaymentLevel2"
                                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                >
                                    Approve Level 2
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Notes</h3>
                        <p class="whitespace-pre-wrap">{{ domain.notes || 'No notes available.' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    domain: {
        type: Object,
        required: true
    }
});

function formatDate(date) {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function isExpired() {
    return new Date(props.domain.expiry_date) < new Date();
}

function isExpiringSoon() {
    const expiryDate = new Date(props.domain.expiry_date);
    const thirtyDaysFromNow = new Date();
    thirtyDaysFromNow.setDate(thirtyDaysFromNow.getDate() + 30);
    return expiryDate > new Date() && expiryDate <= thirtyDaysFromNow;
}

function deleteDomain() {
    if (confirm('Are you sure you want to delete this domain?')) {
        router.delete(route('domains.destroy', props.domain.id));
    }
}

function approvePaymentLevel1() {
    router.patch(route('domains.approve.level1', props.domain.id));
}

function approvePaymentLevel2() {
    router.patch(route('domains.approve.level2', props.domain.id));
}
</script> 