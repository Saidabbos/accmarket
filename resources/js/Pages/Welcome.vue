<script setup>
import { ref, onMounted, watch } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import StarRating from '@/Components/StarRating.vue';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    categoriesWithProducts: Array,
});

const darkMode = ref(false);

onMounted(() => {
    const savedMode = localStorage.getItem('darkMode');
    if (savedMode !== null) {
        darkMode.value = savedMode === 'true';
    } else {
        darkMode.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
    }
    applyDarkMode();
});

watch(darkMode, () => {
    localStorage.setItem('darkMode', darkMode.value.toString());
    applyDarkMode();
});

const applyDarkMode = () => {
    if (darkMode.value) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
};

const toggleDarkMode = () => {
    darkMode.value = !darkMode.value;
};

const features = [
    {
        icon: 'shield',
        title: 'Secure Transactions',
        description: 'All transactions are protected with cryptocurrency payments and secure escrow system.'
    },
    {
        icon: 'lightning',
        title: 'Instant Delivery',
        description: 'Get your digital products instantly after payment confirmation.'
    },
    {
        icon: 'users',
        title: 'Verified Sellers',
        description: 'All sellers go through verification to ensure quality and reliability.'
    },
    {
        icon: 'support',
        title: 'Dispute Resolution',
        description: 'Built-in dispute system to protect both buyers and sellers.'
    },
];
</script>

<template>
    <Head title="Welcome" />

    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-200">
        <!-- Navigation -->
        <nav class="fixed top-0 w-full z-50 bg-white/80 dark:bg-gray-900/80 backdrop-blur-lg border-b border-gray-100 dark:border-gray-800">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <Link href="/" class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-sm">AM</span>
                        </div>
                        <span class="text-lg font-semibold text-gray-900 dark:text-white">AccMarket</span>
                    </Link>

                    <div class="flex items-center space-x-3">
                        <button
                            @click="toggleDarkMode"
                            class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                        >
                            <svg v-if="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                            </svg>
                        </button>

                        <template v-if="canLogin">
                            <Link
                                v-if="$page.props.auth.user"
                                :href="route('dashboard')"
                                class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors"
                            >
                                Dashboard
                            </Link>
                            <template v-else>
                                <Link
                                    :href="route('login')"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors"
                                >
                                    Sign in
                                </Link>
                                <Link
                                    :href="route('login')"
                                    class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors"
                                >
                                    Get started
                                </Link>
                            </template>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="pt-32 pb-20 px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl">
                <div class="text-center">
                    <div class="inline-flex items-center px-3 py-1 rounded-full bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 text-sm font-medium mb-6">
                        <span class="w-2 h-2 rounded-full bg-indigo-600 dark:bg-indigo-400 mr-2 animate-pulse"></span>
                        Trusted by thousands of users
                    </div>
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-gray-900 dark:text-white tracking-tight">
                        Digital Marketplace for
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600">Premium Accounts</span>
                    </h1>
                    <p class="mt-6 text-lg sm:text-xl text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                        Buy and sell digital products securely with cryptocurrency payments.
                        Instant delivery, verified sellers, and buyer protection.
                    </p>
                    <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                        <Link
                            :href="route('shop.index')"
                            class="w-full sm:w-auto px-8 py-3 text-base font-medium text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-500/25"
                        >
                            Browse Products
                        </Link>
                        <Link
                            :href="route('login')"
                            class="w-full sm:w-auto px-8 py-3 text-base font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors border border-gray-200 dark:border-gray-700"
                        >
                            Start Selling
                        </Link>
                    </div>
                </div>

                <!-- Stats -->
                <div class="mt-20 grid grid-cols-2 lg:grid-cols-4 gap-8">
                    <div class="text-center">
                        <div class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">10K+</div>
                        <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">Active Users</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">50K+</div>
                        <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">Products Sold</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">99.9%</div>
                        <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">Uptime</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">24/7</div>
                        <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">Support</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Products by Category Section -->
        <section v-if="categoriesWithProducts && categoriesWithProducts.length > 0" class="py-20 px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">
                        Browse Products
                    </h2>
                    <Link
                        :href="route('shop.index')"
                        class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 flex items-center gap-1 transition-colors"
                    >
                        View all
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                </div>

                <div class="space-y-12">
                    <div v-for="category in categoriesWithProducts" :key="category.id" class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                                {{ category.name }}
                            </h3>
                            <Link
                                :href="route('shop.index', { category: category.id })"
                                class="text-sm text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors"
                            >
                                See all →
                            </Link>
                        </div>

                        <div class="space-y-2">
                            <Link
                                v-for="product in category.products"
                                :key="product.id"
                                :href="route('shop.product', product.slug)"
                                class="group block bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-600 transition-all hover:shadow-md"
                            >
                                <div class="flex items-center gap-4 p-3">
                                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-indigo-100 to-purple-100 dark:from-indigo-900/30 dark:to-purple-900/30 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-medium text-gray-900 dark:text-white truncate group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                            {{ product.name }}
                                        </h4>
                                        <div class="flex items-center gap-3 mt-1">
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ category.name }}
                                            </span>
                                            <StarRating
                                                :rating="product.reviews_avg_rating || 0"
                                                :reviews-count="product.reviews_count || 0"
                                                size="xs"
                                            />
                                            <span class="text-xs text-green-600 dark:text-green-400">
                                                {{ product.stock_count }} in stock
                                            </span>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3 flex-shrink-0">
                                        <div class="text-right">
                                            <div class="text-lg font-bold text-gray-900 dark:text-white">
                                                ${{ product.price }}
                                            </div>
                                        </div>
                                        <button
                                            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors"
                                            @click.prevent="() => {}"
                                        >
                                            View
                                        </button>
                                    </div>
                                </div>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-20 px-4 sm:px-6 lg:px-8 bg-white dark:bg-gray-800/50">
            <div class="mx-auto max-w-7xl">
                <div class="text-center mb-16">
                    <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">
                        Why Choose AccMarket?
                    </h2>
                    <p class="mt-4 text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                        We provide the best platform for buying and selling digital products with security and trust.
                    </p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div
                        v-for="feature in features"
                        :key="feature.title"
                        class="p-6 bg-gray-50 dark:bg-gray-800 rounded-2xl hover:shadow-lg transition-shadow"
                    >
                        <div class="w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center mb-4">
                            <svg v-if="feature.icon === 'shield'" class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            <svg v-if="feature.icon === 'lightning'" class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            <svg v-if="feature.icon === 'users'" class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <svg v-if="feature.icon === 'support'" class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                            {{ feature.title }}
                        </h3>
                        <p class="text-gray-600 dark:text-gray-400 text-sm">
                            {{ feature.description }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20 px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-4xl">
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-3xl p-8 sm:p-12 text-center">
                    <h2 class="text-2xl sm:text-3xl font-bold text-white mb-4">
                        Ready to get started?
                    </h2>
                    <p class="text-indigo-100 mb-8 max-w-xl mx-auto">
                        Join thousands of users already buying and selling on AccMarket.
                        Create your free account today.
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        <Link
                            :href="route('login')"
                            class="w-full sm:w-auto px-8 py-3 text-base font-medium text-indigo-600 bg-white rounded-xl hover:bg-indigo-50 transition-colors"
                        >
                            Sign In
                        </Link>
                        <Link
                            :href="route('shop.index')"
                            class="w-full sm:w-auto px-8 py-3 text-base font-medium text-white bg-white/20 rounded-xl hover:bg-white/30 transition-colors"
                        >
                            Browse Shop
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-white dark:bg-gray-900 border-t border-gray-100 dark:border-gray-800">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex flex-col md:flex-row items-center justify-between space-y-4 md:space-y-0">
                    <div class="flex items-center space-x-2">
                        <div class="w-6 h-6 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-md flex items-center justify-center">
                            <span class="text-white font-bold text-xs">AM</span>
                        </div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">AccMarket</span>
                    </div>
                    <div class="flex items-center space-x-6 text-sm text-gray-500 dark:text-gray-400">
                        <Link :href="route('shop.index')" class="hover:text-gray-900 dark:hover:text-white transition-colors">Shop</Link>
                        <a href="#" class="hover:text-gray-900 dark:hover:text-white transition-colors">Terms</a>
                        <a href="#" class="hover:text-gray-900 dark:hover:text-white transition-colors">Privacy</a>
                    </div>
                    <p class="text-sm text-gray-400 dark:text-gray-500">
                        &copy; {{ new Date().getFullYear() }} AccMarket. All rights reserved.
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>
