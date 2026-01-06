<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import StarRating from '@/Components/StarRating.vue';
import BuyModal from '@/Components/BuyModal.vue';
import { useTranslations } from '@/composables/useTranslations';
import debounce from 'lodash/debounce';

const { t } = useTranslations();

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

// Buy modal state
const showBuyModal = ref(false);
const selectedProduct = ref(null);

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

const openBuyModal = (product) => {
    selectedProduct.value = product;
    showBuyModal.value = true;
};

const closeBuyModal = () => {
    showBuyModal.value = false;
    selectedProduct.value = null;
};
</script>

<template>
    <Head :title="t('common.shop')" />

    <AppLayout>
        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">{{ t('common.shop') }}</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">{{ t('shop.subtitle') }}</p>
                </div>

                <!-- Product List -->
                <div>
                        <!-- Filters Bar -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-4 mb-6">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                <!-- Search -->
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">{{ t('common.search') }}</label>
                                    <div class="relative">
                                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                        <input
                                            v-model="search"
                                            type="text"
                                            :placeholder="t('shop.search')"
                                            class="w-full pl-9 pr-3 py-2 text-sm rounded-lg border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500"
                                        />
                                    </div>
                                </div>

                                <!-- Price Min -->
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">{{ t('shop.min_price') }}</label>
                                    <input
                                        v-model="minPrice"
                                        type="number"
                                        placeholder="$0"
                                        min="0"
                                        class="w-full text-sm rounded-lg border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500"
                                        @change="applyFilters"
                                    />
                                </div>

                                <!-- Price Max -->
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">{{ t('shop.max_price') }}</label>
                                    <input
                                        v-model="maxPrice"
                                        type="number"
                                        placeholder="Any"
                                        min="0"
                                        class="w-full text-sm rounded-lg border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500"
                                        @change="applyFilters"
                                    />
                                </div>

                                <!-- Sort -->
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">{{ t('shop.sort_by') }}</label>
                                    <select
                                        v-model="sort"
                                        @change="applyFilters"
                                        class="w-full text-sm rounded-lg border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="newest">{{ t('shop.newest') }}</option>
                                        <option value="price_asc">{{ t('shop.price_low') }}</option>
                                        <option value="price_desc">{{ t('shop.price_high') }}</option>
                                        <option value="name">Name A-Z</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Active Filters & Clear -->
                            <div v-if="search || category || minPrice || maxPrice || sort !== 'newest'" class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Active filters:</span>
                                    <span v-if="search" class="inline-flex items-center px-2 py-1 rounded-md bg-indigo-50 dark:bg-indigo-900/30 text-xs font-medium text-indigo-700 dark:text-indigo-300">
                                        Search: "{{ search }}"
                                    </span>
                                    <span v-if="minPrice" class="inline-flex items-center px-2 py-1 rounded-md bg-indigo-50 dark:bg-indigo-900/30 text-xs font-medium text-indigo-700 dark:text-indigo-300">
                                        Min: ${{ minPrice }}
                                    </span>
                                    <span v-if="maxPrice" class="inline-flex items-center px-2 py-1 rounded-md bg-indigo-50 dark:bg-indigo-900/30 text-xs font-medium text-indigo-700 dark:text-indigo-300">
                                        Max: ${{ maxPrice }}
                                    </span>
                                </div>
                                <button
                                    @click="clearFilters"
                                    class="text-xs font-medium text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors"
                                >
                                    Clear All
                                </button>
                            </div>
                        </div>

                        <!-- Product List -->
                        <div v-if="products.data.length > 0" class="space-y-2">
                            <div
                                v-for="product in products.data"
                                :key="product.id"
                                class="group bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-600 transition-colors"
                            >
                                <div class="flex items-center gap-4 p-3">
                                    <!-- Product Icon -->
                                    <Link :href="route('shop.product', product.id)" class="flex-shrink-0">
                                        <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                            </svg>
                                        </div>
                                    </Link>

                                    <!-- Product Info -->
                                    <div class="flex-1 min-w-0">
                                        <Link :href="route('shop.product', product.id)">
                                            <h3 class="font-medium text-gray-900 dark:text-white truncate group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                                {{ product.name }}
                                            </h3>
                                        </Link>
                                        <div class="flex items-center gap-3 mt-1">
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ product.category?.name }}
                                            </span>
                                            <StarRating
                                                :rating="product.reviews_avg_rating || 0"
                                                :reviews-count="product.reviews_count || 0"
                                                size="xs"
                                            />
                                            <span class="text-xs text-emerald-600 dark:text-emerald-400">
                                                {{ product.available_items_count }} {{ t('shop.in_stock') }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Price & Actions -->
                                    <div class="flex items-center gap-3">
                                        <div class="text-lg font-bold text-gray-900 dark:text-white">
                                            ${{ parseFloat(product.price).toFixed(2) }}
                                        </div>
                                        <button
                                            v-if="product.available_items_count > 0"
                                            @click.prevent="openBuyModal(product)"
                                            class="px-4 py-1.5 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 transition-colors"
                                        >
                                            {{ t('shop.buy_now') }}
                                        </button>
                                        <span
                                            v-else
                                            class="px-3 py-1.5 text-sm font-medium text-gray-500 bg-gray-100 dark:bg-gray-700 dark:text-gray-400 rounded-md"
                                        >
                                            {{ t('shop.out_of_stock') }}
                                        </span>
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
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ t('shop.no_products') }}</h3>
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
                </div>
            </div>
        </div>

        <!-- Buy Modal -->
        <BuyModal
            :show="showBuyModal"
            :product="selectedProduct"
            @close="closeBuyModal"
        />
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
