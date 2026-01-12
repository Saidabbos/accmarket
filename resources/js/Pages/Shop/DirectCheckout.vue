<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useTranslations } from '@/composables/useTranslations';

const { t } = useTranslations();

const props = defineProps({
    product: Object,
    quantity: Number,
    total: Number,
});

const isProcessing = ref(false);

const processCheckout = () => {
    if (isProcessing.value) return;

    isProcessing.value = true;

    router.post(route('checkout.direct.process'), {}, {
        onFinish: () => {
            isProcessing.value = false;
        },
    });
};
</script>

<template>
    <Head :title="t('checkout.title')" />

    <AppLayout>
        <div class="py-8">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">{{ t('checkout.title') }}</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">{{ t('checkout.order_summary') }}</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Order Items -->
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                            <div class="p-5 border-b border-gray-100 dark:border-gray-700">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ t('buy.quantity') }}: {{ quantity }}</h3>
                            </div>
                            <div class="p-5 flex items-center space-x-4">
                                <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 flex items-center justify-center flex-shrink-0">
                                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900 dark:text-white">{{ product.name }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ product.category?.name }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-gray-900 dark:text-white">${{ total.toFixed(2) }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">${{ parseFloat(product.price).toFixed(2) }} x {{ quantity }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Info Box -->
                        <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800 rounded-xl p-5">
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-medium text-indigo-900 dark:text-indigo-300">{{ t('checkout.how_it_works') }}</h4>
                                    <ul class="mt-2 text-sm text-indigo-800 dark:text-indigo-400 space-y-1">
                                        <li class="flex items-center">
                                            <span class="w-5 h-5 rounded-full bg-indigo-600 text-white text-xs flex items-center justify-center mr-2">1</span>
                                            {{ t('checkout.step_1') }}
                                        </li>
                                        <li class="flex items-center">
                                            <span class="w-5 h-5 rounded-full bg-indigo-600 text-white text-xs flex items-center justify-center mr-2">2</span>
                                            {{ t('checkout.step_2') }}
                                        </li>
                                        <li class="flex items-center">
                                            <span class="w-5 h-5 rounded-full bg-indigo-600 text-white text-xs flex items-center justify-center mr-2">3</span>
                                            {{ t('checkout.step_3') }}
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="lg:col-span-1">
                        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5 sticky top-24">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ t('checkout.order_summary') }}</h3>

                            <div class="space-y-3">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">{{ t('buy.price_per_item') }}</span>
                                    <span class="text-gray-900 dark:text-white font-medium">${{ parseFloat(product.price).toFixed(2) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">{{ t('buy.quantity') }}</span>
                                    <span class="text-gray-900 dark:text-white font-medium">{{ quantity }}</span>
                                </div>
                                <div class="border-t border-gray-100 dark:border-gray-700 pt-3 flex justify-between">
                                    <span class="text-base font-semibold text-gray-900 dark:text-white">{{ t('buy.total') }}</span>
                                    <span class="text-xl font-bold text-indigo-600 dark:text-indigo-400">${{ total.toFixed(2) }}</span>
                                </div>
                            </div>

                            <button
                                @click="processCheckout"
                                :disabled="isProcessing"
                                class="mt-6 w-full flex items-center justify-center px-6 py-3 text-base font-medium text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                            >
                                <svg v-if="isProcessing" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <svg v-else class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                {{ isProcessing ? t('buy.processing') : t('checkout.place_order') }}
                            </button>

                            <Link
                                :href="route('shop.product', product.id)"
                                class="mt-4 block text-center text-sm text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors"
                            >
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                                {{ t('common.back') }}
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
