<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Edit User - {{ user.name }}
                </h2>
                <SecondaryButton @click="() => $inertia.visit(route('user-management.show', user.id))">
                    Back to User
                </SecondaryButton>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <!-- Name -->
                        <div>
                            <InputLabel for="name" value="Full Name" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full"
                                required
                                placeholder="Enter full name"
                            />
                            <InputError :message="form.errors.name" class="mt-2" />
                        </div>

                        <!-- Email -->
                        <div>
                            <InputLabel for="email" value="Email Address" />
                            <TextInput
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="mt-1 block w-full"
                                required
                                placeholder="Enter email address"
                            />
                            <InputError :message="form.errors.email" class="mt-2" />
                        </div>

                        <!-- User Type -->
                        <div>
                            <InputLabel for="user_type" value="User Type" />
                            <select
                                id="user_type"
                                v-model="form.user_type"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required
                            >
                                <option value="">Select user type</option>
                                <option value="account_manager">Account Manager</option>
                                <option value="approver">Approver</option>
                            </select>
                            <InputError :message="form.errors.user_type" class="mt-2" />
                            <p class="mt-1 text-sm text-gray-500">
                                Account Managers can create and manage bills. Approvers can approve bills and manage users.
                            </p>
                        </div>

                        <!-- Password Section -->
                        <div class="border-t pt-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Change Password (Optional)</h3>
                            <p class="text-sm text-gray-500 mb-4">
                                Leave password fields empty to keep the current password unchanged.
                            </p>

                            <!-- New Password -->
                            <div>
                                <InputLabel for="password" value="New Password" />
                                <TextInput
                                    id="password"
                                    v-model="form.password"
                                    type="password"
                                    class="mt-1 block w-full"
                                    placeholder="Enter new password (minimum 8 characters)"
                                />
                                <InputError :message="form.errors.password" class="mt-2" />
                            </div>

                            <!-- Confirm New Password -->
                            <div class="mt-4">
                                <InputLabel for="password_confirmation" value="Confirm New Password" />
                                <TextInput
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    type="password"
                                    class="mt-1 block w-full"
                                    placeholder="Confirm new password"
                                />
                                <InputError :message="form.errors.password_confirmation" class="mt-2" />
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="border-t pt-6">
                            <div class="flex items-center">
                                <Checkbox
                                    id="is_active"
                                    v-model:checked="form.is_active"
                                    name="is_active"
                                />
                                <label for="is_active" class="ml-2 text-sm text-gray-900">
                                    Active User
                                </label>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">
                                Active users can log in and access the system. Inactive users are locked out.
                            </p>
                            <InputError :message="form.errors.is_active" class="mt-2" />
                        </div>

                        <!-- Warning for Self-Edit -->
                        <div v-if="user.id === $page.props.auth.user.id" class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800">
                                        Warning: Editing Your Own Account
                                    </h3>
                                    <div class="mt-2 text-sm text-yellow-700">
                                        <p>You are editing your own user account. Be careful when changing your user type or deactivating your account as this may affect your access to the system.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end space-x-4">
                            <SecondaryButton type="button" @click="() => $inertia.visit(route('user-management.show', user.id))">
                                Cancel
                            </SecondaryButton>
                            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                Update User
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'
import Checkbox from '@/Components/Checkbox.vue'

const props = defineProps({
    user: Object,
})

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    user_type: props.user.user_type,
    password: '',
    password_confirmation: '',
    is_active: props.user.is_active,
})

const submit = () => {
    form.patch(route('user-management.update', props.user.id))
}
</script> 