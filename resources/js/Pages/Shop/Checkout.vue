<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    cartItems: Array,
    total: Number,
});

const processCheckout = () => {
    router.post(route('checkout.process'));
};
</script>

<template>
    <Head title="Checkout" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Checkout
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Order Items -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-lg shadow overflow-hidden">
                            <div class="p-6 border-b">
                                <h3 class="text-lg font-semibold text-gray-900">Order Items</h3>
                            </div>
                            <div class="divide-y divide-gray-200">
                                <div
                                    v-for="item in cartItems"
                                    :key="item.product.id"
                                    class="p-6 flex items-center space-x-4"
                                >
                                    <div class="w-16 h-16 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <span class="text-2xl text-indigo-300">📦</span>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-900">{{ item.product.name }}</p>
                                        <p class="text-sm text-gray-500">Qty: {{ item.quantity }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-medium text-gray-900">${{ item.subtotal.toFixed(2) }}</p>
                                        <p class="text-sm text-gray-500">${{ parseFloat(item.product.price).toFixed(2) }} each</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Info Box -->
                        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h4 class="font-medium text-blue-900">How it works</h4>
                            <ul class="mt-2 text-sm text-blue-800 space-y-1">
                                <li>1. Click "Place Order" to reserve your items</li>
                                <li>2. Complete payment on the next page</li>
                                <li>3. Access your digital items immediately after payment</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-lg shadow p-6 sticky top-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Summary</h3>

                            <div class="space-y-3">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Subtotal</span>
                                    <span class="text-gray-900">${{ total.toFixed(2) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Items</span>
                                    <span class="text-gray-900">{{ cartItems.reduce((sum, item) => sum + item.quantity, 0) }}</span>
                                </div>
                                <div class="border-t pt-3 flex justify-between">
                                    <span class="text-lg font-semibold text-gray-900">Total</span>
                                    <span class="text-lg font-bold text-gray-900">${{ total.toFixed(2) }}</span>
                                </div>
                            </div>

                            <PrimaryButton
                                @click="processCheckout"
                                class="mt-6 w-full justify-center py-3"
                            >
                                Place Order
                            </PrimaryButton>

                            <Link
                                :href="route('cart.index')"
                                class="mt-4 block text-center text-sm text-gray-600 hover:text-gray-900"
                            >
                                ← Back to Cart
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
