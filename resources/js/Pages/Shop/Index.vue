<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import TextInput from '@/Components/TextInput.vue';

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

const applyFilters = () => {
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
};

watch([search], () => {
    const timeout = setTimeout(applyFilters, 300);
    return () => clearTimeout(timeout);
});

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

    <div class="min-h-screen bg-gray-100">
        <!-- Header -->
        <header class="bg-white shadow">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center">
                    <h1 class="text-3xl font-bold text-gray-900">Shop</h1>
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
            <div class="flex gap-8">
                <!-- Sidebar Filters -->
                <aside class="w-64 flex-shrink-0">
                    <div class="bg-white rounded-lg shadow p-6 sticky top-8">
                        <h2 class="text-lg font-semibold mb-4">Filters</h2>

                        <!-- Search -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                            <TextInput
                                v-model="search"
                                type="text"
                                placeholder="Search products..."
                                class="w-full"
                            />
                        </div>

                        <!-- Category -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <select
                                v-model="category"
                                @change="applyFilters"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">All Categories</option>
                                <template v-for="cat in categories" :key="cat.id">
                                    <option :value="cat.slug" class="font-semibold">{{ cat.name }}</option>
                                    <option
                                        v-for="child in cat.children"
                                        :key="child.id"
                                        :value="child.slug"
                                    >
                                        — {{ child.name }}
                                    </option>
                                </template>
                            </select>
                        </div>

                        <!-- Price Range -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Price Range</label>
                            <div class="flex space-x-2">
                                <TextInput
                                    v-model="minPrice"
                                    type="number"
                                    placeholder="Min"
                                    min="0"
                                    class="w-1/2"
                                    @change="applyFilters"
                                />
                                <TextInput
                                    v-model="maxPrice"
                                    type="number"
                                    placeholder="Max"
                                    min="0"
                                    class="w-1/2"
                                    @change="applyFilters"
                                />
                            </div>
                        </div>

                        <!-- Sort -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
                            <select
                                v-model="sort"
                                @change="applyFilters"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="newest">Newest</option>
                                <option value="price_asc">Price: Low to High</option>
                                <option value="price_desc">Price: High to Low</option>
                                <option value="name">Name</option>
                            </select>
                        </div>

                        <button
                            @click="clearFilters"
                            class="w-full text-sm text-indigo-600 hover:text-indigo-800"
                        >
                            Clear Filters
                        </button>
                    </div>
                </aside>

                <!-- Product Grid -->
                <main class="flex-1">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div
                            v-for="product in products.data"
                            :key="product.id"
                            class="bg-white rounded-lg shadow overflow-hidden hover:shadow-lg transition-shadow"
                        >
                            <Link :href="route('shop.product', product.id)">
                                <div class="h-48 bg-gradient-to-br from-indigo-100 to-purple-100 flex items-center justify-center">
                                    <span class="text-4xl text-indigo-300">📦</span>
                                </div>
                            </Link>
                            <div class="p-4">
                                <Link :href="route('shop.product', product.id)">
                                    <h3 class="text-lg font-semibold text-gray-900 hover:text-indigo-600">
                                        {{ product.name }}
                                    </h3>
                                </Link>
                                <p class="text-sm text-gray-500 mt-1">
                                    {{ product.category?.name }}
                                </p>
                                <p class="text-sm text-gray-600 mt-2 line-clamp-2">
                                    {{ product.description?.substring(0, 100) }}{{ product.description?.length > 100 ? '...' : '' }}
                                </p>
                                <div class="mt-4 flex items-center justify-between">
                                    <span class="text-xl font-bold text-gray-900">
                                        ${{ parseFloat(product.price).toFixed(2) }}
                                    </span>
                                    <span class="text-sm text-green-600">
                                        {{ product.available_items_count }} in stock
                                    </span>
                                </div>
                                <button
                                    @click="addToCart(product)"
                                    class="mt-4 w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 transition-colors"
                                >
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="products.data.length === 0" class="text-center py-12 bg-white rounded-lg shadow">
                        <p class="text-gray-500">No products found matching your criteria.</p>
                        <button @click="clearFilters" class="mt-4 text-indigo-600 hover:text-indigo-800">
                            Clear filters
                        </button>
                    </div>

                    <!-- Pagination -->
                    <div v-if="products.links && products.links.length > 3" class="mt-8 flex justify-center">
                        <div class="flex space-x-1">
                            <template v-for="link in products.links" :key="link.label">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    v-html="link.label"
                                    class="px-4 py-2 rounded text-sm"
                                    :class="link.active ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'"
                                />
                                <span
                                    v-else
                                    v-html="link.label"
                                    class="px-4 py-2 rounded text-sm bg-gray-100 text-gray-400"
                                />
                            </template>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
