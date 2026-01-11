<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    product: Object,
    categories: Array,
});

const form = useForm({
    name: props.product.name,
    description: props.product.description || '',
    meta_title: props.product.meta_title || '',
    meta_description: props.product.meta_description || '',
    meta_keywords: props.product.meta_keywords || '',
    price: props.product.price,
    category_id: props.product.category_id,
    items_file: null,
});

const submit = () => {
    form.transform(data => ({
        ...data,
        _method: 'PUT',
    })).post(route('seller.products.update', props.product.id), {
        forceFormData: true,
    });
};

const handleFileChange = (event) => {
    form.items_file = event.target.files[0];
};

const flatCategories = () => {
    const result = [];
    props.categories.forEach(parent => {
        result.push({ id: parent.id, name: parent.name, isParent: true });
        if (parent.children) {
            parent.children.forEach(child => {
                result.push({ id: child.id, name: `— ${child.name}`, isParent: false });
            });
        }
    });
    return result;
};
</script>

<template>
    <Head :title="`Edit: ${product.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">
                Edit Product
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <!-- Current Stock Info -->
                        <div class="bg-blue-50 dark:bg-blue-900/30 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-blue-900 dark:text-blue-100">Current Stock</p>
                                    <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                        {{ product.available_items_count }} / {{ product.items_count }}
                                    </p>
                                    <p class="text-xs text-blue-700 dark:text-blue-300">available / total items</p>
                                </div>
                                <Link :href="route('seller.products.show', product.id)" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-sm">
                                    View Items →
                                </Link>
                            </div>
                        </div>

                        <!-- Name -->
                        <div>
                            <InputLabel for="name" value="Product Name" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="form.errors.name" class="mt-2" />
                        </div>

                        <!-- Description -->
                        <div>
                            <InputLabel for="description" value="Description" />
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="4"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Describe your product..."
                            ></textarea>
                            <InputError :message="form.errors.description" class="mt-2" />
                        </div>

                        <!-- SEO Fields -->
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">SEO Settings</h3>

                            <div class="space-y-4">
                                <div>
                                    <InputLabel for="meta_title" value="Meta Title (for search engines)" />
                                    <TextInput
                                        id="meta_title"
                                        v-model="form.meta_title"
                                        type="text"
                                        class="mt-1 block w-full"
                                        placeholder="Leave empty to use product name"
                                    />
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        {{ form.meta_title.length }}/60 characters recommended
                                    </p>
                                    <InputError :message="form.errors.meta_title" class="mt-2" />
                                </div>

                                <div>
                                    <InputLabel for="meta_description" value="Meta Description" />
                                    <textarea
                                        id="meta_description"
                                        v-model="form.meta_description"
                                        rows="2"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        placeholder="Brief description for search results..."
                                    ></textarea>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        {{ form.meta_description.length }}/160 characters recommended
                                    </p>
                                    <InputError :message="form.errors.meta_description" class="mt-2" />
                                </div>

                                <div>
                                    <InputLabel for="meta_keywords" value="Meta Keywords" />
                                    <TextInput
                                        id="meta_keywords"
                                        v-model="form.meta_keywords"
                                        type="text"
                                        class="mt-1 block w-full"
                                        placeholder="keyword1, keyword2, keyword3..."
                                    />
                                    <InputError :message="form.errors.meta_keywords" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Price -->
                        <div>
                            <InputLabel for="price" value="Price ($)" />
                            <TextInput
                                id="price"
                                v-model="form.price"
                                type="number"
                                step="0.01"
                                min="0.01"
                                max="99999.99"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="form.errors.price" class="mt-2" />
                        </div>

                        <!-- Category -->
                        <div>
                            <InputLabel for="category_id" value="Category" />
                            <select
                                id="category_id"
                                v-model="form.category_id"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            >
                                <option value="">Select a category</option>
                                <option
                                    v-for="category in flatCategories()"
                                    :key="category.id"
                                    :value="category.id"
                                    :class="category.isParent ? 'font-semibold' : ''"
                                >
                                    {{ category.name }}
                                </option>
                            </select>
                            <InputError :message="form.errors.category_id" class="mt-2" />
                        </div>

                        <!-- Add More Items -->
                        <div>
                            <InputLabel for="items_file" value="Add More Items (Optional)" />
                            <div class="mt-1">
                                <input
                                    id="items_file"
                                    type="file"
                                    @change="handleFileChange"
                                    accept=".csv,.txt,.json,.xlsx,.xls"
                                    class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 dark:file:bg-indigo-900/50 file:text-indigo-700 dark:file:text-indigo-300 hover:file:bg-indigo-100 dark:hover:file:bg-indigo-900"
                                />
                            </div>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Upload a file to add more items to this product. Existing items will not be affected.
                            </p>
                            <InputError :message="form.errors.items_file" class="mt-2" />
                        </div>

                        <!-- Submit -->
                        <div class="flex justify-end space-x-3">
                            <Link :href="route('seller.products.index')">
                                <SecondaryButton type="button">Cancel</SecondaryButton>
                            </Link>
                            <PrimaryButton :disabled="form.processing">
                                {{ form.processing ? 'Saving...' : 'Save Changes' }}
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
