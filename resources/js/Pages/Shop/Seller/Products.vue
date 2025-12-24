<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    seller: Object,
    products: Object,
    filters: Object,
});

const sort = ref(props.filters?.sort || '');

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);
};

watch(sort, (newSort) => {
    router.get(route('shop.seller.products', props.seller.id), {
        sort: newSort || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
});
</script>

<template>
    <Head :title="`${seller.name}'s Products`" />

    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center space-x-4">
                    <Link
                        :href="route('shop.seller', seller.id)"
                        class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ seller.name }}'s Products</h1>
                        <p class="text-gray-500 dark:text-gray-400 mt-1">{{ products.total }} products found</p>
                    </div>
                </div>

                <!-- Sort -->
                <select
                    v-model="sort"
                    class="px-4 py-2.5 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                >
                    <option value="">Newest First</option>
                    <option value="price_low">Price: Low to High</option>
                    <option value="price_high">Price: High to Low</option>
                </select>
            </div>

            <!-- Products Grid -->
            <div v-if="products.data.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <Link
                    v-for="product in products.data"
                    :key="product.id"
                    :href="route('shop.product', product.id)"
                    class="group bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300"
                >
                    <div class="aspect-square bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 flex items-center justify-center">
                        <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                    </div>
                    <div class="p-5">
                        <p v-if="product.category" class="text-xs text-indigo-600 dark:text-indigo-400 font-medium mb-2">
                            {{ product.category.name }}
                        </p>
                        <h3 class="font-semibold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors line-clamp-2 mb-3">
                            {{ product.name }}
                        </h3>
                        <div class="flex items-center justify-between">
                            <p class="text-xl font-bold text-gray-900 dark:text-white">
                                {{ formatCurrency(product.price) }}
                            </p>
                            <span
                                class="text-xs font-medium px-2.5 py-1 rounded-full"
                                :class="product.available_items_count > 0
                                    ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
                                    : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'"
                            >
                                {{ product.available_items_count > 0 ? `${product.available_items_count} in stock` : 'Sold out' }}
                            </span>
                        </div>
                    </div>
                </Link>
            </div>

            <!-- Empty State -->
            <div v-else class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-12 text-center">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Products Found</h3>
                <p class="text-gray-500 dark:text-gray-400">This seller hasn't listed any products yet.</p>
            </div>

            <!-- Pagination -->
            <div v-if="products.links && products.links.length > 3" class="flex justify-center mt-8">
                <nav class="flex items-center space-x-1">
                    <template v-for="(link, index) in products.links" :key="index">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="px-4 py-2 text-sm rounded-lg transition-colors"
                            :class="link.active
                                ? 'bg-indigo-600 text-white'
                                : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'"
                            v-html="link.label"
                        />
                        <span
                            v-else
                            class="px-4 py-2 text-sm text-gray-400 dark:text-gray-600"
                            v-html="link.label"
                        />
                    </template>
                </nav>
            </div>
        </div>
    </AppLayout>
</template>
