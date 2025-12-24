<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    order: Object,
});

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
        pending: 'bg-yellow-100 text-yellow-800',
        processing: 'bg-blue-100 text-blue-800',
        paid: 'bg-green-100 text-green-800',
        completed: 'bg-green-100 text-green-800',
        failed: 'bg-red-100 text-red-800',
        cancelled: 'bg-gray-100 text-gray-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const copyToClipboard = (text) => {
    navigator.clipboard.writeText(text);
};
</script>

<template>
    <Head :title="`Order #${order.order_number}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Order #{{ order.order_number }}
                </h2>
                <Link
                    :href="route('orders.index')"
                    class="text-indigo-600 hover:text-indigo-900 text-sm"
                >
                    &larr; Back to Orders
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Order Details -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Status Card -->
                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Order Status</h3>
                                    <p class="text-sm text-gray-500 mt-1">{{ formatDate(order.created_at) }}</p>
                                </div>
                                <div class="flex space-x-2">
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                        :class="getStatusColor(order.payment_status)"
                                    >
                                        Payment: {{ order.payment_status }}
                                    </span>
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                        :class="getStatusColor(order.status)"
                                    >
                                        {{ order.status }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Order Items -->
                        <div class="bg-white rounded-lg shadow overflow-hidden">
                            <div class="p-6 border-b">
                                <h3 class="text-lg font-semibold text-gray-900">Order Items</h3>
                            </div>
                            <div class="divide-y divide-gray-200">
                                <div
                                    v-for="item in order.items"
                                    :key="item.id"
                                    class="p-6"
                                >
                                    <div class="flex items-start space-x-4">
                                        <div class="w-12 h-12 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                            <span class="text-xl">📦</span>
                                        </div>
                                        <div class="flex-1">
                                            <p class="font-medium text-gray-900">
                                                {{ item.product_item?.product?.name || 'Digital Item' }}
                                            </p>
                                            <p class="text-sm text-gray-500">Quantity: {{ item.quantity }}</p>
                                            <p class="text-sm font-medium text-gray-900 mt-1">
                                                ${{ parseFloat(item.price).toFixed(2) }}
                                            </p>

                                            <!-- Show content for completed orders -->
                                            <div
                                                v-if="order.status === 'completed' && item.product_item?.content"
                                                class="mt-4 bg-gray-50 rounded-lg p-4"
                                            >
                                                <div class="flex justify-between items-center mb-2">
                                                    <span class="text-sm font-medium text-gray-700">Your Item:</span>
                                                    <button
                                                        @click="copyToClipboard(item.product_item.content)"
                                                        class="text-sm text-indigo-600 hover:text-indigo-800"
                                                    >
                                                        Copy
                                                    </button>
                                                </div>
                                                <pre class="text-sm text-gray-600 whitespace-pre-wrap break-all bg-white p-3 rounded border">{{ item.product_item.content }}</pre>
                                            </div>

                                            <div
                                                v-else-if="order.status !== 'completed'"
                                                class="mt-4 text-sm text-gray-500 italic"
                                            >
                                                Item details will be available after payment is confirmed.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary Sidebar -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-lg shadow p-6 sticky top-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Summary</h3>

                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Order Number</span>
                                    <span class="font-medium">#{{ order.order_number }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Items</span>
                                    <span class="font-medium">{{ order.items?.length || 0 }}</span>
                                </div>
                                <div v-if="order.payment_method" class="flex justify-between">
                                    <span class="text-gray-600">Payment Method</span>
                                    <span class="font-medium">{{ order.payment_method }}</span>
                                </div>
                                <div class="border-t pt-3 flex justify-between">
                                    <span class="text-lg font-semibold text-gray-900">Total</span>
                                    <span class="text-lg font-bold text-gray-900">
                                        ${{ parseFloat(order.total_amount).toFixed(2) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Action buttons -->
                            <div class="mt-6 space-y-3">
                                <Link
                                    v-if="order.payment_status === 'pending'"
                                    :href="route('payment.show', order.id)"
                                    class="block w-full bg-indigo-600 text-white text-center py-3 px-4 rounded-md hover:bg-indigo-700 transition-colors font-medium"
                                >
                                    Complete Payment
                                </Link>

                                <a
                                    v-else-if="order.payment_url && order.payment_status === 'processing'"
                                    :href="order.payment_url"
                                    target="_blank"
                                    class="block w-full bg-indigo-600 text-white text-center py-3 px-4 rounded-md hover:bg-indigo-700 transition-colors font-medium"
                                >
                                    Continue Payment
                                </a>

                                <Link
                                    :href="route('shop.index')"
                                    class="block w-full text-center text-gray-600 hover:text-gray-900 py-2"
                                >
                                    Continue Shopping
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
