<script setup>
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

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

const initiatePayment = () => {
    if (props.isGuest) {
        router.post(route('payment.guest.initiate', props.order.id), {
            token: props.guestToken,
        });
    } else {
        router.post(route('payment.initiate', props.order.id));
    }
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

// Choose layout based on guest status
const LayoutComponent = props.isGuest ? AppLayout : AuthenticatedLayout;
</script>

<template>
    <Head :title="`Pay Order #${order.order_number}`" />

    <component :is="LayoutComponent">
        <template #header v-if="!isGuest">
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">
                Complete Payment
            </h2>
        </template>

        <div class="py-8 lg:py-12">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <!-- Guest Header -->
                <div v-if="isGuest" class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Complete Payment</h1>
                    <p class="mt-1 text-gray-600 dark:text-gray-400">Complete your order payment below</p>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <!-- Order Header -->
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    Order #{{ order.order_number }}
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ formatDate(order.created_at) }}
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                    :class="{
                                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-400': order.payment_status === 'pending',
                                        'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-400': order.payment_status === 'processing',
                                        'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-400': order.payment_status === 'paid',
                                        'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-400': order.payment_status === 'failed',
                                    }">
                                    {{ order.payment_status.charAt(0).toUpperCase() + order.payment_status.slice(1) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                        <h4 class="font-medium text-gray-900 dark:text-white mb-4">Order Items</h4>
                        <div class="space-y-3">
                            <div
                                v-for="item in order.items"
                                :key="item.id"
                                class="flex items-center justify-between py-2"
                            >
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-100 to-purple-100 dark:from-indigo-900/30 dark:to-purple-900/30 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            {{ item.product_item?.product?.name || 'Digital Item' }}
                                        </p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Qty: {{ item.quantity }}</p>
                                    </div>
                                </div>
                                <p class="font-medium text-gray-900 dark:text-white">
                                    ${{ parseFloat(item.price).toFixed(2) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Summary -->
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-semibold text-gray-900 dark:text-white">Total Amount</span>
                            <span class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                                ${{ parseFloat(order.total_amount).toFixed(2) }}
                            </span>
                        </div>
                    </div>

                    <!-- Payment Info -->
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
                            <h4 class="font-medium text-blue-900 dark:text-blue-300 mb-2">Payment Information</h4>
                            <ul class="text-sm text-blue-800 dark:text-blue-400 space-y-1">
                                <li>• You will be redirected to NowPayments to complete your payment</li>
                                <li>• Multiple cryptocurrencies are accepted (BTC, ETH, USDT, etc.)</li>
                                <li>• Your items will be available immediately after payment confirmation</li>
                                <li>• Payment links expire after 20 minutes</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="p-6">
                        <button
                            v-if="order.payment_status === 'pending'"
                            @click="initiatePayment"
                            class="w-full flex items-center justify-center px-6 py-3 text-base font-medium text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 transition-colors"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            Pay with Cryptocurrency
                        </button>

                        <a
                            v-else-if="order.payment_url && order.payment_status === 'processing'"
                            :href="order.payment_url"
                            target="_blank"
                            class="w-full flex items-center justify-center px-6 py-3 text-base font-medium text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 transition-colors"
                        >
                            Continue to Payment
                        </a>

                        <div
                            v-else-if="order.payment_status === 'paid'"
                            class="text-center"
                        >
                            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                                <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <p class="text-lg font-medium text-green-600 dark:text-green-400">Payment Completed</p>
                        </div>

                        <p class="mt-4 text-center text-sm text-gray-500 dark:text-gray-400">
                            Having trouble? Contact support for assistance.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </component>
</template>
