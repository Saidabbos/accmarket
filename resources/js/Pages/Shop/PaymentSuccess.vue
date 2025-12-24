<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    order: Object,
});
</script>

<template>
    <Head title="Payment Successful" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="bg-white rounded-lg shadow overflow-hidden text-center p-8">
                    <!-- Success Icon -->
                    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>

                    <h1 class="text-2xl font-bold text-gray-900 mb-2">Payment Successful!</h1>
                    <p class="text-gray-600 mb-6">
                        Thank you for your purchase. Your order #{{ order.order_number }} has been confirmed.
                    </p>

                    <!-- Order Summary -->
                    <div class="bg-gray-50 rounded-lg p-4 mb-6 text-left">
                        <h3 class="font-medium text-gray-900 mb-3">Order Summary</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Order Number</span>
                                <span class="font-medium">#{{ order.order_number }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Items</span>
                                <span class="font-medium">{{ order.items?.length || 0 }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total Paid</span>
                                <span class="font-medium text-green-600">${{ parseFloat(order.total_amount).toFixed(2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Info Box -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 text-left">
                        <h4 class="font-medium text-blue-900 mb-2">What's Next?</h4>
                        <ul class="text-sm text-blue-800 space-y-1">
                            <li>• Your digital items are now available in your orders</li>
                            <li>• You can access your purchased items anytime</li>
                            <li>• A confirmation email has been sent to your address</li>
                        </ul>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <Link
                            :href="route('orders.show', order.id)"
                            class="inline-flex items-center justify-center px-6 py-3 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors font-medium"
                        >
                            View Order Details
                        </Link>
                        <Link
                            :href="route('shop.index')"
                            class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors font-medium"
                        >
                            Continue Shopping
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
