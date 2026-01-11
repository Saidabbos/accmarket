<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import BuyModal from '@/Components/BuyModal.vue';
import { useTranslations } from '@/composables/useTranslations';

const { t } = useTranslations();

const props = defineProps({
    product: Object,
    relatedProducts: Array,
});

// Buy modal state
const showBuyModal = ref(false);

const openBuyModal = () => {
    showBuyModal.value = true;
};

const closeBuyModal = () => {
    showBuyModal.value = false;
};

const breadcrumbs = () => {
    const crumbs = [{ name: t('common.shop'), href: route('shop.index') }];
    if (props.product.category?.parent) {
        crumbs.push({
            name: props.product.category.parent.name,
            href: route('shop.index', { category: props.product.category.parent.slug }),
        });
    }
    if (props.product.category) {
        crumbs.push({
            name: props.product.category.name,
            href: route('shop.index', { category: props.product.category.slug }),
        });
    }
    return crumbs;
};
</script>

<template>
    <Head :title="product.name" />

    <AppLayout>
        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Breadcrumbs -->
                <nav class="mb-6 overflow-hidden">
                    <ol class="flex items-center space-x-2 text-sm">
                        <template v-for="(crumb, index) in breadcrumbs()" :key="crumb.name">
                            <li v-if="index > 0" class="text-gray-300 dark:text-gray-600 flex-shrink-0">/</li>
                            <li class="flex-shrink-0">
                                <Link :href="crumb.href" class="text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                    {{ crumb.name }}
                                </Link>
                            </li>
                        </template>
                        <li class="text-gray-300 dark:text-gray-600 flex-shrink-0">/</li>
                        <li class="text-gray-900 dark:text-white font-medium truncate min-w-0" :title="product.name">{{ product.name }}</li>
                    </ol>
                </nav>

                <!-- Product Details -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="grid grid-cols-1 lg:grid-cols-2">
                        <!-- Product Image -->
                        <div class="h-72 lg:h-auto bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 flex items-center justify-center">
                            <div class="w-32 h-32 rounded-3xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                                <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                        </div>

                        <!-- Product Info -->
                        <div class="p-6 lg:p-8">
                            <div class="flex items-center space-x-3 mb-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-400">
                                    {{ product.category?.name }}
                                </span>
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                    :class="product.available_items_count > 0
                                        ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400'
                                        : 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-400'"
                                >
                                    {{ product.available_items_count > 0 ? `${product.available_items_count} ${t('shop.in_stock')}` : t('shop.out_of_stock') }}
                                </span>
                            </div>

                            <h1 class="text-base lg:text-lg font-bold text-gray-900 dark:text-white mb-2 leading-snug">
                                {{ product.name }}
                            </h1>

                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                                {{ t('product.seller') }}:
                                <Link
                                    v-if="product.seller"
                                    :href="route('shop.seller', product.seller.id)"
                                    class="font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300"
                                >
                                    {{ product.seller.name }}
                                </Link>
                                <span v-else class="font-medium text-gray-700 dark:text-gray-300">Unknown</span>
                            </p>

                            <div class="text-3xl font-bold text-gray-900 dark:text-white mb-6">
                                ${{ parseFloat(product.price).toFixed(2) }}
                            </div>

                            <div v-if="product.description" class="mb-6">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">{{ t('product.description') }}</h3>
                                <p class="text-gray-600 dark:text-gray-400 text-sm whitespace-pre-wrap">{{ product.description }}</p>
                            </div>

                            <!-- Buy Button -->
                            <div v-if="product.available_items_count > 0">
                                <button
                                    @click="openBuyModal"
                                    class="w-full flex items-center justify-center px-6 py-3 text-base font-medium text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 transition-colors"
                                >
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    {{ t('shop.buy_now') }}
                                </button>
                            </div>

                            <div v-else>
                                <button
                                    disabled
                                    class="w-full px-6 py-3 text-base font-medium text-gray-500 bg-gray-100 dark:bg-gray-700 dark:text-gray-400 rounded-xl cursor-not-allowed"
                                >
                                    {{ t('shop.out_of_stock') }}
                                </button>
                            </div>

                            <!-- Features -->
                            <div class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-700">
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="flex items-center space-x-2 text-sm text-gray-600 dark:text-gray-400">
                                        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                        <span>{{ t('product.instant_delivery') }}</span>
                                    </div>
                                    <div class="flex items-center space-x-2 text-sm text-gray-600 dark:text-gray-400">
                                        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                        <span>{{ t('product.secure_payment') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Related Products -->
                <div v-if="relatedProducts.length > 0" class="mt-12">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">{{ t('product.related_products') }}</h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        <Link
                            v-for="related in relatedProducts"
                            :key="related.id"
                            :href="route('shop.product', related.id)"
                            class="group bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden hover:shadow-lg hover:border-gray-200 dark:hover:border-gray-600 transition-all"
                        >
                            <div class="h-28 bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 flex items-center justify-center">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center transform group-hover:scale-110 transition-transform">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                </div>
                            </div>
                            <div class="p-3">
                                <h3 class="font-medium text-xs text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors leading-tight">
                                    {{ related.name }}
                                </h3>
                                <div class="mt-2 flex items-center justify-between">
                                    <span class="font-bold text-gray-900 dark:text-white">
                                        ${{ parseFloat(related.price).toFixed(2) }}
                                    </span>
                                    <span class="text-xs text-emerald-600 dark:text-emerald-400">
                                        {{ related.available_items_count }} left
                                    </span>
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>

                <!-- Back Link -->
                <div class="mt-8">
                    <Link :href="route('shop.index')" class="inline-flex items-center text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        {{ t('common.back') }}
                    </Link>
                </div>
            </div>
        </div>

        <!-- Buy Modal -->
        <BuyModal
            :show="showBuyModal"
            :product="product"
            @close="closeBuyModal"
        />
    </AppLayout>
</template>

