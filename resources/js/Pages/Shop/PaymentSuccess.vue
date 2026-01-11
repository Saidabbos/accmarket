<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    order: Object,
    isGuest: {
        type: Boolean,
        default: false,
    },
    guestToken: {
        type: String,
        default: null,
    },
});

const LayoutComponent = props.isGuest ? AppLayout : AuthenticatedLayout;
</script>

<template>
    <Head title="Payment Successful" />

    <component :is="LayoutComponent">
        <div class="py-8 lg:py-12">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden text-center p-8">
                    <!-- Success Icon -->
                    <div class="w-20 h-20 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>

                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Payment Successful!</h1>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        Thank you for your purchase. Your order #{{ order.order_number }} has been confirmed.
                    </p>

                    <!-- Order Summary -->
                    <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-4 mb-6 text-left">
                        <h3 class="font-medium text-gray-900 dark:text-white mb-3">Order Summary</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Order Number</span>
                                <span class="font-medium text-gray-900 dark:text-white">#{{ order.order_number }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Items</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ order.items?.length || 0 }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Total Paid</span>
                                <span class="font-medium text-green-600 dark:text-green-400">${{ parseFloat(order.total_amount).toFixed(2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Info Box -->
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4 mb-6 text-left">
                        <h4 class="font-medium text-blue-900 dark:text-blue-300 mb-2">What's Next?</h4>
                        <ul class="text-sm text-blue-800 dark:text-blue-400 space-y-1">
                            <li v-if="isGuest">• Your digital items download link has been sent to your email</li>
                            <li v-else>• Your digital items are now available in your orders</li>
                            <li>• You can access your purchased items anytime</li>
                            <li>• A confirmation email has been sent to your address</li>
                        </ul>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <Link
                            v-if="!isGuest"
                            :href="route('orders.show', order.id)"
                            class="inline-flex items-center justify-center px-6 py-3 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition-colors font-medium"
                        >
                            View Order Details
                        </Link>
                        <Link
                            :href="route('shop.index')"
                            class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors font-medium"
                        >
                            Continue Shopping
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </component>
</template>
