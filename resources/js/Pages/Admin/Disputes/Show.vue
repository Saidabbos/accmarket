<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    dispute: Object,
});

const showResolveModal = ref(false);

const resolveForm = useForm({
    resolution: '',
    action: 'no_action',
    refund_amount: '',
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getStatusColor = (status) => {
    const colors = {
        open: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        in_progress: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        resolved: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
        closed: 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-400',
    };
    return colors[status] || colors.open;
};

const updateStatus = (newStatus) => {
    router.patch(route('admin.disputes.status', props.dispute.id), {
        status: newStatus,
    }, {
        preserveScroll: true,
    });
};

const submitResolution = () => {
    resolveForm.post(route('admin.disputes.resolve', props.dispute.id), {
        onSuccess: () => {
            showResolveModal.value = false;
            resolveForm.reset();
        },
    });
};
</script>

<template>
    <Head :title="`Dispute: ${dispute.subject} - Admin`" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Link
                        :href="route('admin.disputes.index')"
                        class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dispute Details</h1>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ formatDate(dispute.created_at) }}</p>
                    </div>
                </div>
                <button
                    v-if="dispute.status !== 'resolved' && dispute.status !== 'closed'"
                    @click="showResolveModal = true"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Resolve Dispute
                </button>
            </div>
        </template>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Status Card -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Status</h3>
                        <div class="relative inline-block">
                            <select
                                :value="dispute.status"
                                @change="updateStatus($event.target.value)"
                                class="appearance-none text-sm font-medium px-4 py-2 pr-8 rounded-full border-0 cursor-pointer focus:ring-2 focus:ring-indigo-500"
                                :class="getStatusColor(dispute.status)"
                            >
                                <option value="open">Open</option>
                                <option value="in_progress">In Progress</option>
                                <option value="resolved">Resolved</option>
                                <option value="closed">Closed</option>
                            </select>
                            <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Dispute Details -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ dispute.subject }}</h3>
                    <div class="prose prose-sm dark:prose-invert max-w-none">
                        <p class="text-gray-600 dark:text-gray-400 whitespace-pre-wrap">{{ dispute.description }}</p>
                    </div>
                </div>

                <!-- Resolution (if resolved) -->
                <div v-if="dispute.resolution" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-6">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Resolution</h3>
                            <p v-if="dispute.resolver" class="text-sm text-gray-500 dark:text-gray-400">
                                Resolved by {{ dispute.resolver.name }} on {{ formatDate(dispute.resolved_at) }}
                            </p>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                        <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ dispute.resolution }}</p>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Order Items</h3>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        <div
                            v-for="item in dispute.order?.items"
                            :key="item.id"
                            class="p-6"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 flex items-center justify-center">
                                        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            {{ item.product_item?.product?.name || 'Product' }}
                                        </p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Qty: {{ item.quantity }}</p>
                                    </div>
                                </div>
                                <p class="font-semibold text-gray-900 dark:text-white">{{ formatCurrency(item.price) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Order Info -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Order Details</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Order Number</span>
                            <span class="font-medium text-gray-900 dark:text-white">#{{ dispute.order?.order_number }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Order Total</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ formatCurrency(dispute.order?.total_amount || 0) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Order Date</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ formatDate(dispute.order?.created_at) }}</span>
                        </div>
                        <div class="pt-3 border-t border-gray-100 dark:border-gray-700">
                            <Link
                                :href="route('admin.orders.show', dispute.order?.id)"
                                class="inline-flex items-center text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300"
                            >
                                View Order
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Customer Info -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Customer</h3>
                    <div class="flex items-center space-x-4 mb-4">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                            <span class="text-white font-medium">{{ dispute.user?.name?.charAt(0)?.toUpperCase() || '?' }}</span>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">{{ dispute.user?.name }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ dispute.user?.email }}</p>
                        </div>
                    </div>
                    <Link
                        :href="route('admin.users.show', dispute.user?.id)"
                        class="inline-flex items-center text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300"
                    >
                        View Profile
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                </div>

                <!-- Refund Info (if applicable) -->
                <div v-if="dispute.order?.refund_amount" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-6">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Refund Issued</h3>
                    </div>
                    <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-lg p-4">
                        <div class="flex justify-between items-center">
                            <span class="text-emerald-700 dark:text-emerald-300">Amount</span>
                            <span class="text-xl font-bold text-emerald-700 dark:text-emerald-300">
                                {{ formatCurrency(dispute.order.refund_amount) }}
                            </span>
                        </div>
                        <p v-if="dispute.order.refunded_at" class="mt-2 text-sm text-emerald-600 dark:text-emerald-400">
                            {{ formatDate(dispute.order.refunded_at) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resolve Modal -->
        <div v-if="showResolveModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 bg-gray-500 dark:bg-gray-900 bg-opacity-75 dark:bg-opacity-75 transition-opacity" @click="showResolveModal = false"></div>

                <div class="relative inline-block w-full max-w-lg p-6 my-8 text-left align-middle bg-white dark:bg-gray-800 rounded-2xl shadow-xl transform transition-all">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Resolve Dispute</h3>
                        <button
                            @click="showResolveModal = false"
                            class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form @submit.prevent="submitResolution" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Resolution Action
                            </label>
                            <div class="space-y-2">
                                <label class="flex items-center p-3 rounded-lg border border-gray-200 dark:border-gray-600 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <input
                                        type="radio"
                                        v-model="resolveForm.action"
                                        value="no_action"
                                        class="text-indigo-600 focus:ring-indigo-500"
                                    />
                                    <span class="ml-3 text-sm text-gray-700 dark:text-gray-300">No refund - resolve without action</span>
                                </label>
                                <label class="flex items-center p-3 rounded-lg border border-gray-200 dark:border-gray-600 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <input
                                        type="radio"
                                        v-model="resolveForm.action"
                                        value="refund"
                                        class="text-indigo-600 focus:ring-indigo-500"
                                    />
                                    <span class="ml-3 text-sm text-gray-700 dark:text-gray-300">
                                        Full refund ({{ formatCurrency(dispute.order?.total_amount || 0) }})
                                    </span>
                                </label>
                                <label class="flex items-center p-3 rounded-lg border border-gray-200 dark:border-gray-600 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <input
                                        type="radio"
                                        v-model="resolveForm.action"
                                        value="partial_refund"
                                        class="text-indigo-600 focus:ring-indigo-500"
                                    />
                                    <span class="ml-3 text-sm text-gray-700 dark:text-gray-300">Partial refund</span>
                                </label>
                            </div>
                        </div>

                        <div v-if="resolveForm.action === 'partial_refund'">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Refund Amount
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">$</span>
                                <input
                                    v-model="resolveForm.refund_amount"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    :max="dispute.order?.total_amount"
                                    placeholder="0.00"
                                    class="w-full pl-8 pr-4 py-3 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                />
                            </div>
                            <p v-if="resolveForm.errors.refund_amount" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ resolveForm.errors.refund_amount }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Resolution Notes *
                            </label>
                            <textarea
                                v-model="resolveForm.resolution"
                                rows="4"
                                placeholder="Explain how this dispute was resolved..."
                                class="w-full px-4 py-3 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none"
                                required
                            ></textarea>
                            <p v-if="resolveForm.errors.resolution" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ resolveForm.errors.resolution }}
                            </p>
                        </div>

                        <div class="flex items-center justify-end space-x-4 pt-4">
                            <button
                                type="button"
                                @click="showResolveModal = false"
                                class="px-6 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="resolveForm.processing"
                                class="px-6 py-3 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                            >
                                <span v-if="resolveForm.processing">Processing...</span>
                                <span v-else>Resolve Dispute</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
