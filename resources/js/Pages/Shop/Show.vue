<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    product: Object,
    relatedProducts: Array,
});

const quantity = ref(1);

const addToCart = () => {
    router.post(route('cart.add'), {
        product_id: props.product.id,
        quantity: quantity.value,
    });
};

const increaseQty = () => {
    if (quantity.value < props.product.available_items_count) {
        quantity.value++;
    }
};

const decreaseQty = () => {
    if (quantity.value > 1) {
        quantity.value--;
    }
};

const breadcrumbs = () => {
    const crumbs = [{ name: 'Shop', href: route('shop.index') }];
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
                <nav class="mb-6">
                    <ol class="flex items-center space-x-2 text-sm">
                        <template v-for="(crumb, index) in breadcrumbs()" :key="crumb.name">
                            <li v-if="index > 0" class="text-gray-300 dark:text-gray-600">/</li>
                            <li>
                                <Link :href="crumb.href" class="text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                    {{ crumb.name }}
                                </Link>
                            </li>
                        </template>
                        <li class="text-gray-300 dark:text-gray-600">/</li>
                        <li class="text-gray-900 dark:text-white font-medium">{{ product.name }}</li>
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
                                    {{ product.available_items_count > 0 ? `${product.available_items_count} in stock` : 'Out of stock' }}
                                </span>
                            </div>

                            <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white mb-2">
                                {{ product.name }}
                            </h1>

                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                                Sold by
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
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Description</h3>
                                <p class="text-gray-600 dark:text-gray-400 text-sm whitespace-pre-wrap">{{ product.description }}</p>
                            </div>

                            <!-- Add to Cart -->
                            <div v-if="product.available_items_count > 0" class="space-y-4">
                                <div class="flex items-center space-x-4">
                                    <div class="flex items-center border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden">
                                        <button
                                            @click="decreaseQty"
                                            class="px-4 py-2.5 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors disabled:opacity-50"
                                            :disabled="quantity <= 1"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                            </svg>
                                        </button>
                                        <span class="px-4 py-2.5 font-medium text-gray-900 dark:text-white border-x border-gray-200 dark:border-gray-600">
                                            {{ quantity }}
                                        </span>
                                        <button
                                            @click="increaseQty"
                                            class="px-4 py-2.5 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors disabled:opacity-50"
                                            :disabled="quantity >= product.available_items_count"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                        </button>
                                    </div>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                        Total: <span class="font-semibold text-gray-900 dark:text-white">${{ (parseFloat(product.price) * quantity).toFixed(2) }}</span>
                                    </span>
                                </div>

                                <button
                                    @click="addToCart"
                                    class="w-full flex items-center justify-center px-6 py-3 text-base font-medium text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 transition-colors"
                                >
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                    </svg>
                                    Add to Cart
                                </button>
                            </div>

                            <div v-else>
                                <button
                                    disabled
                                    class="w-full px-6 py-3 text-base font-medium text-gray-500 bg-gray-100 dark:bg-gray-700 dark:text-gray-400 rounded-xl cursor-not-allowed"
                                >
                                    Out of Stock
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Related Products -->
                <div v-if="relatedProducts.length > 0" class="mt-12">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Related Products</h2>
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
                                <h3 class="font-medium text-sm text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors line-clamp-1">
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
                        Back to Shop
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
