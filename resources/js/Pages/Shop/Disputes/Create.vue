<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    order: Object,
});

const form = useForm({
    subject: '',
    description: '',
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
    });
};

const submit = () => {
    form.post(route('disputes.store', props.order.id));
};
</script>

<template>
    <Head :title="`File Dispute - Order #${order.order_number}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center space-x-4">
                <Link
                    :href="route('orders.show', order.id)"
                    class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </Link>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    File a Dispute
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <!-- Order Summary -->
                    <div class="p-6 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-3">Order Details</h3>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-semibold text-gray-900 dark:text-white">Order #{{ order.order_number }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ formatDate(order.created_at) }}</p>
                            </div>
                            <p class="text-lg font-bold text-gray-900 dark:text-white">{{ formatCurrency(order.total_amount) }}</p>
                        </div>

                        <!-- Order Items -->
                        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Items in this order:</p>
                            <div class="space-y-2">
                                <div
                                    v-for="item in order.items"
                                    :key="item.id"
                                    class="flex items-center justify-between text-sm"
                                >
                                    <span class="text-gray-700 dark:text-gray-300">
                                        {{ item.product_item?.product?.name || 'Product' }}
                                    </span>
                                    <span class="text-gray-900 dark:text-white font-medium">
                                        {{ formatCurrency(item.price) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dispute Form -->
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <div>
                            <label for="subject" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Subject *
                            </label>
                            <input
                                id="subject"
                                v-model="form.subject"
                                type="text"
                                placeholder="Brief summary of the issue"
                                class="w-full px-4 py-3 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors"
                                required
                            />
                            <p v-if="form.errors.subject" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.subject }}
                            </p>
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Description *
                            </label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="6"
                                placeholder="Please describe your issue in detail. Include any relevant information that will help us resolve your dispute."
                                class="w-full px-4 py-3 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors resize-none"
                                required
                            ></textarea>
                            <p v-if="form.errors.description" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.description }}
                            </p>
                        </div>

                        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4">
                            <div class="flex">
                                <svg class="w-5 h-5 text-amber-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <div>
                                    <h4 class="text-sm font-medium text-amber-800 dark:text-amber-300">Important</h4>
                                    <p class="mt-1 text-sm text-amber-700 dark:text-amber-400">
                                        Our team will review your dispute within 24-48 hours. You'll receive updates via email.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end space-x-4 pt-4">
                            <Link
                                :href="route('orders.show', order.id)"
                                class="px-6 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-6 py-3 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                            >
                                <span v-if="form.processing">Submitting...</span>
                                <span v-else>Submit Dispute</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
