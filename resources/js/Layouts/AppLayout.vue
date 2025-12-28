<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import Toast from '@/Components/Toast.vue';

const page = usePage();
const mobileMenuOpen = ref(false);
const darkMode = ref(false);
const categoriesOpen = ref(false);
const categories = ref([]);

const user = computed(() => page.props.auth?.user);
const isAdmin = computed(() => user.value?.roles?.some(r => r.name === 'admin'));
const isSeller = computed(() => user.value?.roles?.some(r => r.name === 'seller' || r.name === 'admin'));
const cartCount = computed(() => page.props.cartCount || 0);

const parentCategories = computed(() => {
    return categories.value?.filter(cat => !cat.parent_id) || [];
});

const getChildCategories = (parentId) => {
    return categories.value?.filter(cat => cat.parent_id === parentId) || [];
};

const getCategoryIcon = (categoryName) => {
    const name = categoryName.toLowerCase();
    if (name.includes('steam')) return '🎮';
    if (name.includes('playstation') || name.includes('ps')) return '🎮';
    if (name.includes('xbox')) return '🎮';
    if (name.includes('epic') || name.includes('game')) return '🎮';
    if (name.includes('netflix')) return '📺';
    if (name.includes('spotify')) return '🎵';
    if (name.includes('disney')) return '🎬';
    if (name.includes('hbo') || name.includes('hulu')) return '📺';
    if (name.includes('instagram')) return '📷';
    if (name.includes('twitter') || name.includes('x')) return '🐦';
    if (name.includes('tiktok')) return '🎵';
    if (name.includes('facebook')) return '👥';
    if (name.includes('linkedin')) return '💼';
    if (name.includes('telegram')) return '✈️';
    if (name.includes('windows')) return '🪟';
    if (name.includes('office')) return '📄';
    if (name.includes('antivirus')) return '🛡️';
    if (name.includes('vpn')) return '🔒';
    if (name.includes('gift') || name.includes('card')) return '🎁';
    if (name.includes('amazon')) return '📦';
    if (name.includes('itunes') || name.includes('apple')) return '🍎';
    if (name.includes('google')) return '🔍';
    return '📦';
};

onMounted(async () => {
    // Load dark mode
    const savedMode = localStorage.getItem('darkMode');
    if (savedMode !== null) {
        darkMode.value = savedMode === 'true';
    } else {
        darkMode.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
    }
    applyDarkMode();

    // Fetch categories
    try {
        const response = await fetch('/api/categories');
        if (response.ok) {
            categories.value = await response.json();
        }
    } catch (error) {
        console.error('Failed to fetch categories:', error);
    }
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
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-200">
        <!-- Toast Notifications -->
        <Toast />

        <!-- Navigation -->
        <nav class="sticky top-0 z-50 bg-white/80 dark:bg-gray-900/80 backdrop-blur-lg border-b border-gray-100 dark:border-gray-800">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <!-- Logo -->
                    <div class="flex items-center space-x-8">
                        <Link :href="route('shop.index')" class="flex items-center space-x-2">
                            <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center">
                                <span class="text-white font-bold text-sm">AM</span>
                            </div>
                            <span class="text-lg font-semibold text-gray-900 dark:text-white hidden sm:block">AccMarket</span>
                        </Link>

                        <!-- Desktop Navigation -->
                        <div class="hidden md:flex items-center space-x-1">
                            <!-- Categories Dropdown -->
                            <div class="relative" @mouseleave="categoriesOpen = false">
                                <button
                                    @mouseenter="categoriesOpen = true"
                                    class="px-3 py-2 text-sm font-medium rounded-lg transition-colors flex items-center gap-1 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800"
                                >
                                    Categories
                                    <svg
                                        class="w-4 h-4 transition-transform"
                                        :class="categoriesOpen ? 'rotate-180' : ''"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <!-- Mega Menu Dropdown -->
                                <div
                                    v-if="categoriesOpen"
                                    @mouseenter="categoriesOpen = true"
                                    class="absolute left-0 top-full mt-1 w-screen max-w-4xl bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-100 dark:border-gray-700 z-50"
                                >
                                    <div class="p-6">
                                        <div class="grid grid-cols-3 gap-6">
                                            <div v-for="parent in parentCategories.slice(0, 6)" :key="parent.id">
                                                <div class="flex items-center gap-2 mb-3">
                                                    <span class="text-lg">{{ getCategoryIcon(parent.name) }}</span>
                                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                                                        {{ parent.name }}
                                                    </h3>
                                                </div>
                                                <ul class="space-y-2">
                                                    <li v-for="child in getChildCategories(parent.id)" :key="child.id">
                                                        <Link
                                                            :href="route('shop.index', { category: child.slug })"
                                                            class="text-sm text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors block"
                                                            @click="categoriesOpen = false"
                                                        >
                                                            {{ child.name }}
                                                        </Link>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>

                                        <!-- View All Categories Link -->
                                        <div class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-700">
                                            <Link
                                                :href="route('shop.index')"
                                                class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 flex items-center gap-1"
                                                @click="categoriesOpen = false"
                                            >
                                                View all products
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <template v-if="user">
                                <Link
                                    :href="route('orders.index')"
                                    class="px-3 py-2 text-sm font-medium rounded-lg transition-colors"
                                    :class="route().current('orders.*')
                                        ? 'text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30'
                                        : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800'"
                                >
                                    Orders
                                </Link>
                                <Link
                                    v-if="isSeller"
                                    :href="route('seller.products.index')"
                                    class="px-3 py-2 text-sm font-medium rounded-lg transition-colors"
                                    :class="route().current('seller.products.*')
                                        ? 'text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30'
                                        : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800'"
                                >
                                    Sell
                                </Link>
                            </template>
                        </div>
                    </div>

                    <!-- Right Side -->
                    <div class="flex items-center space-x-3">
                        <!-- Dark Mode Toggle -->
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

                        <!-- Cart -->
                        <Link
                            :href="route('cart.index')"
                            class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors relative"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            <span
                                v-if="cartCount > 0"
                                class="absolute -top-1 -right-1 w-5 h-5 bg-indigo-600 text-white text-xs font-bold rounded-full flex items-center justify-center"
                            >
                                {{ cartCount > 99 ? '99+' : cartCount }}
                            </span>
                        </Link>

                        <!-- Admin Link -->
                        <Link
                            v-if="isAdmin"
                            :href="route('admin.dashboard')"
                            class="hidden sm:flex p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </Link>

                        <!-- User Menu / Auth Links -->
                        <template v-if="user">
                            <div class="hidden sm:flex items-center space-x-2">
                                <Link
                                    :href="route('profile.edit')"
                                    class="flex items-center space-x-2 px-3 py-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                                >
                                    <div class="w-7 h-7 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                                        <span class="text-white text-xs font-medium">{{ user.name.charAt(0).toUpperCase() }}</span>
                                    </div>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ user.name }}</span>
                                </Link>
                                <Link
                                    :href="route('logout')"
                                    method="post"
                                    as="button"
                                    class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                </Link>
                            </div>
                        </template>
                        <template v-else>
                            <div class="hidden sm:flex items-center space-x-2">
                                <Link
                                    :href="route('login')"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors"
                                >
                                    Sign in
                                </Link>
                                <Link
                                    :href="route('register')"
                                    class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors"
                                >
                                    Get started
                                </Link>
                            </div>
                        </template>

                        <!-- Mobile menu button -->
                        <button
                            @click="mobileMenuOpen = !mobileMenuOpen"
                            class="md:hidden p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                        >
                            <svg v-if="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Mobile Navigation -->
                <div v-if="mobileMenuOpen" class="md:hidden py-4 border-t border-gray-100 dark:border-gray-800">
                    <div class="space-y-1">
                        <template v-if="user">
                            <Link
                                :href="route('orders.index')"
                                class="block px-3 py-2 text-sm font-medium rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800"
                            >
                                My Orders
                            </Link>
                            <Link
                                v-if="isSeller"
                                :href="route('seller.products.index')"
                                class="block px-3 py-2 text-sm font-medium rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800"
                            >
                                My Products
                            </Link>
                            <Link
                                v-if="isAdmin"
                                :href="route('admin.dashboard')"
                                class="block px-3 py-2 text-sm font-medium rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800"
                            >
                                Admin Panel
                            </Link>
                            <div class="pt-4 border-t border-gray-100 dark:border-gray-800 mt-4">
                                <Link
                                    :href="route('profile.edit')"
                                    class="block px-3 py-2 text-sm font-medium rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800"
                                >
                                    Profile
                                </Link>
                                <Link
                                    :href="route('logout')"
                                    method="post"
                                    as="button"
                                    class="block w-full text-left px-3 py-2 text-sm font-medium rounded-lg text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20"
                                >
                                    Sign out
                                </Link>
                            </div>
                        </template>
                        <template v-else>
                            <div class="pt-4 border-t border-gray-100 dark:border-gray-800 mt-4 space-y-2">
                                <Link
                                    :href="route('login')"
                                    class="block px-3 py-2 text-sm font-medium rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800"
                                >
                                    Sign in
                                </Link>
                                <Link
                                    :href="route('register')"
                                    class="block px-3 py-2 text-sm font-medium text-center text-white bg-indigo-600 rounded-lg hover:bg-indigo-700"
                                >
                                    Get started
                                </Link>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main>
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-white dark:bg-gray-900 border-t border-gray-100 dark:border-gray-800 mt-auto">
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
