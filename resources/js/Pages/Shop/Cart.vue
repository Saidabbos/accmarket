<script setup>
import { Head, Link, router } from '@inertiajs/vue3';

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

    <div class="min-h-screen bg-gray-100">
        <!-- Header -->
        <header class="bg-white shadow">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center">
                    <h1 class="text-3xl font-bold text-gray-900">Shopping Cart</h1>
                    <div class="flex items-center space-x-4">
                        <Link :href="route('shop.index')" class="text-gray-600 hover:text-gray-900">
                            Continue Shopping
                        </Link>
                        <Link v-if="$page.props.auth?.user" :href="route('dashboard')" class="text-gray-600 hover:text-gray-900">
                            Dashboard
                        </Link>
                    </div>
                </div>
            </div>
        </header>

        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div v-if="cartItems.length > 0" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Cart Items -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="divide-y divide-gray-200">
                            <div
                                v-for="item in cartItems"
                                :key="item.product.id"
                                class="p-6 flex items-center space-x-4"
                            >
                                <!-- Product Image -->
                                <div class="w-24 h-24 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <span class="text-3xl text-indigo-300">📦</span>
                                </div>

                                <!-- Product Info -->
                                <div class="flex-1">
                                    <Link :href="route('shop.product', item.product.id)" class="text-lg font-semibold text-gray-900 hover:text-indigo-600">
                                        {{ item.product.name }}
                                    </Link>
                                    <p class="text-sm text-gray-500">{{ item.product.category?.name }}</p>
                                    <p class="text-lg font-bold text-gray-900 mt-1">
                                        ${{ parseFloat(item.product.price).toFixed(2) }}
                                    </p>
                                </div>

                                <!-- Quantity -->
                                <div class="flex items-center space-x-2">
                                    <button
                                        @click="updateQuantity(item.product.id, item.quantity - 1)"
                                        class="w-8 h-8 rounded-full border flex items-center justify-center hover:bg-gray-100"
                                        :disabled="item.quantity <= 1"
                                    >
                                        -
                                    </button>
                                    <span class="w-12 text-center">{{ item.quantity }}</span>
                                    <button
                                        @click="updateQuantity(item.product.id, item.quantity + 1)"
                                        class="w-8 h-8 rounded-full border flex items-center justify-center hover:bg-gray-100"
                                        :disabled="item.quantity >= item.product.available_items_count"
                                    >
                                        +
                                    </button>
                                </div>

                                <!-- Subtotal -->
                                <div class="text-right">
                                    <p class="text-lg font-bold text-gray-900">
                                        ${{ item.subtotal.toFixed(2) }}
                                    </p>
                                    <button
                                        @click="removeItem(item.product.id)"
                                        class="text-sm text-red-600 hover:text-red-800 mt-1"
                                    >
                                        Remove
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 bg-gray-50 flex justify-between items-center">
                            <button
                                @click="clearCart"
                                class="text-red-600 hover:text-red-800"
                            >
                                Clear Cart
                            </button>
                            <Link :href="route('shop.index')" class="text-indigo-600 hover:text-indigo-800">
                                Continue Shopping
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow p-6 sticky top-8">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Order Summary</h2>

                        <div class="space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Items ({{ cartItems.reduce((sum, item) => sum + item.quantity, 0) }})</span>
                                <span class="text-gray-900">${{ total.toFixed(2) }}</span>
                            </div>
                            <div class="border-t pt-3 flex justify-between">
                                <span class="text-lg font-semibold text-gray-900">Total</span>
                                <span class="text-lg font-bold text-gray-900">${{ total.toFixed(2) }}</span>
                            </div>
                        </div>

                        <Link
                            v-if="$page.props.auth?.user"
                            :href="route('checkout.index')"
                            class="mt-6 block w-full bg-indigo-600 text-white text-center py-3 px-4 rounded-md hover:bg-indigo-700 transition-colors font-medium"
                        >
                            Proceed to Checkout
                        </Link>
                        <div v-else class="mt-6 space-y-3">
                            <Link
                                :href="route('login')"
                                class="block w-full bg-indigo-600 text-white text-center py-3 px-4 rounded-md hover:bg-indigo-700 transition-colors font-medium"
                            >
                                Login to Checkout
                            </Link>
                            <p class="text-sm text-center text-gray-500">
                                Don't have an account?
                                <Link :href="route('register')" class="text-indigo-600 hover:text-indigo-800">Register</Link>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty Cart -->
            <div v-else class="bg-white rounded-lg shadow p-12 text-center">
                <div class="text-6xl mb-4">🛒</div>
                <h2 class="text-2xl font-semibold text-gray-900 mb-2">Your cart is empty</h2>
                <p class="text-gray-500 mb-6">Looks like you haven't added any products yet.</p>
                <Link
                    :href="route('shop.index')"
                    class="inline-block bg-indigo-600 text-white py-3 px-6 rounded-md hover:bg-indigo-700 transition-colors font-medium"
                >
                    Start Shopping
                </Link>
            </div>
        </div>
    </div>
</template>
