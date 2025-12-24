<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    categories: Array,
});

const deleteModal = ref(false);
const categoryToDelete = ref(null);

const confirmDelete = (category) => {
    categoryToDelete.value = category;
    deleteModal.value = true;
};

const deleteCategory = () => {
    router.delete(route('admin.categories.destroy', categoryToDelete.value.id), {
        onSuccess: () => {
            deleteModal.value = false;
            categoryToDelete.value = null;
        },
    });
};

const toggleStatus = (category) => {
    router.patch(route('admin.categories.toggle', category.id));
};
</script>

<template>
    <Head title="Manage Categories" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Manage Categories
                </h2>
                <Link :href="route('admin.categories.create')">
                    <PrimaryButton>Add Category</PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Category Tree -->
                        <div class="space-y-4">
                            <template v-for="parent in categories" :key="parent.id">
                                <!-- Parent Category -->
                                <div class="border rounded-lg overflow-hidden">
                                    <div class="bg-gray-50 px-4 py-3 flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <span class="text-lg font-medium text-gray-900">
                                                {{ parent.name }}
                                            </span>
                                            <span
                                                :class="[
                                                    'px-2 py-0.5 text-xs font-medium rounded-full',
                                                    parent.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                                ]"
                                            >
                                                {{ parent.is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                            <span class="text-sm text-gray-500">
                                                ({{ parent.children?.length || 0 }} subcategories)
                                            </span>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <button
                                                @click="toggleStatus(parent)"
                                                class="text-sm text-gray-600 hover:text-gray-900"
                                            >
                                                {{ parent.is_active ? 'Disable' : 'Enable' }}
                                            </button>
                                            <Link
                                                :href="route('admin.categories.edit', parent.id)"
                                                class="text-indigo-600 hover:text-indigo-900 text-sm"
                                            >
                                                Edit
                                            </Link>
                                            <button
                                                @click="confirmDelete(parent)"
                                                class="text-red-600 hover:text-red-900 text-sm"
                                                :disabled="parent.children?.length > 0"
                                                :class="{ 'opacity-50 cursor-not-allowed': parent.children?.length > 0 }"
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Child Categories -->
                                    <div v-if="parent.children?.length" class="divide-y divide-gray-100">
                                        <div
                                            v-for="child in parent.children"
                                            :key="child.id"
                                            class="px-4 py-3 pl-10 flex items-center justify-between hover:bg-gray-50"
                                        >
                                            <div class="flex items-center space-x-3">
                                                <span class="text-gray-400">└</span>
                                                <span class="text-gray-700">{{ child.name }}</span>
                                                <span
                                                    :class="[
                                                        'px-2 py-0.5 text-xs font-medium rounded-full',
                                                        child.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                                    ]"
                                                >
                                                    {{ child.is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <button
                                                    @click="toggleStatus(child)"
                                                    class="text-sm text-gray-600 hover:text-gray-900"
                                                >
                                                    {{ child.is_active ? 'Disable' : 'Enable' }}
                                                </button>
                                                <Link
                                                    :href="route('admin.categories.edit', child.id)"
                                                    class="text-indigo-600 hover:text-indigo-900 text-sm"
                                                >
                                                    Edit
                                                </Link>
                                                <button
                                                    @click="confirmDelete(child)"
                                                    class="text-red-600 hover:text-red-900 text-sm"
                                                >
                                                    Delete
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <!-- Empty State -->
                            <div v-if="categories.length === 0" class="text-center py-12 text-gray-500">
                                No categories found.
                                <Link :href="route('admin.categories.create')" class="text-indigo-600 hover:underline">
                                    Create your first category
                                </Link>
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
                    Delete Category
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Are you sure you want to delete "{{ categoryToDelete?.name }}"? This action cannot be undone.
                </p>
                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton @click="deleteModal = false">
                        Cancel
                    </SecondaryButton>
                    <DangerButton @click="deleteCategory">
                        Delete Category
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
