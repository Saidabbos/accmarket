<script setup>
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    order: Object,
});

const initiatePayment = () => {
    router.post(route('payment.initiate', props.order.id));
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
</script>

<template>
    <Head :title="`Pay Order #${order.order_number}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Complete Payment
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <!-- Order Header -->
                    <div class="p-6 border-b bg-gray-50">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">
                                    Order #{{ order.order_number }}
                                </h3>
                                <p class="text-sm text-gray-500">
                                    {{ formatDate(order.created_at) }}
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                    :class="{
                                        'bg-yellow-100 text-yellow-800': order.payment_status === 'pending',
                                        'bg-blue-100 text-blue-800': order.payment_status === 'processing',
                                        'bg-green-100 text-green-800': order.payment_status === 'paid',
                                        'bg-red-100 text-red-800': order.payment_status === 'failed',
                                    }">
                                    {{ order.payment_status.charAt(0).toUpperCase() + order.payment_status.slice(1) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="p-6 border-b">
                        <h4 class="font-medium text-gray-900 mb-4">Order Items</h4>
                        <div class="space-y-3">
                            <div
                                v-for="item in order.items"
                                :key="item.id"
                                class="flex items-center justify-between py-2"
                            >
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-100 to-purple-100 rounded flex items-center justify-center">
                                        <span class="text-lg">📦</span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">
                                            {{ item.product_item?.product?.name || 'Digital Item' }}
                                        </p>
                                        <p class="text-sm text-gray-500">Qty: {{ item.quantity }}</p>
                                    </div>
                                </div>
                                <p class="font-medium text-gray-900">
                                    ${{ parseFloat(item.price).toFixed(2) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Summary -->
                    <div class="p-6 border-b bg-gray-50">
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-semibold text-gray-900">Total Amount</span>
                            <span class="text-2xl font-bold text-indigo-600">
                                ${{ parseFloat(order.total_amount).toFixed(2) }}
                            </span>
                        </div>
                    </div>

                    <!-- Payment Info -->
                    <div class="p-6 border-b">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h4 class="font-medium text-blue-900 mb-2">Payment Information</h4>
                            <ul class="text-sm text-blue-800 space-y-1">
                                <li>• You will be redirected to NowPayments to complete your payment</li>
                                <li>• Multiple cryptocurrencies are accepted (BTC, ETH, USDT, etc.)</li>
                                <li>• Your items will be available immediately after payment confirmation</li>
                                <li>• Payment links expire after 20 minutes</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="p-6">
                        <PrimaryButton
                            v-if="order.payment_status === 'pending'"
                            @click="initiatePayment"
                            class="w-full justify-center py-3 text-lg"
                        >
                            Pay with Cryptocurrency
                        </PrimaryButton>

                        <a
                            v-else-if="order.payment_url && order.payment_status === 'processing'"
                            :href="order.payment_url"
                            target="_blank"
                            class="block w-full bg-indigo-600 text-white text-center py-3 px-4 rounded-md hover:bg-indigo-700 transition-colors font-medium"
                        >
                            Continue to Payment
                        </a>

                        <div
                            v-else-if="order.payment_status === 'paid'"
                            class="text-center"
                        >
                            <div class="text-green-600 text-4xl mb-2">✓</div>
                            <p class="text-lg font-medium text-green-600">Payment Completed</p>
                        </div>

                        <p class="mt-4 text-center text-sm text-gray-500">
                            Having trouble? Contact support for assistance.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
