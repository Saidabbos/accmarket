<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';

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

    <div class="min-h-screen bg-gray-100">
        <!-- Header -->
        <header class="bg-white shadow">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center">
                    <Link :href="route('shop.index')" class="text-2xl font-bold text-gray-900">Shop</Link>
                    <div class="flex items-center space-x-4">
                        <Link :href="route('cart.index')" class="text-gray-600 hover:text-gray-900">
                            Cart
                        </Link>
                        <Link v-if="$page.props.auth?.user" :href="route('dashboard')" class="text-gray-600 hover:text-gray-900">
                            Dashboard
                        </Link>
                        <template v-else>
                            <Link :href="route('login')" class="text-gray-600 hover:text-gray-900">Login</Link>
                            <Link :href="route('register')" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                                Register
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </header>

        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Breadcrumbs -->
            <nav class="mb-6">
                <ol class="flex items-center space-x-2 text-sm text-gray-500">
                    <template v-for="(crumb, index) in breadcrumbs()" :key="crumb.name">
                        <li v-if="index > 0" class="text-gray-400">/</li>
                        <li>
                            <Link :href="crumb.href" class="hover:text-indigo-600">{{ crumb.name }}</Link>
                        </li>
                    </template>
                    <li class="text-gray-400">/</li>
                    <li class="text-gray-900 font-medium">{{ product.name }}</li>
                </ol>
            </nav>

            <!-- Product Details -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 p-8">
                    <!-- Product Image -->
                    <div class="h-96 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-lg flex items-center justify-center">
                        <span class="text-8xl text-indigo-300">📦</span>
                    </div>

                    <!-- Product Info -->
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">{{ product.name }}</h1>

                        <div class="mt-2 flex items-center space-x-4">
                            <span class="text-sm text-gray-500">
                                Category: {{ product.category?.name }}
                            </span>
                            <span class="text-sm text-gray-500">
                                Seller: {{ product.seller?.name }}
                            </span>
                        </div>

                        <div class="mt-6">
                            <span class="text-4xl font-bold text-gray-900">
                                ${{ parseFloat(product.price).toFixed(2) }}
                            </span>
                        </div>

                        <div class="mt-4">
                            <span
                                :class="product.available_items_count > 0 ? 'text-green-600' : 'text-red-600'"
                                class="text-lg font-medium"
                            >
                                {{ product.available_items_count > 0 ? `${product.available_items_count} in stock` : 'Out of stock' }}
                            </span>
                        </div>

                        <div class="mt-6" v-if="product.description">
                            <h3 class="text-lg font-medium text-gray-900">Description</h3>
                            <p class="mt-2 text-gray-600 whitespace-pre-wrap">{{ product.description }}</p>
                        </div>

                        <!-- Add to Cart -->
                        <div v-if="product.available_items_count > 0" class="mt-8">
                            <div class="flex items-center space-x-4">
                                <div class="flex items-center border rounded-md">
                                    <button
                                        @click="decreaseQty"
                                        class="px-4 py-2 text-gray-600 hover:bg-gray-100"
                                        :disabled="quantity <= 1"
                                    >
                                        -
                                    </button>
                                    <span class="px-4 py-2 border-x">{{ quantity }}</span>
                                    <button
                                        @click="increaseQty"
                                        class="px-4 py-2 text-gray-600 hover:bg-gray-100"
                                        :disabled="quantity >= product.available_items_count"
                                    >
                                        +
                                    </button>
                                </div>
                                <button
                                    @click="addToCart"
                                    class="flex-1 bg-indigo-600 text-white py-3 px-6 rounded-md hover:bg-indigo-700 transition-colors font-medium"
                                >
                                    Add to Cart
                                </button>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">
                                Total: ${{ (parseFloat(product.price) * quantity).toFixed(2) }}
                            </p>
                        </div>

                        <div v-else class="mt-8">
                            <button
                                disabled
                                class="w-full bg-gray-300 text-gray-500 py-3 px-6 rounded-md cursor-not-allowed"
                            >
                                Out of Stock
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Products -->
            <div v-if="relatedProducts.length > 0" class="mt-12">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Related Products</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div
                        v-for="related in relatedProducts"
                        :key="related.id"
                        class="bg-white rounded-lg shadow overflow-hidden hover:shadow-lg transition-shadow"
                    >
                        <Link :href="route('shop.product', related.id)">
                            <div class="h-32 bg-gradient-to-br from-indigo-100 to-purple-100 flex items-center justify-center">
                                <span class="text-3xl text-indigo-300">📦</span>
                            </div>
                            <div class="p-4">
                                <h3 class="text-sm font-semibold text-gray-900 hover:text-indigo-600">
                                    {{ related.name }}
                                </h3>
                                <div class="mt-2 flex items-center justify-between">
                                    <span class="font-bold text-gray-900">
                                        ${{ parseFloat(related.price).toFixed(2) }}
                                    </span>
                                    <span class="text-xs text-green-600">
                                        {{ related.available_items_count }} left
                                    </span>
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Back to Shop -->
            <div class="mt-8">
                <Link :href="route('shop.index')" class="text-indigo-600 hover:text-indigo-800">
                    ← Back to Shop
                </Link>
            </div>
        </div>
    </div>
</template>
