<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    activityLogs: { type: Object, required: true },
    filters: { type: Object, required: true },
    canViewAll: { type: Boolean, required: true },
    users: { type: Array, required: true },
    stats: { type: Object, required: true },
});

const page = usePage();
const expandedLogs = ref(new Set());
const filterForm = ref({
    search: props.filters.search || '',
    action: props.filters.action || '',
    user_id: props.filters.user_id || '',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
});

let searchTimer;

const hasFilters = computed(() => Object.values(filterForm.value).some(Boolean));

function applyFilters() {
    router.get(route('activity-logs.index'), filterForm.value, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function applySearch() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(applyFilters, 300);
}

function resetFilters() {
    filterForm.value = { search: '', action: '', user_id: '', date_from: '', date_to: '' };
    applyFilters();
}

function toggleDetails(id) {
    const next = new Set(expandedLogs.value);
    next.has(id) ? next.delete(id) : next.add(id);
    expandedLogs.value = next;
}

function isExpanded(id) {
    return expandedLogs.value.has(id);
}

function actionStyle(action) {
    return {
        created: { box: 'bg-emerald-50 text-emerald-600', badge: 'bg-emerald-100 text-emerald-700' },
        updated: { box: 'bg-amber-50 text-amber-600', badge: 'bg-amber-100 text-amber-700' },
        deleted: { box: 'bg-red-50 text-red-600', badge: 'bg-red-100 text-red-700' },
        approved: { box: 'bg-indigo-50 text-indigo-600', badge: 'bg-indigo-100 text-indigo-700' },
        logged_in: { box: 'bg-blue-50 text-blue-600', badge: 'bg-blue-100 text-blue-700' },
        logged_out: { box: 'bg-slate-100 text-slate-600', badge: 'bg-slate-200 text-slate-700' },
    }[action] || { box: 'bg-gray-100 text-gray-600', badge: 'bg-gray-100 text-gray-700' };
}

function actionLabel(action) {
    return {
        created: 'Created',
        updated: 'Updated',
        deleted: 'Deleted',
        approved: 'Approved',
        logged_in: 'Signed in',
        logged_out: 'Signed out',
    }[action] || action;
}

function activityTitle(log) {
    const actor = log.user_id === page.props.auth.user.id ? 'You' : (log.actor_name || 'System');
    const verb = actionLabel(log.action).toLowerCase();

    if (['logged_in', 'logged_out'].includes(log.action)) {
        return `${actor} ${verb}`;
    }

    return `${actor} ${verb} ${subjectName(log.subject_type)}${log.subject_label ? ` — ${log.subject_label}` : ''}`;
}

function subjectName(type) {
    return type ? type.split('\\').pop().replace(/([a-z])([A-Z])/g, '$1 $2') : 'record';
}

function formatDateTime(value) {
    return new Intl.DateTimeFormat('en-GB', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit', hour12: true,
    }).format(new Date(value));
}

function formatDevice(userAgent) {
    if (!userAgent) return 'Unknown device';

    const browser = userAgent.includes('Edg/') ? 'Edge'
        : userAgent.includes('Chrome/') ? 'Chrome'
            : userAgent.includes('Firefox/') ? 'Firefox'
                : userAgent.includes('Safari/') ? 'Safari' : 'Browser';
    const device = userAgent.includes('iPhone') ? 'iPhone'
        : userAgent.includes('Android') ? 'Android'
            : userAgent.includes('Macintosh') ? 'Mac'
                : userAgent.includes('Windows') ? 'Windows'
                    : userAgent.includes('Linux') ? 'Linux' : 'device';

    return `${browser} on ${device}`;
}

function changes(log) {
    return Object.entries(log.metadata?.changes || {});
}

function displayValue(value) {
    if (value === null || value === undefined || value === '') return '—';
    if (typeof value === 'boolean') return value ? 'Yes' : 'No';
    if (Array.isArray(value)) return value.join(', ');
    if (typeof value === 'object') return JSON.stringify(value);
    return String(value);
}
</script>

<template>
    <Head title="Activity Logs" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Activity Logs</h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ canViewAll ? 'Review activity across all users.' : 'Review your account activity and changes.' }}
                </p>
            </div>
        </template>

        <div class="py-8">
            <div class="page-container space-y-6">
                <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                        <p class="text-sm font-medium text-gray-500">Total activity</p>
                        <p class="mt-2 text-2xl font-semibold text-gray-900">{{ stats.total }}</p>
                    </div>
                    <div class="rounded-xl border border-blue-100 bg-blue-50 p-4 shadow-sm">
                        <p class="text-sm font-medium text-blue-700">Today</p>
                        <p class="mt-2 text-2xl font-semibold text-blue-900">{{ stats.today }}</p>
                    </div>
                    <div class="rounded-xl border border-indigo-100 bg-indigo-50 p-4 shadow-sm">
                        <p class="text-sm font-medium text-indigo-700">Approvals</p>
                        <p class="mt-2 text-2xl font-semibold text-indigo-900">{{ stats.approvals }}</p>
                    </div>
                    <div class="rounded-xl border border-red-100 bg-red-50 p-4 shadow-sm">
                        <p class="text-sm font-medium text-red-700">Deletions</p>
                        <p class="mt-2 text-2xl font-semibold text-red-900">{{ stats.deletions }}</p>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-6">
                        <div class="md:col-span-2">
                            <label for="activity_search" class="block text-sm font-medium text-gray-700">Search activity</label>
                            <input
                                id="activity_search"
                                v-model="filterForm.search"
                                type="search"
                                placeholder="Actor, record, action, or IP address"
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                @input="applySearch"
                            />
                        </div>
                        <div>
                            <label for="activity_action" class="block text-sm font-medium text-gray-700">Action</label>
                            <select id="activity_action" v-model="filterForm.action" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" @change="applyFilters">
                                <option value="">All actions</option>
                                <option value="created">Created</option>
                                <option value="updated">Updated</option>
                                <option value="deleted">Deleted</option>
                                <option value="approved">Approved</option>
                                <option value="logged_in">Signed in</option>
                                <option value="logged_out">Signed out</option>
                            </select>
                        </div>
                        <div v-if="canViewAll">
                            <label for="activity_user" class="block text-sm font-medium text-gray-700">User</label>
                            <select id="activity_user" v-model="filterForm.user_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" @change="applyFilters">
                                <option value="">All users</option>
                                <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label for="activity_from" class="block text-sm font-medium text-gray-700">From</label>
                            <input id="activity_from" v-model="filterForm.date_from" type="date" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" @change="applyFilters" />
                        </div>
                        <div>
                            <label for="activity_to" class="block text-sm font-medium text-gray-700">To</label>
                            <input id="activity_to" v-model="filterForm.date_to" type="date" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" @change="applyFilters" />
                        </div>
                    </div>
                    <div v-if="hasFilters" class="mt-4 flex justify-end">
                        <button type="button" class="text-sm font-medium text-indigo-600 hover:text-indigo-800" @click="resetFilters">Clear filters</button>
                    </div>
                </div>

                <div v-if="activityLogs.data.length" class="space-y-4">
                    <article v-for="log in activityLogs.data" :key="log.id" class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition hover:shadow-md">
                        <div class="flex items-start gap-4 p-5 sm:p-6">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl" :class="actionStyle(log.action).box">
                                <svg v-if="log.action === 'created'" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                <svg v-else-if="log.action === 'updated'" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                <svg v-else-if="log.action === 'deleted'" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16" /></svg>
                                <svg v-else-if="log.action === 'approved'" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <svg v-else class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 17l5-5-5-5m5 5H3m12-7h4a2 2 0 012 2v10a2 2 0 01-2 2h-4" /></svg>
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="break-words text-base font-semibold text-gray-900 sm:text-lg">{{ activityTitle(log) }}</h3>
                                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="actionStyle(log.action).badge">{{ actionLabel(log.action) }}</span>
                                        </div>
                                        <div class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-gray-500">
                                            <span>{{ formatDateTime(log.created_at) }}</span>
                                            <span>{{ formatDevice(log.user_agent) }}</span>
                                            <span v-if="canViewAll && log.actor_email">{{ log.actor_email }}</span>
                                        </div>
                                    </div>
                                    <button type="button" class="inline-flex shrink-0 items-center gap-1 text-sm font-medium text-gray-600 hover:text-indigo-600" @click="toggleDetails(log.id)">
                                        Details
                                        <svg class="h-4 w-4 transition" :class="{ 'rotate-180': isExpanded(log.id) }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                    </button>
                                </div>

                                <div v-if="isExpanded(log.id)" class="mt-5 border-t border-gray-100 pt-5">
                                    <dl class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2 lg:grid-cols-4">
                                        <div><dt class="font-medium text-gray-500">IP address</dt><dd class="mt-1 text-gray-900">{{ log.ip_address || 'Unknown' }}</dd></div>
                                        <div><dt class="font-medium text-gray-500">Device</dt><dd class="mt-1 text-gray-900">{{ formatDevice(log.user_agent) }}</dd></div>
                                        <div><dt class="font-medium text-gray-500">Route</dt><dd class="mt-1 text-gray-900">{{ log.metadata?.route || '—' }}</dd></div>
                                        <div><dt class="font-medium text-gray-500">Request</dt><dd class="mt-1 text-gray-900">{{ log.metadata?.method || '—' }}</dd></div>
                                    </dl>

                                    <div v-if="changes(log).length" class="mt-5 overflow-hidden rounded-lg border border-gray-200">
                                        <div class="bg-gray-50 px-4 py-2 text-sm font-semibold text-gray-700">Changed fields</div>
                                        <div class="divide-y divide-gray-100">
                                            <div v-for="([field, values]) in changes(log)" :key="field" class="grid grid-cols-1 gap-2 px-4 py-3 text-sm sm:grid-cols-3">
                                                <div class="font-medium text-gray-700">{{ field.replaceAll('_', ' ') }}</div>
                                                <div class="break-words text-gray-500"><span class="text-xs uppercase text-gray-400">Before:</span> {{ displayValue(values.old) }}</div>
                                                <div class="break-words text-gray-900"><span class="text-xs uppercase text-gray-400">After:</span> {{ displayValue(values.new) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>
                </div>

                <div v-else class="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-16 text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    </div>
                    <h3 class="mt-4 font-semibold text-gray-900">No activity found</h3>
                    <p class="mt-1 text-sm text-gray-500">Try clearing the filters or check back after taking an action.</p>
                </div>

                <div v-if="activityLogs.last_page > 1" class="flex flex-wrap justify-center gap-1">
                    <Link
                        v-for="link in activityLogs.links"
                        :key="link.label"
                        :href="link.url || '#'"
                        preserve-scroll
                        class="rounded-md border px-3 py-2 text-sm"
                        :class="[
                            link.active ? 'border-indigo-500 bg-indigo-50 font-medium text-indigo-700' : 'border-gray-300 bg-white text-gray-600',
                            !link.url ? 'pointer-events-none opacity-50' : 'hover:bg-gray-50',
                        ]"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
