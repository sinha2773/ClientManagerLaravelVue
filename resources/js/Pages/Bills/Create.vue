<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create New Bill</h2>
                <SecondaryButton @click="() => $inertia.visit(route('bills.index'))">
                    Back to Bills
                </SecondaryButton>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <!-- Client Selection -->
                        <div>
                            <InputLabel for="client_id" value="Client" />
                            <select
                                id="client_id"
                                v-model="form.client_id"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required
                                @change="filterServices"
                            >
                                <option value="">Select a client</option>
                                <option v-for="client in clients" :key="client.id" :value="client.id">
                                    {{ client.name }} ({{ client.email }})
                                </option>
                            </select>
                            <InputError :message="form.errors.client_id" class="mt-2" />
                        </div>

                        <!-- Service Type -->
                        <div>
                            <InputLabel for="service_type" value="Service Type" />
                            <select
                                id="service_type"
                                v-model="form.service_type"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required
                                @change="updateServiceOptions"
                            >
                                <option value="">Select service type</option>
                                <option value="domain">Domain</option>
                                <option value="hosting">Hosting Service</option>
                                <option value="ssl_certificate">SSL Certificate</option>
                            </select>
                            <InputError :message="form.errors.service_type" class="mt-2" />
                        </div>

                        <!-- Service Selection -->
                        <div v-if="form.service_type && availableServices.length > 0">
                            <InputLabel for="service_id" value="Service" />
                            <select
                                id="service_id"
                                v-model="form.service_id"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required
                                @change="updateAmountFromService"
                            >
                                <option value="">Select a service</option>
                                <option v-for="service in availableServices" :key="service.id" :value="service.id">
                                    {{ getServiceDisplayName(service) }} - ${{ Number(service.price || 0).toFixed(2) }}
                                </option>
                            </select>
                            <InputError :message="form.errors.service_id" class="mt-2" />
                        </div>

                        <div v-else-if="form.service_type && availableServices.length === 0" 
                             class="p-4 bg-yellow-50 border border-yellow-200 rounded-md">
                            <p class="text-yellow-800">No {{ formatServiceType(form.service_type) }} services found for the selected client.</p>
                        </div>

                        <!-- Description -->
                        <div>
                            <InputLabel for="description" value="Description" />
                            <TextInput
                                id="description"
                                v-model="form.description"
                                type="text"
                                class="mt-1 block w-full"
                                required
                                placeholder="Brief description of the bill"
                            />
                            <InputError :message="form.errors.description" class="mt-2" />
                        </div>

                        <!-- Amount -->
                        <div>
                            <InputLabel for="amount" value="Amount ($)" />
                            <TextInput
                                id="amount"
                                v-model="form.amount"
                                type="number"
                                step="0.01"
                                min="0"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="form.errors.amount" class="mt-2" />
                        </div>

                        <!-- Due Date -->
                        <div>
                            <InputLabel for="due_date" value="Due Date" />
                            <TextInput
                                id="due_date"
                                v-model="form.due_date"
                                type="date"
                                class="mt-1 block w-full"
                                required
                                :min="today"
                            />
                            <InputError :message="form.errors.due_date" class="mt-2" />
                        </div>

                        <!-- Notes -->
                        <div>
                            <InputLabel for="notes" value="Notes (Optional)" />
                            <TextArea
                                id="notes"
                                v-model="form.notes"
                                class="mt-1 block w-full"
                                rows="3"
                                placeholder="Additional notes about this bill"
                            />
                            <InputError :message="form.errors.notes" class="mt-2" />
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end space-x-4">
                            <SecondaryButton type="button" @click="() => $inertia.visit(route('bills.index'))">
                                Cancel
                            </SecondaryButton>
                            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                Create Bill
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import TextArea from '@/Components/TextArea.vue'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'

const props = defineProps({
    clients: Array,
    domains: Array,
    hostingServices: Array,
    sslCertificates: Array,
})

const form = useForm({
    client_id: '',
    service_type: '',
    service_id: '',
    description: '',
    amount: '',
    due_date: '',
    notes: '',
})

const availableServices = ref([])
const today = new Date().toISOString().split('T')[0]

const filterServices = () => {
    form.service_id = ''
    form.service_type = ''
    updateServiceOptions()
}

const updateServiceOptions = () => {
    form.service_id = ''
    form.amount = ''
    
    if (!form.client_id || !form.service_type) {
        availableServices.value = []
        return
    }

    switch (form.service_type) {
        case 'domain':
            availableServices.value = props.domains.filter(domain => domain.client_id == form.client_id)
            break
        case 'hosting':
            availableServices.value = props.hostingServices.filter(service => service.client_id == form.client_id)
            break
        case 'ssl_certificate':
            availableServices.value = props.sslCertificates.filter(ssl => 
                ssl.domain && ssl.domain.client_id == form.client_id
            )
            break
        default:
            availableServices.value = []
    }
}

const updateAmountFromService = () => {
    if (form.service_id) {
        const selectedService = availableServices.value.find(service => service.id == form.service_id)
        if (selectedService && selectedService.price) {
            form.amount = Number(selectedService.price).toFixed(2)
        }
    }
}

const getServiceDisplayName = (service) => {
    switch (form.service_type) {
        case 'domain':
            return service.name
        case 'hosting':
            return `${service.package_name} (${service.domain?.name || 'No domain'})`
        case 'ssl_certificate':
            return `${service.type} - ${service.provider} (${service.domain?.name || 'No domain'})`
        default:
            return 'Unknown Service'
    }
}

const formatServiceType = (type) => {
    const types = {
        domain: 'Domain',
        hosting: 'Hosting Service',
        ssl_certificate: 'SSL Certificate'
    }
    return types[type] || type
}

const submit = () => {
    form.post(route('bills.store'))
}

onMounted(() => {
    // Set default due date to 30 days from now
    const defaultDueDate = new Date()
    defaultDueDate.setDate(defaultDueDate.getDate() + 30)
    form.due_date = defaultDueDate.toISOString().split('T')[0]
})
</script> 