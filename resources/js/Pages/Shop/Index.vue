<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    products: Object,
    categories: Array,
    filters: Object,
});

const search = ref(props.filters.search || '');
const category = ref(props.filters.category || '');
const minPrice = ref(props.filters.min_price || '');
const maxPrice = ref(props.filters.max_price || '');
const sort = ref(props.filters.sort || 'newest');

const applyFilters = debounce(() => {
    router.get(route('shop.index'), {
        search: search.value || undefined,
        category: category.value || undefined,
        min_price: minPrice.value || undefined,
        max_price: maxPrice.value || undefined,
        sort: sort.value !== 'newest' ? sort.value : undefined,
    }, {
        preserveState: true,
        replace: true,
    });
}, 300);

watch([search], applyFilters);

const clearFilters = () => {
    search.value = '';
    category.value = '';
    minPrice.value = '';
    maxPrice.value = '';
    sort.value = 'newest';
    router.get(route('shop.index'));
};

const addToCart = (product) => {
    router.post(route('cart.add'), {
        product_id: product.id,
        quantity: 1,
    }, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Shop" />

    <AppLayout>
        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">Shop</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Browse our collection of digital products</p>
                </div>

                <div class="flex flex-col lg:flex-row gap-8">
                    <!-- Sidebar Filters -->
                    <aside class="w-full lg:w-64 flex-shrink-0">
                        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5 sticky top-24">
                            <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                </svg>
                                Filters
                            </h2>

                            <!-- Search -->
                            <div class="mb-5">
                                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Search</label>
                                <div class="relative">
                                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                    <input
                                        v-model="search"
                                        type="text"
                                        placeholder="Search products..."
                                        class="w-full pl-9 pr-3 py-2 text-sm rounded-lg border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                </div>
                            </div>

                            <!-- Category -->
                            <div class="mb-5">
                                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Category</label>
                                <select
                                    v-model="category"
                                    @change="applyFilters"
                                    class="w-full text-sm rounded-lg border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">All Categories</option>
                                    <template v-for="cat in categories" :key="cat.id">
                                        <option :value="cat.slug" class="font-medium">{{ cat.name }}</option>
                                        <option
                                            v-for="child in cat.children"
                                            :key="child.id"
                                            :value="child.slug"
                                        >
                                            &nbsp;&nbsp;{{ child.name }}
                                        </option>
                                    </template>
                                </select>
                            </div>

                            <!-- Price Range -->
                            <div class="mb-5">
                                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Price Range</label>
                                <div class="flex items-center space-x-2">
                                    <input
                                        v-model="minPrice"
                                        type="number"
                                        placeholder="Min"
                                        min="0"
                                        class="w-1/2 text-sm rounded-lg border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500"
                                        @change="applyFilters"
                                    />
                                    <span class="text-gray-400">-</span>
                                    <input
                                        v-model="maxPrice"
                                        type="number"
                                        placeholder="Max"
                                        min="0"
                                        class="w-1/2 text-sm rounded-lg border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500"
                                        @change="applyFilters"
                                    />
                                </div>
                            </div>

                            <!-- Sort -->
                            <div class="mb-5">
                                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Sort By</label>
                                <select
                                    v-model="sort"
                                    @change="applyFilters"
                                    class="w-full text-sm rounded-lg border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="newest">Newest First</option>
                                    <option value="price_asc">Price: Low to High</option>
                                    <option value="price_desc">Price: High to Low</option>
                                    <option value="name">Name A-Z</option>
                                </select>
                            </div>

                            <button
                                @click="clearFilters"
                                class="w-full text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors"
                            >
                                Clear All Filters
                            </button>
                        </div>
                    </aside>

                    <!-- Product Grid -->
                    <main class="flex-1">
                        <div v-if="products.data.length > 0" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
                            <div
                                v-for="product in products.data"
                                :key="product.id"
                                class="group bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden hover:shadow-lg hover:border-gray-200 dark:hover:border-gray-600 transition-all duration-200"
                            >
                                <Link :href="route('shop.product', product.id)" class="block">
                                    <div class="h-40 bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 flex items-center justify-center relative overflow-hidden">
                                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center transform group-hover:scale-110 transition-transform duration-200">
                                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                            </svg>
                                        </div>
                                        <div class="absolute top-3 right-3">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400">
                                                {{ product.available_items_count }} left
                                            </span>
                                        </div>
                                    </div>
                                </Link>
                                <div class="p-4">
                                    <div class="flex items-start justify-between mb-2">
                                        <Link :href="route('shop.product', product.id)" class="block flex-1">
                                            <h3 class="font-semibold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors line-clamp-1">
                                                {{ product.name }}
                                            </h3>
                                        </Link>
                                    </div>
                                    <p class="text-xs text-indigo-600 dark:text-indigo-400 mb-2">
                                        {{ product.category?.name }}
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2 mb-3">
                                        {{ product.description?.substring(0, 80) }}{{ product.description?.length > 80 ? '...' : '' }}
                                    </p>
                                    <div class="flex items-center justify-between pt-3 border-t border-gray-100 dark:border-gray-700">
                                        <span class="text-lg font-bold text-gray-900 dark:text-white">
                                            ${{ parseFloat(product.price).toFixed(2) }}
                                        </span>
                                        <button
                                            @click.prevent="addToCart(product)"
                                            class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors"
                                        >
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                            Add
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div v-else class="text-center py-16 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700">
                            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No products found</h3>
                            <p class="text-gray-500 dark:text-gray-400 mb-4">Try adjusting your filters or search criteria.</p>
                            <button
                                @click="clearFilters"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors"
                            >
                                Clear all filters
                            </button>
                        </div>

                        <!-- Pagination -->
                        <div v-if="products.links && products.links.length > 3" class="mt-8">
                            <div class="flex items-center justify-center space-x-1">
                                <template v-for="link in products.links" :key="link.label">
                                    <Link
                                        v-if="link.url"
                                        :href="link.url"
                                        class="px-3 py-1.5 text-sm rounded-lg transition-colors"
                                        :class="link.active
                                            ? 'bg-indigo-600 text-white'
                                            : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700'"
                                        v-html="link.label"
                                    />
                                    <span
                                        v-else
                                        class="px-3 py-1.5 text-sm rounded-lg bg-gray-50 dark:bg-gray-800 text-gray-400 dark:text-gray-500"
                                        v-html="link.label"
                                    />
                                </template>
                            </div>
                        </div>
                    </main>
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
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
