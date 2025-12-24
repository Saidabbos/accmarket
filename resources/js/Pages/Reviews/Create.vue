<script setup>
import { ref } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    order: Object,
    product: Object,
});

const form = useForm({
    rating: 0,
    comment: '',
});

const hoveredRating = ref(0);

const setRating = (rating) => {
    form.rating = rating;
};

const submit = () => {
    form.post(route('reviews.store', { order: props.order.id, product: props.product.id }), {
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <Head title="Leave a Review" />

    <AppLayout>
        <div class="py-8">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <Link
                        :href="route('orders.show', order.id)"
                        class="inline-flex items-center text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors mb-4"
                    >
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to Order
                    </Link>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        Leave a Review
                    </h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        Share your experience with {{ product.name }}
                    </p>
                </div>

                <!-- Product Info -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-6 mb-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 flex items-center justify-center flex-shrink-0">
                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ product.name }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Order #{{ order.order_number }}</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white mt-1">
                                ${{ parseFloat(product.price).toFixed(2) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Review Form -->
                <form @submit.prevent="submit" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-6">
                    <!-- Rating -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                            Your Rating <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center space-x-2">
                            <button
                                v-for="star in 5"
                                :key="star"
                                type="button"
                                @click="setRating(star)"
                                @mouseenter="hoveredRating = star"
                                @mouseleave="hoveredRating = 0"
                                class="focus:outline-none transition-transform hover:scale-110"
                            >
                                <svg
                                    class="w-10 h-10 transition-colors"
                                    :class="star <= (hoveredRating || form.rating) ? 'text-amber-500 fill-current' : 'text-gray-300 dark:text-gray-600'"
                                    viewBox="0 0 20 20"
                                >
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            </button>
                            <span v-if="form.rating" class="ml-3 text-lg font-medium text-gray-700 dark:text-gray-300">
                                {{ form.rating }} / 5
                            </span>
                        </div>
                        <p v-if="form.errors.rating" class="mt-2 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.rating }}
                        </p>
                    </div>

                    <!-- Comment -->
                    <div class="mb-6">
                        <label for="comment" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Your Review (Optional)
                        </label>
                        <textarea
                            id="comment"
                            v-model="form.comment"
                            rows="5"
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-transparent transition-colors"
                            placeholder="Share details about your experience with this product..."
                            maxlength="1000"
                        ></textarea>
                        <div class="mt-2 flex justify-between items-center">
                            <p v-if="form.errors.comment" class="text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.comment }}
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 ml-auto">
                                {{ form.comment.length }} / 1000
                            </p>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex items-center justify-end space-x-3">
                        <Link
                            :href="route('orders.show', order.id)"
                            class="px-6 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing || form.rating === 0"
                            class="px-6 py-3 text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 rounded-lg transition-all duration-200 shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="form.processing">Submitting...</span>
                            <span v-else>Submit Review</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
