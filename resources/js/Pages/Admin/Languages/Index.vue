<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    languages: Array,
});

const showAddModal = ref(false);
const editingLanguage = ref(null);

const addForm = useForm({
    code: '',
    name: '',
    native_name: '',
});

const editForm = useForm({
    name: '',
    native_name: '',
});

const openAddModal = () => {
    addForm.reset();
    showAddModal.value = true;
};

const closeAddModal = () => {
    showAddModal.value = false;
    addForm.reset();
};

const submitAdd = () => {
    addForm.post(route('admin.languages.store'), {
        onSuccess: () => closeAddModal(),
    });
};

const openEditModal = (language) => {
    editingLanguage.value = language;
    editForm.name = language.name;
    editForm.native_name = language.native_name;
};

const closeEditModal = () => {
    editingLanguage.value = null;
    editForm.reset();
};

const submitEdit = () => {
    editForm.put(route('admin.languages.update', editingLanguage.value.id), {
        onSuccess: () => closeEditModal(),
    });
};

const toggleActive = (language) => {
    router.patch(route('admin.languages.toggle', language.id));
};

const setDefault = (language) => {
    router.patch(route('admin.languages.default', language.id));
};

const deleteLanguage = (language) => {
    if (confirm(`Are you sure you want to delete ${language.name}?`)) {
        router.delete(route('admin.languages.destroy', language.id));
    }
};
</script>

<template>
    <Head title="Languages" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Languages
                </h2>
                <button
                    @click="openAddModal"
                    class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700"
                >
                    Add Language
                </button>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Code</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Native Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Default</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="language in languages" :key="language.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    {{ language.code }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                    {{ language.name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                    {{ language.native_name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button
                                        @click="toggleActive(language)"
                                        :class="[
                                            'px-2.5 py-1 rounded-full text-xs font-medium transition-colors',
                                            language.is_active
                                                ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400'
                                                : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400'
                                        ]"
                                        :disabled="language.is_default"
                                    >
                                        {{ language.is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button
                                        v-if="!language.is_default"
                                        @click="setDefault(language)"
                                        class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-800"
                                    >
                                        Set Default
                                    </button>
                                    <span v-else class="px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-400">
                                        Default
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button
                                        @click="openEditModal(language)"
                                        class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 mr-3"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        v-if="!language.is_default"
                                        @click="deleteLanguage(language)"
                                        class="text-red-600 dark:text-red-400 hover:text-red-900"
                                    >
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Add Modal -->
        <div v-if="showAddModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black/50" @click="closeAddModal"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Add Language</h3>
                    <form @submit.prevent="submitAdd">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Code</label>
                                <input
                                    v-model="addForm.code"
                                    type="text"
                                    placeholder="en, ru, uz..."
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                />
                                <p v-if="addForm.errors.code" class="mt-1 text-sm text-red-600">{{ addForm.errors.code }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name</label>
                                <input
                                    v-model="addForm.name"
                                    type="text"
                                    placeholder="English"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                />
                                <p v-if="addForm.errors.name" class="mt-1 text-sm text-red-600">{{ addForm.errors.name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Native Name</label>
                                <input
                                    v-model="addForm.native_name"
                                    type="text"
                                    placeholder="English"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                />
                                <p v-if="addForm.errors.native_name" class="mt-1 text-sm text-red-600">{{ addForm.errors.native_name }}</p>
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end gap-3">
                            <button
                                type="button"
                                @click="closeAddModal"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="addForm.processing"
                                class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 disabled:opacity-50"
                            >
                                {{ addForm.processing ? 'Adding...' : 'Add Language' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div v-if="editingLanguage" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black/50" @click="closeEditModal"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Edit Language</h3>
                    <form @submit.prevent="submitEdit">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Code</label>
                                <input
                                    :value="editingLanguage.code"
                                    type="text"
                                    disabled
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white bg-gray-100 dark:bg-gray-600"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name</label>
                                <input
                                    v-model="editForm.name"
                                    type="text"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                />
                                <p v-if="editForm.errors.name" class="mt-1 text-sm text-red-600">{{ editForm.errors.name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Native Name</label>
                                <input
                                    v-model="editForm.native_name"
                                    type="text"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                />
                                <p v-if="editForm.errors.native_name" class="mt-1 text-sm text-red-600">{{ editForm.errors.native_name }}</p>
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end gap-3">
                            <button
                                type="button"
                                @click="closeEditModal"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="editForm.processing"
                                class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 disabled:opacity-50"
                            >
                                {{ editForm.processing ? 'Saving...' : 'Save Changes' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
