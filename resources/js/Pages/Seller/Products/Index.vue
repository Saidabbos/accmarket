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
        'draft': 'bg-gray-100 text-gray-800',
        'active': 'bg-green-100 text-green-800',
        'inactive': 'bg-red-100 text-red-800',
    }[status] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="My Products" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    My Products
                </h2>
                <Link :href="route('seller.products.create')">
                    <PrimaryButton>Add Product</PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="mb-6 bg-white p-4 rounded-lg shadow">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <TextInput
                                v-model="search"
                                type="text"
                                placeholder="Search products..."
                                class="w-full"
                            />
                        </div>
                        <div>
                            <select
                                v-model="status"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
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
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">All Categories</option>
                                <option v-for="category in categories" :key="category.id" :value="category.id">
                                    {{ category.parent_id ? '— ' : '' }}{{ category.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Products Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Product
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Category
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Price
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Stock
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="product in products.data" :key="product.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ product.name }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ product.description?.substring(0, 50) }}{{ product.description?.length > 50 ? '...' : '' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ product.category?.name || 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ${{ parseFloat(product.price).toFixed(2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="text-green-600 font-medium">{{ product.available_items_count }}</span>
                                        <span class="text-gray-500"> / {{ product.items_count }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <select
                                            :value="product.status"
                                            @change="updateStatus(product, $event.target.value)"
                                            :class="['text-xs font-medium px-2 py-1 rounded-full border-0', getStatusClass(product.status)]"
                                        >
                                            <option value="draft">Draft</option>
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                        <Link
                                            :href="route('seller.products.show', product.id)"
                                            class="text-gray-600 hover:text-gray-900"
                                        >
                                            View
                                        </Link>
                                        <Link
                                            :href="route('seller.products.edit', product.id)"
                                            class="text-indigo-600 hover:text-indigo-900"
                                        >
                                            Edit
                                        </Link>
                                        <button
                                            @click="confirmDelete(product)"
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="products.data.length === 0">
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        No products found.
                                        <Link :href="route('seller.products.create')" class="text-indigo-600 hover:underline">
                                            Create your first product
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="products.links && products.links.length > 3" class="px-6 py-4 border-t">
                        <div class="flex justify-between items-center">
                            <div class="text-sm text-gray-700">
                                Showing {{ products.from }} to {{ products.to }} of {{ products.total }} products
                            </div>
                            <div class="flex space-x-1">
                                <template v-for="link in products.links" :key="link.label">
                                    <Link
                                        v-if="link.url"
                                        :href="link.url"
                                        v-html="link.label"
                                        class="px-3 py-1 rounded text-sm"
                                        :class="link.active ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                    />
                                    <span
                                        v-else
                                        v-html="link.label"
                                        class="px-3 py-1 rounded text-sm bg-gray-100 text-gray-400"
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
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Delete Product
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Are you sure you want to delete "{{ productToDelete?.name }}"? This will also delete all associated items. This action cannot be undone.
                </p>
                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton @click="deleteModal = false">
                        Cancel
                    </SecondaryButton>
                    <DangerButton @click="deleteProduct">
                        Delete Product
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
