<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    order: Object,
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

// Group order items by product
const groupedItems = computed(() => {
    const groups = {};
    props.order.items?.forEach(item => {
        const productId = item.product_item?.product?.id || item.id;
        const productName = item.product_item?.product?.name || 'Product';

        if (!groups[productId]) {
            groups[productId] = {
                productId,
                productName,
                quantity: 0,
                totalPrice: 0,
                unitPrice: parseFloat(item.price),
                items: [],
            };
        }
        groups[productId].quantity += item.quantity || 1;
        groups[productId].totalPrice += parseFloat(item.price) * (item.quantity || 1);
        groups[productId].items.push(item);
    });
    return Object.values(groups);
});

const getStatusColor = (status) => {
    const colors = {
        pending: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        processing: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        completed: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
        paid: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
        failed: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
        cancelled: 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-400',
        available: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
        sold: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        reserved: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
    };
    return colors[status] || colors.pending;
};

const updateStatus = (newStatus) => {
    router.patch(route('admin.orders.status', props.order.id), {
        status: newStatus,
    }, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`Order #${order.order_number} - Admin`" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Link
                        :href="route('admin.orders.index')"
                        class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Order #{{ order.order_number }}</h1>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ formatDate(order.created_at) }}</p>
                    </div>
                </div>
            </div>
        </template>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Order Status -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Order Status</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Payment Status</p>
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                :class="getStatusColor(order.payment_status)"
                            >
                                {{ order.payment_status }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Order Status</p>
                            <div class="relative inline-block">
                                <select
                                    :value="order.status"
                                    @change="updateStatus($event.target.value)"
                                    class="appearance-none text-sm font-medium px-3 py-1.5 pr-8 rounded-full border-0 cursor-pointer focus:ring-2 focus:ring-indigo-500"
                                    :class="getStatusColor(order.status)"
                                >
                                    <option value="pending">Pending</option>
                                    <option value="processing">Processing</option>
                                    <option value="completed">Completed</option>
                                    <option value="failed">Failed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                                <svg class="absolute right-2 top-1/2 -translate-y-1/2 w-4 h-4 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div v-if="order.payment_method" class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Payment Method</p>
                        <p class="font-medium text-gray-900 dark:text-white">{{ order.payment_method }}</p>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700">
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Order Items</h3>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        <div
                            v-for="group in groupedItems"
                            :key="group.productId"
                            class="p-4 flex items-center justify-between"
                        >
                            <div class="flex items-center space-x-3 min-w-0 flex-1">
                                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                        {{ group.productName }}
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ group.quantity }} × {{ formatCurrency(group.unitPrice) }}
                                    </p>
                                </div>
                            </div>
                            <div class="ml-4 flex-shrink-0 text-right">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ formatCurrency(group.totalPrice) }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="p-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700 flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-900 dark:text-white">Total</span>
                        <span class="text-lg font-bold text-gray-900 dark:text-white">
                            {{ formatCurrency(order.total_amount) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Order Summary -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Order Summary</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Order Number</span>
                            <span class="font-medium text-gray-900 dark:text-white">#{{ order.order_number }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Items</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ order.items?.length || 0 }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Created</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ formatDate(order.created_at) }}</span>
                        </div>
                        <div class="border-t border-gray-100 dark:border-gray-700 pt-3 flex justify-between">
                            <span class="text-base font-semibold text-gray-900 dark:text-white">Total</span>
                            <span class="text-xl font-bold text-gray-900 dark:text-white">
                                {{ formatCurrency(order.total_amount) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Customer Info -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Customer</h3>
                    <div v-if="order.buyer" class="flex items-center space-x-4">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                            <span class="text-white font-medium">{{ order.buyer.name.charAt(0).toUpperCase() }}</span>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">{{ order.buyer.name }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ order.buyer.email }}</p>
                        </div>
                    </div>
                    <div v-else class="text-gray-500 dark:text-gray-400">
                        Guest checkout
                    </div>

                    <div v-if="order.buyer" class="mt-4">
                        <Link
                            :href="route('admin.users.show', order.buyer.id)"
                            class="inline-flex items-center text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300"
                        >
                            View Customer Profile
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </Link>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Actions</h3>
                    <div class="space-y-2">
                        <button
                            v-if="order.status === 'pending'"
                            @click="updateStatus('completed')"
                            class="w-full flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Mark as Completed
                        </button>
                        <button
                            v-if="order.status !== 'cancelled'"
                            @click="updateStatus('cancelled')"
                            class="w-full flex items-center justify-center px-4 py-2 text-sm font-medium text-red-700 dark:text-red-300 bg-red-100 dark:bg-red-900/30 rounded-lg hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Cancel Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
