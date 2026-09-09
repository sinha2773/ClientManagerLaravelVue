<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout split>
        <Head title="Forgot Password" />

        <div class="mb-8">
            <p class="mb-3 text-xs font-semibold uppercase tracking-widest text-indigo-600">Account recovery</p>
            <h2 class="text-3xl font-semibold tracking-tight text-gray-900">Forgot your password?</h2>
            <p class="mt-3 text-sm leading-6 text-gray-500">Enter the email address for your account and we’ll send you a link to reset your password.</p>
        </div>

        <div
            v-if="status"
            class="mb-4 text-sm font-medium text-green-600"
        >
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-2 block w-full py-3"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4 flex items-center justify-end">
                <PrimaryButton
                    class="w-full justify-center bg-indigo-600 py-3 hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Email Password Reset Link
                </PrimaryButton>
            </div>
        </form>

        <div class="mt-6 text-center">
            <Link :href="route('login')" class="rounded-md text-sm font-medium text-gray-600 hover:text-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                Back to log in
            </Link>
        </div>
    </GuestLayout>
</template>
