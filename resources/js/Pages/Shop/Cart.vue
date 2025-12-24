<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    cartItems: Array,
    total: Number,
});

const updateQuantity = (productId, quantity) => {
    router.patch(route('cart.update'), {
        product_id: productId,
        quantity: quantity,
    }, {
        preserveScroll: true,
    });
};

const removeItem = (productId) => {
    router.delete(route('cart.remove'), {
        data: { product_id: productId },
        preserveScroll: true,
    });
};

const clearCart = () => {
    router.delete(route('cart.clear'));
};
</script>

<template>
    <Head title="Cart" />

    <AppLayout>
        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">Shopping Cart</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        {{ cartItems.length > 0 ? `${cartItems.reduce((sum, item) => sum + item.quantity, 0)} items in your cart` : 'Your cart is empty' }}
                    </p>
                </div>

                <div v-if="cartItems.length > 0" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Cart Items -->
                    <div class="lg:col-span-2 space-y-4">
                        <div
                            v-for="item in cartItems"
                            :key="item.product.id"
                            class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-4 sm:p-5"
                        >
                            <div class="flex items-start space-x-4">
                                <!-- Product Image -->
                                <Link :href="route('shop.product', item.product.id)" class="flex-shrink-0">
                                    <div class="w-20 h-20 rounded-xl bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 flex items-center justify-center">
                                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                            </svg>
                                        </div>
                                    </div>
                                </Link>

                                <!-- Product Info -->
                                <div class="flex-1 min-w-0">
                                    <Link :href="route('shop.product', item.product.id)" class="block">
                                        <h3 class="font-semibold text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                            {{ item.product.name }}
                                        </h3>
                                    </Link>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ item.product.category?.name }}</p>
                                    <p class="font-semibold text-gray-900 dark:text-white mt-2">
                                        ${{ parseFloat(item.product.price).toFixed(2) }}
                                    </p>
                                </div>

                                <!-- Quantity & Actions -->
                                <div class="flex flex-col items-end space-y-3">
                                    <div class="flex items-center border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden">
                                        <button
                                            @click="updateQuantity(item.product.id, item.quantity - 1)"
                                            class="px-3 py-1.5 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors disabled:opacity-50"
                                            :disabled="item.quantity <= 1"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                            </svg>
                                        </button>
                                        <span class="px-3 py-1.5 font-medium text-sm text-gray-900 dark:text-white border-x border-gray-200 dark:border-gray-600">
                                            {{ item.quantity }}
                                        </span>
                                        <button
                                            @click="updateQuantity(item.product.id, item.quantity + 1)"
                                            class="px-3 py-1.5 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors disabled:opacity-50"
                                            :disabled="item.quantity >= item.product.available_items_count"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                        </button>
                                    </div>

                                    <div class="text-right">
                                        <p class="font-bold text-gray-900 dark:text-white">${{ item.subtotal.toFixed(2) }}</p>
                                        <button
                                            @click="removeItem(item.product.id)"
                                            class="text-xs text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 transition-colors mt-1"
                                        >
                                            Remove
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cart Actions -->
                        <div class="flex items-center justify-between pt-4">
                            <button
                                @click="clearCart"
                                class="text-sm text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 transition-colors"
                            >
                                Clear Cart
                            </button>
                            <Link :href="route('shop.index')" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors">
                                Continue Shopping
                            </Link>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="lg:col-span-1">
                        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5 sticky top-24">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Order Summary</h2>

                            <div class="space-y-3">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">
                                        Items ({{ cartItems.reduce((sum, item) => sum + item.quantity, 0) }})
                                    </span>
                                    <span class="text-gray-900 dark:text-white font-medium">${{ total.toFixed(2) }}</span>
                                </div>
                                <div class="border-t border-gray-100 dark:border-gray-700 pt-3 flex justify-between">
                                    <span class="text-base font-semibold text-gray-900 dark:text-white">Total</span>
                                    <span class="text-xl font-bold text-gray-900 dark:text-white">${{ total.toFixed(2) }}</span>
                                </div>
                            </div>

                            <div class="mt-6">
                                <Link
                                    v-if="$page.props.auth?.user"
                                    :href="route('checkout.index')"
                                    class="block w-full text-center px-6 py-3 text-base font-medium text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 transition-colors"
                                >
                                    Proceed to Checkout
                                </Link>
                                <div v-else class="space-y-3">
                                    <Link
                                        :href="route('login')"
                                        class="block w-full text-center px-6 py-3 text-base font-medium text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 transition-colors"
                                    >
                                        Login to Checkout
                                    </Link>
                                    <p class="text-sm text-center text-gray-500 dark:text-gray-400">
                                        Don't have an account?
                                        <Link :href="route('register')" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 font-medium">
                                            Register
                                        </Link>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty Cart -->
                <div v-else class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-12 text-center">
                    <div class="w-20 h-20 mx-auto mb-6 rounded-2xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Your cart is empty</h2>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Looks like you haven't added any products yet.</p>
                    <Link
                        :href="route('shop.index')"
                        class="inline-flex items-center px-6 py-3 text-base font-medium text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 transition-colors"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        Start Shopping
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
