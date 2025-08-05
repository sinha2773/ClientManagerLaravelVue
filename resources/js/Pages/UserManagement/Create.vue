<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create New User</h2>
                <SecondaryButton @click="() => $inertia.visit(route('user-management.index'))">
                    Back to Users
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

                        <!-- Password -->
                        <div>
                            <InputLabel for="password" value="Password" />
                            <TextInput
                                id="password"
                                v-model="form.password"
                                type="password"
                                class="mt-1 block w-full"
                                required
                                placeholder="Enter password (minimum 8 characters)"
                            />
                            <InputError :message="form.errors.password" class="mt-2" />
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <InputLabel for="password_confirmation" value="Confirm Password" />
                            <TextInput
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                class="mt-1 block w-full"
                                required
                                placeholder="Confirm password"
                            />
                            <InputError :message="form.errors.password_confirmation" class="mt-2" />
                        </div>

                        <!-- Status -->
                        <div>
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

                        <!-- Actions -->
                        <div class="flex items-center justify-end space-x-4">
                            <SecondaryButton type="button" @click="() => $inertia.visit(route('user-management.index'))">
                                Cancel
                            </SecondaryButton>
                            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                Create User
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

const form = useForm({
    name: '',
    email: '',
    user_type: '',
    password: '',
    password_confirmation: '',
    is_active: true,
})

const submit = () => {
    form.post(route('user-management.store'))
}
</script> 