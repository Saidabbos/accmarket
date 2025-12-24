<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    product: Object,
});

const addItemsModal = ref(false);
const deleteModal = ref(false);

const addItemsForm = useForm({
    items_file: null,
});

const handleFileChange = (event) => {
    addItemsForm.items_file = event.target.files[0];
};

const submitAddItems = () => {
    addItemsForm.post(route('seller.products.add-items', props.product.id), {
        forceFormData: true,
        onSuccess: () => {
            addItemsModal.value = false;
            addItemsForm.reset();
        },
    });
};

const deleteProduct = () => {
    router.delete(route('seller.products.destroy', props.product.id));
};

const getStatusClass = (status) => {
    return {
        'available': 'bg-green-100 text-green-800',
        'reserved': 'bg-yellow-100 text-yellow-800',
        'sold': 'bg-gray-100 text-gray-800',
    }[status] || 'bg-gray-100 text-gray-800';
};

const getProductStatusClass = (status) => {
    return {
        'draft': 'bg-gray-100 text-gray-800',
        'active': 'bg-green-100 text-green-800',
        'inactive': 'bg-red-100 text-red-800',
    }[status] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head :title="product.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    {{ product.name }}
                </h2>
                <div class="flex space-x-2">
                    <Link :href="route('seller.products.edit', product.id)">
                        <SecondaryButton>Edit</SecondaryButton>
                    </Link>
                    <DangerButton @click="deleteModal = true">Delete</DangerButton>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- Product Info -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Status</h3>
                                <span :class="['mt-1 inline-block px-3 py-1 rounded-full text-sm font-medium', getProductStatusClass(product.status)]">
                                    {{ product.status.charAt(0).toUpperCase() + product.status.slice(1) }}
                                </span>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Price</h3>
                                <p class="mt-1 text-2xl font-bold text-gray-900">${{ parseFloat(product.price).toFixed(2) }}</p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Category</h3>
                                <p class="mt-1 text-lg text-gray-900">{{ product.category?.name || 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="mt-6">
                            <h3 class="text-sm font-medium text-gray-500">Description</h3>
                            <p class="mt-1 text-gray-900">{{ product.description || 'No description provided.' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Stock Summary -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Stock Summary</h3>
                            <PrimaryButton @click="addItemsModal = true">Add Items</PrimaryButton>
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <div class="bg-green-50 rounded-lg p-4 text-center">
                                <p class="text-3xl font-bold text-green-600">{{ product.available_items_count }}</p>
                                <p class="text-sm text-green-700">Available</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4 text-center">
                                <p class="text-3xl font-bold text-gray-600">{{ product.items_count - product.available_items_count }}</p>
                                <p class="text-sm text-gray-700">Sold/Reserved</p>
                            </div>
                            <div class="bg-blue-50 rounded-lg p-4 text-center">
                                <p class="text-3xl font-bold text-blue-600">{{ product.items_count }}</p>
                                <p class="text-sm text-blue-700">Total</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Items List -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Items (Last 100)</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Content</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="item in product.items" :key="item.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            #{{ item.id }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 font-mono">
                                            {{ item.content.length > 80 ? item.content.substring(0, 80) + '...' : item.content }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="['px-2 py-1 text-xs font-medium rounded-full', getStatusClass(item.status)]">
                                                {{ item.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ new Date(item.created_at).toLocaleDateString() }}
                                        </td>
                                    </tr>
                                    <tr v-if="!product.items || product.items.length === 0">
                                        <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                            No items found. Add items to start selling.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Back Link -->
                <div>
                    <Link :href="route('seller.products.index')" class="text-indigo-600 hover:text-indigo-800">
                        ← Back to Products
                    </Link>
                </div>
            </div>
        </div>

        <!-- Add Items Modal -->
        <Modal :show="addItemsModal" @close="addItemsModal = false">
            <form @submit.prevent="submitAddItems" class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    Add More Items
                </h2>
                <div>
                    <input
                        type="file"
                        @change="handleFileChange"
                        accept=".csv,.txt,.json,.xlsx,.xls"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                        required
                    />
                    <p class="mt-2 text-sm text-gray-500">
                        Upload CSV, TXT, JSON, or XLSX file with new items.
                    </p>
                    <InputError :message="addItemsForm.errors.items_file" class="mt-2" />
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton type="button" @click="addItemsModal = false">
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton :disabled="addItemsForm.processing">
                        {{ addItemsForm.processing ? 'Uploading...' : 'Upload Items' }}
                    </PrimaryButton>
                </div>
            </form>
        </Modal>

        <!-- Delete Modal -->
        <Modal :show="deleteModal" @close="deleteModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Delete Product
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Are you sure you want to delete "{{ product.name }}"? This will also delete all {{ product.items_count }} items. This action cannot be undone.
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
