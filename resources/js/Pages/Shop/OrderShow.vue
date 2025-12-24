<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    order: Object,
});

const downloadingItem = ref(null);
const downloadingAll = ref(false);
const copiedItemId = ref(null);

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

const copyToClipboard = async (text, itemId) => {
    await navigator.clipboard.writeText(text);
    copiedItemId.value = itemId;
    setTimeout(() => {
        copiedItemId.value = null;
    }, 2000);
};

const downloadItem = async (item) => {
    downloadingItem.value = item.id;
    try {
        const response = await fetch(route('download.link', { order: props.order.id, orderItem: item.id }));
        const data = await response.json();
        if (data.download_url) {
            window.location.href = data.download_url;
        }
    } catch (error) {
        console.error('Download failed:', error);
    } finally {
        setTimeout(() => {
            downloadingItem.value = null;
        }, 1000);
    }
};

const downloadAll = async () => {
    downloadingAll.value = true;
    try {
        const response = await fetch(route('download.all.link', { order: props.order.id }));
        const data = await response.json();
        if (data.download_url) {
            window.location.href = data.download_url;
        }
    } catch (error) {
        console.error('Download failed:', error);
    } finally {
        setTimeout(() => {
            downloadingAll.value = false;
        }, 1000);
    }
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
                            <div class="p-6 border-b flex justify-between items-center">
                                <h3 class="text-lg font-semibold text-gray-900">Order Items</h3>
                                <button
                                    v-if="order.status === 'completed'"
                                    @click="downloadAll"
                                    :disabled="downloadingAll"
                                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50"
                                >
                                    <svg v-if="!downloadingAll" class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    <svg v-else class="w-4 h-4 mr-1.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ downloadingAll ? 'Downloading...' : 'Download All' }}
                                </button>
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
                                                    <div class="flex items-center space-x-2">
                                                        <button
                                                            @click="copyToClipboard(item.product_item.content, item.id)"
                                                            class="text-sm text-indigo-600 hover:text-indigo-800"
                                                        >
                                                            {{ copiedItemId === item.id ? 'Copied!' : 'Copy' }}
                                                        </button>
                                                        <button
                                                            @click="downloadItem(item)"
                                                            :disabled="downloadingItem === item.id"
                                                            class="inline-flex items-center text-sm text-green-600 hover:text-green-800 disabled:opacity-50"
                                                        >
                                                            <svg v-if="downloadingItem !== item.id" class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                            </svg>
                                                            <svg v-else class="w-4 h-4 mr-1 animate-spin" fill="none" viewBox="0 0 24 24">
                                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                                            </svg>
                                                            Download
                                                        </button>
                                                    </div>
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
