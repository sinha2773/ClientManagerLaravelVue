<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Bill Details - {{ bill.bill_number }}
                </h2>
                <div class="flex space-x-2 no-print">
                    <SecondaryButton @click="printInvoice">
                        Print Invoice
                    </SecondaryButton>
                    <SecondaryButton @click="() => $inertia.visit(route('bills.index'))">
                        Back to Bills
                    </SecondaryButton>
                    <PrimaryButton 
                        v-if="canEdit"
                        @click="() => $inertia.visit(route('bills.edit', bill.id))"
                    >
                        Edit Bill
                    </PrimaryButton>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="invoice-print-area bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-8">
                        <div class="flex flex-col gap-6 border-b border-gray-200 pb-6 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <h1 class="text-xl font-bold uppercase tracking-wide text-gray-900">Invoice</h1>
                                <img
                                    src="/codegaon-logo.png"
                                    alt="Codegaon"
                                    class="mt-3 h-16 w-auto object-contain"
                                />
                            </div>
                            <div class="text-left sm:text-right">
                                <p class="text-sm font-medium text-gray-500">Invoice No.</p>
                                <p class="text-xl font-semibold text-gray-900">{{ bill.bill_number }}</p>
                                <p class="mt-3 text-sm text-gray-600">Issued: {{ formatDate(bill.created_at) }}</p>
                                <p class="text-sm text-gray-600">Due: {{ formatDate(bill.due_date) }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6 border-b border-gray-200 py-6 sm:grid-cols-2">
                            <div>
                                <h2 class="text-sm font-semibold uppercase text-gray-500">Bill To</h2>
                                <p class="mt-2 text-base font-semibold text-gray-900">{{ bill.client.name }}</p>
                                <p v-if="bill.client.company" class="text-sm text-gray-700">{{ bill.client.company }}</p>
                                <p v-if="bill.client.email" class="text-sm text-gray-700">{{ bill.client.email }}</p>
                                <p v-if="bill.client.phone" class="text-sm text-gray-700">{{ bill.client.phone }}</p>
                                <p v-if="bill.client.address" class="mt-2 whitespace-pre-line text-sm text-gray-700">{{ bill.client.address }}</p>
                            </div>
                            <div>
                                <h2 class="text-sm font-semibold uppercase text-gray-500">Invoice Details</h2>
                                <dl class="mt-2 grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                                    <dt class="text-gray-500">Service Type</dt>
                                    <dd class="text-right font-medium text-gray-900">{{ formatServiceType(bill.service_type) }}</dd>
                                    <dt class="text-gray-500">Academic Year</dt>
                                    <dd class="text-right font-medium text-gray-900">{{ bill.academic_year || '-' }}</dd>
                                    <template v-if="bill.service_type !== 'eims_fee'">
                                        <dt class="text-gray-500">Started Date</dt>
                                        <dd class="text-right font-medium text-gray-900">{{ formatDate(bill.service_started_date) }}</dd>
                                        <dt class="text-gray-500">Renewal Date</dt>
                                        <dd class="text-right font-medium text-gray-900">{{ formatDate(bill.service_renewal_date) }}</dd>
                                    </template>
                                    <dt class="text-gray-500">Status</dt>
                                    <dd class="text-right font-medium text-gray-900">{{ formatStatus(bill.status) }}</dd>
                                    <dt class="text-gray-500">Payment</dt>
                                    <dd class="text-right font-medium text-gray-900">{{ formatPaymentStatus(bill.payment_status) }}</dd>
                                    <dt v-if="service" class="text-gray-500">Domain</dt>
                                    <dd v-if="service" class="text-right font-medium text-gray-900">{{ service.name || service.domain?.name }}</dd>
                                </dl>
                            </div>
                        </div>

                        <div class="py-6">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead>
                                    <tr class="text-left text-xs font-semibold uppercase text-gray-500">
                                        <th class="py-3 pr-4">Description</th>
                                        <th class="px-4 py-3 text-right">Qty</th>
                                        <th class="px-4 py-3 text-right">Rate</th>
                                        <th class="py-3 pl-4 text-right">Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="item in invoiceLineItems" :key="item.description">
                                        <td class="py-4 pr-4 text-gray-900">
                                            <p class="font-medium">{{ item.description }}</p>
                                            <p v-if="item.meta" class="mt-1 text-xs text-gray-500">{{ item.meta }}</p>
                                        </td>
                                        <td class="px-4 py-4 text-right text-gray-700">{{ item.quantity }}</td>
                                        <td class="px-4 py-4 text-right text-gray-700">{{ formatMoney(item.rate) }}</td>
                                        <td class="py-4 pl-4 text-right font-medium text-gray-900">{{ formatMoney(item.amount) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="flex justify-end border-t border-gray-200 pt-6">
                            <dl class="w-full max-w-sm space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Subtotal</dt>
                                    <dd class="font-medium text-gray-900">{{ formatMoney(invoiceSubtotal) }}</dd>
                                </div>
                                <div v-if="Number(bill.discount || 0) > 0" class="flex justify-between">
                                    <dt class="text-gray-600">Discount</dt>
                                    <dd class="font-medium text-gray-900">-{{ formatMoney(bill.discount) }}</dd>
                                </div>
                                <div class="flex justify-between border-t border-gray-200 pt-3 text-base">
                                    <dt class="font-semibold text-gray-900">Total</dt>
                                    <dd class="font-bold text-gray-900">{{ formatMoney(bill.amount) }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Paid</dt>
                                    <dd class="font-medium text-gray-900">{{ formatMoney(bill.paid_amount) }}</dd>
                                </div>
                                <div class="flex justify-between text-base">
                                    <dt class="font-semibold text-gray-900">Balance Due</dt>
                                    <dd class="font-bold text-gray-900">{{ formatMoney(remainingAmount) }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div v-if="bill.notes || bill.service_type === 'eims_fee'" class="mt-8 border-t border-gray-200 pt-6 text-sm text-gray-700">
                            <p v-if="bill.service_type === 'eims_fee'">
                                Billing months:
                                <span class="font-medium">{{ formattedBillingMonths || '-' }}</span>
                            </p>
                            <p v-if="bill.student_summary_year" class="mt-1">
                                Student summary year: <span class="font-medium">{{ bill.student_summary_year }}</span>
                            </p>
                            <p v-if="bill.notes" class="mt-3 whitespace-pre-line">{{ bill.notes }}</p>
                        </div>

                        <div class="mt-10 grid grid-cols-1 gap-8 text-sm text-gray-700 sm:grid-cols-2">
                            <div>
                                <p class="font-medium text-gray-900">Prepared By</p>
                                <p class="mt-1">{{ bill.creator.name }}</p>
                            </div>
                            <div class="text-left sm:text-right">
                                <div class="ml-auto mt-8 w-48 border-t border-gray-400 pt-2 sm:mt-0">
                                    Authorized Signature
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bill Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg no-print">
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Bill Information</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Bill Number</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ bill.bill_number }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Client</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ bill.client.name }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Academic Year</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ bill.academic_year || '-' }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Service Type</label>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                            :class="getServiceTypeClass(bill.service_type)">
                                            {{ formatServiceType(bill.service_type) }}
                                        </span>
                                    </div>
                                    <div v-if="bill.service_type !== 'eims_fee'">
                                        <label class="block text-sm font-medium text-gray-700">Started Date</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ formatDate(bill.service_started_date) }}</p>
                                    </div>
                                    <div v-if="bill.service_type !== 'eims_fee'">
                                        <label class="block text-sm font-medium text-gray-700">Renewal Date</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ formatDate(bill.service_renewal_date) }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Status</label>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                            :class="getStatusClass(bill.status)">
                                            {{ formatStatus(bill.status) }}
                                        </span>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Created By</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ bill.creator.name }}</p>
                                    </div>
                                    <div v-if="bill.approver">
                                        <label class="block text-sm font-medium text-gray-700">Approved By</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ bill.approver.name }}</p>
                                        <p class="text-xs text-gray-500">{{ formatDateTime(bill.approved_at) }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-col space-y-2">
                                <PrimaryButton 
                                    v-if="canApprove && bill.status === 'draft'"
                                    @click="approveBill"
                                    class="bg-green-600 hover:bg-green-700"
                                >
                                    Approve Bill
                                </PrimaryButton>
                                <DangerButton 
                                    v-if="canEdit"
                                    @click="deleteBill"
                                >
                                    Delete Bill
                                </DangerButton>
                            </div>
                        </div>

                        <div class="border-t pt-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Description</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ bill.description }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Due Date</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ formatDate(bill.due_date) }}</p>
                                    <p v-if="isOverdue" class="text-xs text-red-600 font-medium">Overdue</p>
                                </div>
                                <div v-if="bill.notes">
                                    <label class="block text-sm font-medium text-gray-700">Notes</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ bill.notes }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Service Details -->
                <div v-if="service" class="bg-white overflow-hidden shadow-sm sm:rounded-lg no-print">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Service Details</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-if="bill.service_type === 'domain'">
                                <label class="block text-sm font-medium text-gray-700">Domain Name</label>
                                <p class="mt-1 text-sm text-gray-900">{{ service.name }}</p>
                            </div>
                            <div v-if="bill.service_type === 'hosting'">
                                <label class="block text-sm font-medium text-gray-700">Package Name</label>
                                <p class="mt-1 text-sm text-gray-900">{{ service.package_name }}</p>
                            </div>
                            <div v-if="bill.service_type === 'ssl_certificate'">
                                <label class="block text-sm font-medium text-gray-700">SSL Type</label>
                                <p class="mt-1 text-sm text-gray-900">{{ service.type }} - {{ service.provider }}</p>
                            </div>
                            <div v-if="service.domain && bill.service_type !== 'domain'">
                                <label class="block text-sm font-medium text-gray-700">Related Domain</label>
                                <p class="mt-1 text-sm text-gray-900">{{ service.domain.name }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- EIMS Fee Details -->
                <div v-if="bill.service_type === 'eims_fee'" class="bg-white overflow-hidden shadow-sm sm:rounded-lg no-print">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">EIMS Fee Details</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div v-if="service" class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Domain</label>
                                <p class="text-lg font-semibold text-gray-900">{{ service.name }}</p>
                            </div>
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Total Students</label>
                                <p class="text-3xl font-bold text-blue-900">{{ bill.total_students }}</p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Monthly EIMS Fee</label>
                                <p class="text-lg font-semibold text-gray-900">Tk {{ Number(bill.eims_monthly || 0).toFixed(2) }}</p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Discount</label>
                                <p class="text-lg font-semibold text-gray-900">Tk {{ Number(bill.discount || 0).toFixed(2) }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Billing Months</label>
                                <div class="flex flex-wrap gap-2">
                                    <span 
                                        v-for="(month, index) in bill.billing_months" 
                                        :key="index"
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800"
                                    >
                                        {{ formatMonth(month) }}
                                    </span>
                                </div>
                                <p class="mt-2 text-sm text-gray-600">
                                    Total: {{ bill.billing_months ? bill.billing_months.length : 0 }} month(s)
                                </p>
                            </div>
                            <div v-if="bill.student_summary_year || bill.student_grand_totals">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Student Summary</label>
                                <div class="rounded-md border border-gray-200 bg-gray-50 p-3 text-sm text-gray-700">
                                    <p v-if="bill.student_summary_year">Year: {{ bill.student_summary_year }}</p>
                                    <p v-if="bill.student_grand_totals">Regular: {{ bill.student_grand_totals.regular || 0 }}</p>
                                    <p v-if="bill.student_grand_totals">Others: {{ bill.student_grand_totals.others || 0 }}</p>
                                    <p v-if="bill.student_grand_totals">Total: {{ bill.student_grand_totals.total || 0 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg no-print">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Payment Information</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-700">Total Amount</label>
                                <p class="mt-1 text-2xl font-semibold text-gray-900">Tk {{ Number(bill.amount).toFixed(2) }}</p>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-700">Paid Amount</label>
                                <p class="mt-1 text-2xl font-semibold text-green-600">Tk {{ Number(bill.paid_amount).toFixed(2) }}</p>
                            </div>
                            <div class="bg-red-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-700">Remaining Amount</label>
                                <p class="mt-1 text-2xl font-semibold text-red-600">Tk {{ remainingAmount.toFixed(2) }}</p>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Payment Status</label>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                :class="getPaymentStatusClass(bill.payment_status)">
                                {{ formatPaymentStatus(bill.payment_status) }}
                            </span>
                            <p v-if="bill.paid_date" class="text-sm text-gray-500 mt-1">
                                Paid on: {{ formatDate(bill.paid_date) }}
                            </p>
                        </div>

                        <!-- Update Payment Form -->
                        <div v-if="bill.payment_status !== 'paid'" class="border-t pt-6 no-print">
                            <h4 class="text-md font-medium text-gray-900 mb-4">Update Payment</h4>
                            <form @submit.prevent="updatePayment" class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <InputLabel for="paid_amount" value="Paid Amount (Tk)" />
                                        <TextInput
                                            id="paid_amount"
                                            v-model="paymentForm.paid_amount"
                                            type="number"
                                            step="0.01"
                                            :min="Number(bill.paid_amount)"
                                            :max="Number(bill.amount)"
                                            class="mt-1 block w-full"
                                            required
                                        />
                                        <InputError :message="paymentForm.errors.paid_amount" class="mt-2" />
                                    </div>
                                    <div>
                                        <InputLabel for="payment_notes" value="Payment Notes (Optional)" />
                                        <TextInput
                                            id="payment_notes"
                                            v-model="paymentForm.notes"
                                            type="text"
                                            class="mt-1 block w-full"
                                            placeholder="Payment reference, notes..."
                                        />
                                        <InputError :message="paymentForm.errors.notes" class="mt-2" />
                                    </div>
                                </div>
                                <div class="flex justify-end">
                                    <PrimaryButton 
                                        :class="{ 'opacity-25': paymentForm.processing }" 
                                        :disabled="paymentForm.processing"
                                    >
                                        Update Payment
                                    </PrimaryButton>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import DangerButton from '@/Components/DangerButton.vue'
import TextInput from '@/Components/TextInput.vue'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'

const props = defineProps({
    bill: Object,
    service: Object,
    canApprove: Boolean,
    canEdit: Boolean,
})

const paymentForm = useForm({
    paid_amount: props.bill.paid_amount,
    notes: props.bill.notes || '',
})

const remainingAmount = computed(() => {
    return Number(props.bill.amount) - Number(props.bill.paid_amount)
})

const formattedBillingMonths = computed(() => {
    return (props.bill.billing_months || []).map(formatMonth).join(', ')
})

const invoiceSubtotal = computed(() => {
    if (props.bill.service_type === 'eims_fee') {
        return invoiceLineItems.value.reduce((total, item) => total + Number(item.amount || 0), 0)
    }

    return Number(props.bill.amount || 0)
})

const invoiceLineItems = computed(() => {
    if (props.bill.service_type === 'eims_fee') {
        const students = Number(props.bill.total_students || 0)
        const months = props.bill.billing_months?.length || 0
        const monthlyFee = Number(props.bill.eims_monthly || 0)

        return [
            {
                description: props.bill.description || 'EIMS Monthly Student Fee',
                meta: `${students} student(s) x ${months} month(s)${formattedBillingMonths.value ? ` (${formattedBillingMonths.value})` : ''}`,
                quantity: students * months,
                rate: monthlyFee,
                amount: students * months * monthlyFee,
            },
        ]
    }

    return [
        {
            description: props.bill.description || formatServiceType(props.bill.service_type),
            meta: props.service ? getServiceDisplayName(props.service) : '',
            quantity: 1,
            rate: Number(props.bill.amount || 0),
            amount: Number(props.bill.amount || 0),
        },
    ]
})

const isOverdue = computed(() => {
    const today = new Date()
    const dueDate = new Date(props.bill.due_date)
    return dueDate < today && props.bill.payment_status !== 'paid'
})

const approveBill = () => {
    if (confirm('Are you sure you want to approve this bill?')) {
        router.patch(route('bills.approve', props.bill.id))
    }
}

const deleteBill = () => {
    if (confirm('Are you sure you want to delete this bill? This action cannot be undone.')) {
        router.delete(route('bills.destroy', props.bill.id))
    }
}

const updatePayment = () => {
    paymentForm.patch(route('bills.update-payment', props.bill.id), {
        onSuccess: () => {
            // Payment updated successfully
        }
    })
}

const printInvoice = () => {
    window.print()
}

const formatMoney = (amount) => {
    return `Tk ${Number(amount || 0).toFixed(2)}`
}

const getServiceDisplayName = (service) => {
    switch (props.bill.service_type) {
        case 'domain':
            return service.name
        case 'hosting':
            return `${service.package_name} (${service.domain?.name || 'No domain'})`
        case 'ssl_certificate':
            return `${service.type} - ${service.provider}${service.domain?.name ? ` (${service.domain.name})` : ''}`
        case 'eims_fee':
            return service.name
        default:
            return ''
    }
}

const formatServiceType = (type) => {
    const types = {
        domain: 'Domain',
        hosting: 'Hosting',
        ssl_certificate: 'SSL Certificate',
        eims_fee: 'EIMS Fee'
    }
    return types[type] || type
}

const formatMonth = (monthYear) => {
    // Convert YYYY-MM to readable format like "October 2025"
    const [year, month] = monthYear.split('-')
    const date = new Date(year, parseInt(month) - 1)
    return date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
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
    if (!date) {
        return '-'
    }

    const parsedDate = new Date(date)

    return Number.isNaN(parsedDate.getTime()) ? '-' : parsedDate.toLocaleDateString()
}

const formatDateTime = (datetime) => {
    return new Date(datetime).toLocaleString()
}

const getServiceTypeClass = (type) => {
    const classes = {
        domain: 'bg-blue-100 text-blue-800',
        hosting: 'bg-green-100 text-green-800',
        ssl_certificate: 'bg-purple-100 text-purple-800',
        eims_fee: 'bg-indigo-100 text-indigo-800'
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

<style scoped>
@media print {
    @page {
        margin: 14mm;
        size: A4;
    }

    :global(body) {
        background: #ffffff !important;
    }

    :global(body *) {
        visibility: hidden !important;
    }

    .invoice-print-area,
    .invoice-print-area * {
        visibility: visible !important;
    }

    .invoice-print-area {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        box-shadow: none !important;
        border-radius: 0 !important;
    }

    .no-print {
        display: none !important;
    }
}
</style>
