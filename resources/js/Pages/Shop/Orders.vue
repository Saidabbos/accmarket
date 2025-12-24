<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    orders: Object,
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const getStatusColor = (status) => {
    const colors = {
        pending: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        processing: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        paid: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
        completed: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
        failed: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
        cancelled: 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-400',
    };
    return colors[status] || 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-400';
};
</script>

<template>
    <Head title="My Orders" />

    <AppLayout>
        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">My Orders</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">View and manage your order history</p>
                </div>

                <div v-if="orders.data.length > 0" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800/50">
                                <tr>
                                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Order
                                    </th>
                                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Date
                                    </th>
                                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Items
                                    </th>
                                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Total
                                    </th>
                                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Payment
                                    </th>
                                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <tr v-for="order in orders.data" :key="order.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <Link
                                            :href="route('orders.show', order.id)"
                                            class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 font-medium"
                                        >
                                            #{{ order.order_number }}
                                        </Link>
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ formatDate(order.created_at) }}
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        {{ order.items?.length || 0 }} items
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-white">
                                        ${{ parseFloat(order.total_amount).toFixed(2) }}
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                            :class="getStatusColor(order.payment_status)"
                                        >
                                            {{ order.payment_status }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                            :class="getStatusColor(order.status)"
                                        >
                                            {{ order.status }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                        <Link
                                            :href="route('orders.show', order.id)"
                                            class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300"
                                        >
                                            View
                                        </Link>
                                        <Link
                                            v-if="order.payment_status === 'pending'"
                                            :href="route('payment.show', order.id)"
                                            class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-900 dark:hover:text-emerald-300"
                                        >
                                            Pay Now
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="orders.links && orders.links.length > 3" class="px-5 py-4 border-t border-gray-100 dark:border-gray-700">
                        <div class="flex items-center justify-center space-x-1">
                            <Link
                                v-for="link in orders.links"
                                :key="link.label"
                                :href="link.url || '#'"
                                class="px-3 py-1.5 text-sm rounded-lg transition-colors"
                                :class="{
                                    'bg-indigo-600 text-white': link.active,
                                    'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700': !link.active && link.url,
                                    'bg-gray-50 dark:bg-gray-800 text-gray-400 dark:text-gray-500 cursor-not-allowed': !link.url,
                                }"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-12 text-center">
                    <div class="w-20 h-20 mx-auto mb-6 rounded-2xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">No orders yet</h2>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">You haven't placed any orders yet. Start shopping!</p>
                    <Link
                        :href="route('shop.index')"
                        class="inline-flex items-center px-6 py-3 text-base font-medium text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 transition-colors"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        Start Shopping
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
