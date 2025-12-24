<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

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

    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <div>
                    <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Categories</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Manage product categories</p>
                </div>
                <Link
                    :href="route('admin.categories.create')"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Category
                </Link>
            </div>
        </template>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
            <!-- Category Tree -->
            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                <template v-for="parent in categories" :key="parent.id">
                    <!-- Parent Category -->
                    <div>
                        <div class="px-6 py-4 flex items-center justify-between bg-gray-50 dark:bg-gray-800/50">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ parent.name }}
                                    </span>
                                    <span class="ml-2 text-xs text-gray-500 dark:text-gray-400">
                                        ({{ parent.children?.length || 0 }} subcategories)
                                    </span>
                                </div>
                                <span
                                    :class="[
                                        'px-2 py-0.5 text-xs font-medium rounded-full',
                                        parent.is_active
                                            ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
                                            : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
                                    ]"
                                >
                                    {{ parent.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <button
                                    @click="toggleStatus(parent)"
                                    class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors"
                                >
                                    {{ parent.is_active ? 'Disable' : 'Enable' }}
                                </button>
                                <Link
                                    :href="route('admin.categories.edit', parent.id)"
                                    class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 transition-colors"
                                >
                                    Edit
                                </Link>
                                <button
                                    @click="confirmDelete(parent)"
                                    class="text-sm text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 transition-colors"
                                    :disabled="parent.children?.length > 0"
                                    :class="{ 'opacity-50 cursor-not-allowed': parent.children?.length > 0 }"
                                >
                                    Delete
                                </button>
                            </div>
                        </div>

                        <!-- Child Categories -->
                        <div v-if="parent.children?.length" class="divide-y divide-gray-100 dark:divide-gray-700">
                            <div
                                v-for="child in parent.children"
                                :key="child.id"
                                class="px-6 py-3 pl-16 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                            >
                                <div class="flex items-center space-x-3">
                                    <span class="text-gray-300 dark:text-gray-600">└</span>
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ child.name }}</span>
                                    <span
                                        :class="[
                                            'px-2 py-0.5 text-xs font-medium rounded-full',
                                            child.is_active
                                                ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
                                                : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
                                        ]"
                                    >
                                        {{ child.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <button
                                        @click="toggleStatus(child)"
                                        class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors"
                                    >
                                        {{ child.is_active ? 'Disable' : 'Enable' }}
                                    </button>
                                    <Link
                                        :href="route('admin.categories.edit', child.id)"
                                        class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 transition-colors"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        @click="confirmDelete(child)"
                                        class="text-sm text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 transition-colors"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Empty State -->
                <div v-if="categories.length === 0" class="p-12 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                        </svg>
                    </div>
                    <p class="text-gray-500 dark:text-gray-400 mb-4">No categories found</p>
                    <Link
                        :href="route('admin.categories.create')"
                        class="text-indigo-600 dark:text-indigo-400 hover:underline"
                    >
                        Create your first category
                    </Link>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div v-if="deleteModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-900/50 dark:bg-gray-900/80" @click="deleteModal = false"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-xl max-w-md w-full p-6 shadow-xl">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Delete Category</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                        Are you sure you want to delete "<strong class="text-gray-900 dark:text-white">{{ categoryToDelete?.name }}</strong>"? This action cannot be undone.
                    </p>
                    <div class="flex justify-end space-x-3">
                        <button
                            @click="deleteModal = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            @click="deleteCategory"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors"
                        >
                            Delete Category
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
