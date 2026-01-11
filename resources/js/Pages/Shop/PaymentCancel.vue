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
});

const LayoutComponent = props.isGuest ? AppLayout : AuthenticatedLayout;
</script>

<template>
    <Head title="Payment Cancelled" />

    <component :is="LayoutComponent">
        <div class="py-8 lg:py-12">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden text-center p-8">
                    <!-- Warning Icon -->
                    <div class="w-20 h-20 bg-yellow-100 dark:bg-yellow-900/30 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>

                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Payment Cancelled</h1>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        Your payment for order #{{ order.order_number }} was cancelled or expired.
                    </p>

                    <!-- Info Box -->
                    <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-4 mb-6 text-left">
                        <h4 class="font-medium text-gray-900 dark:text-white mb-2">What Happened?</h4>
                        <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                            <li>• The payment was cancelled or the payment link expired</li>
                            <li>• Your reserved items have been released</li>
                            <li>• No charges have been made to your account</li>
                        </ul>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <Link
                            :href="route('shop.index')"
                            class="inline-flex items-center justify-center px-6 py-3 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition-colors font-medium"
                        >
                            Continue Shopping
                        </Link>
                    </div>

                    <p class="mt-6 text-sm text-gray-500 dark:text-gray-400">
                        Need help? <a href="#" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">Contact Support</a>
                    </p>
                </div>
            </div>
        </div>
    </component>
</template>
