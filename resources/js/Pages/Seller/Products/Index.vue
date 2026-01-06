<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    products: Object,
    categories: Array,
    filters: Object,
});

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const categoryId = ref(props.filters.category_id || '');

const deleteModal = ref(false);
const productToDelete = ref(null);

const statusForm = useForm({
    status: '',
});

watch([search, status, categoryId], () => {
    router.get(route('seller.products.index'), {
        search: search.value || undefined,
        status: status.value || undefined,
        category_id: categoryId.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
}, { debounce: 300 });

const confirmDelete = (product) => {
    productToDelete.value = product;
    deleteModal.value = true;
};

const deleteProduct = () => {
    router.delete(route('seller.products.destroy', productToDelete.value.id), {
        onSuccess: () => {
            deleteModal.value = false;
            productToDelete.value = null;
        },
    });
};

const updateStatus = (product, newStatus) => {
    statusForm.status = newStatus;
    statusForm.patch(route('seller.products.status', product.id));
};

const getStatusClass = (status) => {
    return {
        'draft': 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
        'active': 'bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-400',
        'inactive': 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-400',
    }[status] || 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300';
};
</script>

<template>
    <Head title="My Products" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">
                    My Products
                </h2>
                <Link :href="route('seller.products.create')">
                    <PrimaryButton>Add Product</PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="mb-4 bg-white dark:bg-gray-800 p-4 rounded-lg shadow border border-gray-100 dark:border-gray-700">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <TextInput
                                v-model="search"
                                type="text"
                                placeholder="Search products..."
                                class="w-full text-sm"
                            />
                        </div>
                        <div>
                            <select
                                v-model="status"
                                class="w-full text-sm rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">All Statuses</option>
                                <option value="draft">Draft</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div>
                            <select
                                v-model="categoryId"
                                class="w-full text-sm rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">All Categories</option>
                                <option v-for="category in categories" :key="category.id" :value="category.id">
                                    {{ category.parent_id ? '— ' : '' }}{{ category.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Products List -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-100 dark:border-gray-700">
                    <!-- Header -->
                    <div class="hidden sm:flex items-center px-4 py-2 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                        <div class="flex-1">Product</div>
                        <div class="w-20 text-center">Stock</div>
                        <div class="w-20 text-center">Price</div>
                        <div class="w-24 text-center">Status</div>
                        <div class="w-28 text-right">Actions</div>
                    </div>

                    <!-- Product Items -->
                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        <div
                            v-for="product in products.data"
                            :key="product.id"
                            class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-0 p-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                        >
                            <!-- Product Info -->
                            <div class="flex-1 min-w-0">
                                <Link :href="route('seller.products.show', product.id)" class="block">
                                    <h3 class="text-sm font-medium text-gray-900 dark:text-white line-clamp-1 hover:text-indigo-600 dark:hover:text-indigo-400" :title="product.name">
                                        {{ product.name }}
                                    </h3>
                                </Link>
                                <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-1 mt-0.5">
                                    {{ product.category?.name || 'No category' }}
                                    <span v-if="product.description" class="hidden sm:inline"> • {{ product.description.substring(0, 40) }}{{ product.description.length > 40 ? '...' : '' }}</span>
                                </p>
                            </div>

                            <!-- Mobile: Price, Stock, Status row -->
                            <div class="flex sm:hidden items-center justify-between gap-2 mt-1">
                                <div class="flex items-center gap-3">
                                    <span class="text-sm font-bold text-gray-900 dark:text-white">${{ parseFloat(product.price).toFixed(2) }}</span>
                                    <span class="text-xs">
                                        <span class="text-green-600 dark:text-green-400 font-medium">{{ product.available_items_count }}</span>
                                        <span class="text-gray-400">/{{ product.items_count }}</span>
                                    </span>
                                    <span :class="['text-xs px-2 py-0.5 rounded-full', getStatusClass(product.status)]">
                                        {{ product.status }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <Link :href="route('seller.products.edit', product.id)" class="text-xs text-indigo-600 dark:text-indigo-400">Edit</Link>
                                    <button @click="confirmDelete(product)" class="text-xs text-red-600 dark:text-red-400">Delete</button>
                                </div>
                            </div>

                            <!-- Desktop: Stock -->
                            <div class="hidden sm:block w-20 text-center text-xs">
                                <span class="text-green-600 dark:text-green-400 font-medium">{{ product.available_items_count }}</span>
                                <span class="text-gray-400 dark:text-gray-500">/{{ product.items_count }}</span>
                            </div>

                            <!-- Desktop: Price -->
                            <div class="hidden sm:block w-20 text-center">
                                <span class="text-sm font-bold text-gray-900 dark:text-white">${{ parseFloat(product.price).toFixed(2) }}</span>
                            </div>

                            <!-- Desktop: Status -->
                            <div class="hidden sm:block w-24 text-center">
                                <select
                                    :value="product.status"
                                    @change="updateStatus(product, $event.target.value)"
                                    :class="['text-xs font-medium px-2 py-1 rounded-full border-0 cursor-pointer', getStatusClass(product.status)]"
                                >
                                    <option value="draft">Draft</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>

                            <!-- Desktop: Actions -->
                            <div class="hidden sm:flex w-28 justify-end items-center gap-2 text-xs">
                                <Link :href="route('seller.products.edit', product.id)" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800">
                                    Edit
                                </Link>
                                <button @click="confirmDelete(product)" class="text-red-600 dark:text-red-400 hover:text-red-800">
                                    Delete
                                </button>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div v-if="products.data.length === 0" class="p-8 text-center">
                            <div class="w-12 h-12 mx-auto mb-3 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">No products found</p>
                            <Link :href="route('seller.products.create')" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                                Create your first product
                            </Link>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="products.links && products.links.length > 3" class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">
                        <div class="flex flex-col sm:flex-row justify-between items-center gap-2">
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                {{ products.from }}-{{ products.to }} of {{ products.total }}
                            </div>
                            <div class="flex space-x-1">
                                <template v-for="link in products.links" :key="link.label">
                                    <Link
                                        v-if="link.url"
                                        :href="link.url"
                                        v-html="link.label"
                                        class="px-2.5 py-1 rounded text-xs"
                                        :class="link.active ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'"
                                    />
                                    <span
                                        v-else
                                        v-html="link.label"
                                        class="px-2.5 py-1 rounded text-xs bg-gray-50 dark:bg-gray-800 text-gray-400 dark:text-gray-500"
                                    />
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <Modal :show="deleteModal" @close="deleteModal = false">
            <div class="p-6 dark:bg-gray-800">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    Delete Product
                </h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Are you sure you want to delete "{{ productToDelete?.name }}"? This will also delete all associated items. This action cannot be undone.
                </p>
                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton @click="deleteModal = false">
                        Cancel
                    </SecondaryButton>
                    <DangerButton @click="deleteProduct">
                        Delete
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<style scoped>
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
