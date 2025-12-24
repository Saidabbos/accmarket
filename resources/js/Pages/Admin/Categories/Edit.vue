<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import Checkbox from '@/Components/Checkbox.vue';

const props = defineProps({
    category: Object,
    parentCategories: Array,
});

const form = useForm({
    name: props.category.name,
    description: props.category.description || '',
    parent_id: props.category.parent_id || '',
    sort_order: props.category.sort_order || 0,
    is_active: props.category.is_active,
});

const submit = () => {
    form.put(route('admin.categories.update', props.category.id));
};
</script>

<template>
    <Head :title="`Edit: ${category.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Edit Category
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <!-- Category Info -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-500">Slug:</span>
                                    <span class="ml-2 font-mono text-gray-900">{{ category.slug }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Products:</span>
                                    <span class="ml-2 font-medium text-gray-900">{{ category.products_count || 0 }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Name -->
                        <div>
                            <InputLabel for="name" value="Category Name" />
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
                            <InputLabel for="description" value="Description (Optional)" />
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Brief description of this category..."
                            ></textarea>
                            <InputError :message="form.errors.description" class="mt-2" />
                        </div>

                        <!-- Parent Category -->
                        <div>
                            <InputLabel for="parent_id" value="Parent Category" />
                            <select
                                id="parent_id"
                                v-model="form.parent_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                :disabled="category.children_count > 0"
                            >
                                <option value="">None (Top Level)</option>
                                <option v-for="parent in parentCategories" :key="parent.id" :value="parent.id">
                                    {{ parent.name }}
                                </option>
                            </select>
                            <p v-if="category.children_count > 0" class="mt-1 text-sm text-amber-600">
                                Cannot change parent for categories with subcategories.
                            </p>
                            <InputError :message="form.errors.parent_id" class="mt-2" />
                        </div>

                        <!-- Sort Order -->
                        <div>
                            <InputLabel for="sort_order" value="Sort Order" />
                            <TextInput
                                id="sort_order"
                                v-model="form.sort_order"
                                type="number"
                                min="0"
                                class="mt-1 block w-32"
                            />
                            <p class="mt-1 text-sm text-gray-500">Lower numbers appear first.</p>
                            <InputError :message="form.errors.sort_order" class="mt-2" />
                        </div>

                        <!-- Is Active -->
                        <div class="flex items-center">
                            <Checkbox id="is_active" v-model:checked="form.is_active" />
                            <InputLabel for="is_active" value="Active" class="ml-2" />
                        </div>

                        <!-- Submit -->
                        <div class="flex justify-end space-x-3">
                            <Link :href="route('admin.categories.index')">
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
