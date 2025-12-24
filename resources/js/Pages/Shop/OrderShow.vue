<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

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
        pending: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        processing: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        paid: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
        completed: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
        failed: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
        cancelled: 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-400',
    };
    return colors[status] || 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-400';
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

    <AppLayout>
        <div class="py-8">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8 flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">
                            Order #{{ order.order_number }}
                        </h1>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">{{ formatDate(order.created_at) }}</p>
                    </div>
                    <Link
                        :href="route('orders.index')"
                        class="inline-flex items-center text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors"
                    >
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to Orders
                    </Link>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Order Details -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Status Card -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Order Status</h3>
                                </div>
                                <div class="flex flex-wrap gap-2">
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
                        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                            <div class="p-5 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Order Items</h3>
                                <button
                                    v-if="order.status === 'completed'"
                                    @click="downloadAll"
                                    :disabled="downloadingAll"
                                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 transition-colors"
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
                            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                                <div
                                    v-for="item in order.items"
                                    :key="item.id"
                                    class="p-5"
                                >
                                    <div class="flex items-start space-x-4">
                                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 flex items-center justify-center flex-shrink-0">
                                            <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="flex-1">
                                            <p class="font-medium text-gray-900 dark:text-white">
                                                {{ item.product_item?.product?.name || 'Digital Item' }}
                                            </p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">Quantity: {{ item.quantity }}</p>
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white mt-1">
                                                ${{ parseFloat(item.price).toFixed(2) }}
                                            </p>

                                            <!-- Show content for completed orders -->
                                            <div
                                                v-if="order.status === 'completed' && item.product_item?.content"
                                                class="mt-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4"
                                            >
                                                <div class="flex justify-between items-center mb-2">
                                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Your Item:</span>
                                                    <div class="flex items-center space-x-3">
                                                        <button
                                                            @click="copyToClipboard(item.product_item.content, item.id)"
                                                            class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors"
                                                        >
                                                            {{ copiedItemId === item.id ? 'Copied!' : 'Copy' }}
                                                        </button>
                                                        <button
                                                            @click="downloadItem(item)"
                                                            :disabled="downloadingItem === item.id"
                                                            class="inline-flex items-center text-sm text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 disabled:opacity-50 transition-colors"
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
                                                <pre class="text-sm text-gray-600 dark:text-gray-400 whitespace-pre-wrap break-all bg-white dark:bg-gray-800 p-3 rounded-lg border border-gray-200 dark:border-gray-600">{{ item.product_item.content }}</pre>
                                            </div>

                                            <div
                                                v-else-if="order.status !== 'completed'"
                                                class="mt-4 text-sm text-gray-500 dark:text-gray-400 italic"
                                            >
                                                Item details will be available after payment is confirmed.
                                            </div>

                                            <!-- Review button for completed orders -->
                                            <div
                                                v-if="order.status === 'completed' && item.product_item?.product"
                                                class="mt-4"
                                            >
                                                <Link
                                                    v-if="!item.has_review"
                                                    :href="route('reviews.create', { order: order.id, product: item.product_item.product.id })"
                                                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 rounded-lg transition-all duration-200 shadow-sm hover:shadow-md"
                                                >
                                                    <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                    </svg>
                                                    Leave a Review
                                                </Link>
                                                <div
                                                    v-else
                                                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700/50 rounded-lg"
                                                >
                                                    <svg class="w-4 h-4 mr-1.5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                    </svg>
                                                    Review Submitted
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary Sidebar -->
                    <div class="lg:col-span-1">
                        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5 sticky top-24">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Order Summary</h3>

                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Order Number</span>
                                    <span class="font-medium text-gray-900 dark:text-white">#{{ order.order_number }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Items</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ order.items?.length || 0 }}</span>
                                </div>
                                <div v-if="order.payment_method" class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Payment Method</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ order.payment_method }}</span>
                                </div>
                                <div class="border-t border-gray-100 dark:border-gray-700 pt-3 flex justify-between">
                                    <span class="text-base font-semibold text-gray-900 dark:text-white">Total</span>
                                    <span class="text-xl font-bold text-gray-900 dark:text-white">
                                        ${{ parseFloat(order.total_amount).toFixed(2) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Action buttons -->
                            <div class="mt-6 space-y-3">
                                <Link
                                    v-if="order.payment_status === 'pending'"
                                    :href="route('payment.show', order.id)"
                                    class="block w-full text-center px-6 py-3 text-base font-medium text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 transition-colors"
                                >
                                    Complete Payment
                                </Link>

                                <a
                                    v-else-if="order.payment_url && order.payment_status === 'processing'"
                                    :href="order.payment_url"
                                    target="_blank"
                                    class="block w-full text-center px-6 py-3 text-base font-medium text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 transition-colors"
                                >
                                    Continue Payment
                                </a>

                                <Link
                                    :href="route('shop.index')"
                                    class="block w-full text-center text-sm text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 py-2 transition-colors"
                                >
                                    Continue Shopping
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
