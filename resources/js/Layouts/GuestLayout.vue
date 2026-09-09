<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    split: { type: Boolean, default: false },
});
</script>

<template>
    <div v-if="split" class="grid min-h-screen bg-white lg:grid-cols-2">
        <aside class="relative isolate flex overflow-hidden bg-slate-950 px-5 py-10 text-white sm:px-12 lg:min-h-screen lg:flex-col lg:justify-between lg:p-16">
            <div aria-hidden="true" class="absolute inset-0 -z-10 bg-[radial-gradient(ellipse_at_top_left,_#4338ca_0%,_transparent_60%),radial-gradient(ellipse_at_bottom_right,_#0f766e_0%,_transparent_60%)]"></div>
            <div aria-hidden="true" class="absolute -right-40 top-1/4 -z-10 h-[32rem] w-[32rem] rounded-full border border-white/10"></div>
            <div aria-hidden="true" class="absolute -right-24 top-1/3 -z-10 h-80 w-80 rounded-full border border-white/10"></div>

            <div class="flex w-full flex-col gap-10 lg:flex-1 lg:justify-between">
                <Link href="/" class="inline-flex w-fit items-center gap-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-white">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 ring-1 ring-white/20">
                        <ApplicationLogo class="h-7 w-7 fill-current text-white" />
                    </span>
                    <span class="text-lg font-semibold tracking-tight">Client Manager</span>
                </Link>

                <div class="max-w-lg lg:py-20">
                    <p class="mb-5 text-xs font-semibold uppercase tracking-[0.22em] text-indigo-200">Your business, connected</p>
                    <h1 class="text-3xl font-semibold leading-tight tracking-tight sm:text-4xl lg:text-5xl">Less admin.<br />More room to grow.</h1>
                    <p class="mt-6 max-w-md text-base leading-7 text-slate-300">Bring your clients, services, and billing together. Stay organized and keep your next priority in sight.</p>
                    <div class="mt-10 hidden flex-wrap gap-3 sm:flex">
                        <span v-for="feature in ['Client management', 'Service renewals', 'Simplified billing']" :key="feature" class="rounded-full border border-white/15 bg-white/5 px-4 py-2 text-xs text-slate-200">{{ feature }}</span>
                    </div>
                </div>

                <p class="hidden text-sm text-slate-400 lg:block">One workspace. A clearer view of your business.</p>
            </div>
        </aside>

        <main class="flex items-center justify-center px-5 py-12 sm:px-12 lg:py-16">
            <div class="w-full max-w-md">
                <slot />
            </div>
        </main>
    </div>
    <div v-else
        class="flex min-h-screen flex-col items-center bg-gray-100 px-[20px] pt-6 sm:justify-center sm:pt-0"
    >
        <div>
            <Link href="/">
                <ApplicationLogo class="h-20 w-20 fill-current text-gray-500" />
            </Link>
        </div>

        <div
            class="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:rounded-lg"
        >
            <slot />
        </div>
    </div>
</template>
