<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    category: Object,
    parentCategories: Array,
});

const form = useForm({
    name: props.category.name,
    description: props.category.description || '',
    icon: null,
    remove_icon: false,
    parent_id: props.category.parent_id || '',
    sort_order: props.category.sort_order || 0,
    is_active: props.category.is_active,
});

const iconPreview = ref(null);
const iconInput = ref(null);

// Existing icon URL
const existingIconUrl = computed(() => {
    if (props.category.icon) {
        return `/storage/${props.category.icon}`;
    }
    return null;
});

const showExistingIcon = computed(() => {
    return existingIconUrl.value && !iconPreview.value && !form.remove_icon;
});

const handleIconChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.icon = file;
        form.remove_icon = false;
        iconPreview.value = URL.createObjectURL(file);
    }
};

const removeIcon = () => {
    form.icon = null;
    form.remove_icon = true;
    iconPreview.value = null;
    if (iconInput.value) {
        iconInput.value.value = '';
    }
};

const removeNewIcon = () => {
    form.icon = null;
    iconPreview.value = null;
    if (iconInput.value) {
        iconInput.value.value = '';
    }
};

const submit = () => {
    router.post(route('admin.categories.update', props.category.id), {
        _method: 'put',
        name: form.name,
        description: form.description,
        icon: form.icon,
        remove_icon: form.remove_icon,
        parent_id: form.parent_id,
        sort_order: form.sort_order,
        is_active: form.is_active,
    }, {
        forceFormData: true,
        preserveScroll: true,
        onError: (errors) => {
            form.errors = errors;
        },
    });
};
</script>

<template>
    <Head :title="`Edit: ${category.name}`" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center space-x-3">
                <Link
                    :href="route('admin.categories.index')"
                    class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </Link>
                <div>
                    <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Edit Category</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ category.name }}</p>
                </div>
            </div>
        </template>

        <div class="max-w-2xl">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-6">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Category Info -->
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Slug:</span>
                                <span class="ml-2 font-mono text-gray-900 dark:text-white">{{ category.slug }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Products:</span>
                                <span class="ml-2 font-medium text-gray-900 dark:text-white">{{ category.products_count || 0 }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Category Name
                        </label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full rounded-lg border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:border-indigo-500 focus:ring-indigo-500"
                        />
                        <p v-if="form.errors.name" class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ form.errors.name }}</p>
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Description <span class="text-gray-400">(Optional)</span>
                        </label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="3"
                            class="w-full rounded-lg border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="Brief description of this category..."
                        ></textarea>
                        <p v-if="form.errors.description" class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ form.errors.description }}</p>
                    </div>

                    <!-- Icon Upload -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Category Icon <span class="text-gray-400">(Optional)</span>
                        </label>
                        <div class="flex items-start gap-4">
                            <!-- Existing Icon Preview -->
                            <div
                                v-if="showExistingIcon"
                                class="relative w-20 h-20 rounded-lg border-2 border-gray-200 dark:border-gray-600 overflow-hidden flex-shrink-0"
                            >
                                <img
                                    :src="existingIconUrl"
                                    alt="Current icon"
                                    class="w-full h-full object-cover"
                                />
                                <button
                                    type="button"
                                    @click="removeIcon"
                                    class="absolute top-1 right-1 p-1 bg-red-500 text-white rounded-full hover:bg-red-600 transition-colors"
                                    title="Remove icon"
                                >
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <!-- New Icon Preview -->
                            <div
                                v-if="iconPreview"
                                class="relative w-20 h-20 rounded-lg border-2 border-indigo-300 dark:border-indigo-600 overflow-hidden flex-shrink-0"
                            >
                                <img
                                    :src="iconPreview"
                                    alt="New icon preview"
                                    class="w-full h-full object-cover"
                                />
                                <button
                                    type="button"
                                    @click="removeNewIcon"
                                    class="absolute top-1 right-1 p-1 bg-red-500 text-white rounded-full hover:bg-red-600 transition-colors"
                                    title="Remove new icon"
                                >
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                                <span class="absolute bottom-0 left-0 right-0 bg-indigo-600 text-white text-xs text-center py-0.5">New</span>
                            </div>
                            <!-- Upload Area -->
                            <div class="flex-1">
                                <label
                                    class="flex flex-col items-center justify-center w-full h-20 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                                >
                                    <div class="flex flex-col items-center justify-center py-2">
                                        <svg class="w-6 h-6 text-gray-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ showExistingIcon ? 'Click to replace' : 'Click to upload icon' }}
                                        </p>
                                    </div>
                                    <input
                                        ref="iconInput"
                                        type="file"
                                        class="hidden"
                                        accept="image/png,image/jpeg,image/jpg,image/webp,image/svg+xml"
                                        @change="handleIconChange"
                                    />
                                </label>
                            </div>
                        </div>
                        <p class="mt-1.5 text-sm text-gray-500 dark:text-gray-400">
                            Recommended: 128x128px, PNG/WebP/SVG, max 512KB
                        </p>
                        <p v-if="form.remove_icon" class="mt-1.5 text-sm text-amber-600 dark:text-amber-400">
                            Icon will be removed when you save.
                        </p>
                        <p v-if="form.errors.icon" class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ form.errors.icon }}</p>
                    </div>

                    <!-- Parent Category -->
                    <div>
                        <label for="parent_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Parent Category
                        </label>
                        <select
                            id="parent_id"
                            v-model="form.parent_id"
                            class="w-full rounded-lg border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:opacity-50"
                            :disabled="category.children_count > 0"
                        >
                            <option value="">None (Top Level)</option>
                            <option v-for="parent in parentCategories" :key="parent.id" :value="parent.id">
                                {{ parent.name }}
                            </option>
                        </select>
                        <p v-if="category.children_count > 0" class="mt-1.5 text-sm text-amber-600 dark:text-amber-400">
                            Cannot change parent for categories with subcategories.
                        </p>
                        <p v-if="form.errors.parent_id" class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ form.errors.parent_id }}</p>
                    </div>

                    <!-- Sort Order -->
                    <div>
                        <label for="sort_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Sort Order
                        </label>
                        <input
                            id="sort_order"
                            v-model="form.sort_order"
                            type="number"
                            min="0"
                            class="w-32 rounded-lg border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:border-indigo-500 focus:ring-indigo-500"
                        />
                        <p class="mt-1.5 text-sm text-gray-500 dark:text-gray-400">Lower numbers appear first.</p>
                        <p v-if="form.errors.sort_order" class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ form.errors.sort_order }}</p>
                    </div>

                    <!-- Is Active -->
                    <div class="flex items-center">
                        <input
                            id="is_active"
                            v-model="form.is_active"
                            type="checkbox"
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600 rounded"
                        />
                        <label for="is_active" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            Active
                        </label>
                    </div>

                    <!-- Submit -->
                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <Link
                            :href="route('admin.categories.index')"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 disabled:opacity-50 transition-colors"
                        >
                            {{ form.processing ? 'Saving...' : 'Save Changes' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
