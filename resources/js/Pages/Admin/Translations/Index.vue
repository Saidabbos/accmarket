<script setup>
import { ref, watch } from 'vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    languages: Array,
    groups: Array,
    translations: Object,
    filters: Object,
});

const locale = ref(props.filters.locale);
const group = ref(props.filters.group);
const search = ref(props.filters.search);
const showAddModal = ref(false);
const editingTranslation = ref(null);

const addForm = useForm({
    locale: props.filters.locale,
    group: '',
    key: '',
    value: '',
});

const editForm = useForm({
    value: '',
});

const applyFilters = debounce(() => {
    router.get(route('admin.translations.index'), {
        locale: locale.value,
        group: group.value || undefined,
        search: search.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
}, 300);

watch([locale, group], applyFilters);
watch(search, debounce(applyFilters, 500));

const openAddModal = () => {
    addForm.locale = locale.value;
    addForm.group = group.value || '';
    addForm.key = '';
    addForm.value = '';
    showAddModal.value = true;
};

const closeAddModal = () => {
    showAddModal.value = false;
    addForm.reset();
};

const submitAdd = () => {
    addForm.post(route('admin.translations.store'), {
        onSuccess: () => closeAddModal(),
    });
};

const openEditModal = (translation) => {
    editingTranslation.value = translation;
    editForm.value = translation.value;
};

const closeEditModal = () => {
    editingTranslation.value = null;
    editForm.reset();
};

const submitEdit = () => {
    editForm.put(route('admin.translations.update', editingTranslation.value.id), {
        onSuccess: () => closeEditModal(),
    });
};

const deleteTranslation = (translation) => {
    if (confirm('Are you sure you want to delete this translation?')) {
        router.delete(route('admin.translations.destroy', translation.id));
    }
};

const importTranslations = () => {
    router.post(route('admin.translations.import'), {
        locale: locale.value,
    });
};

const importAllTranslations = () => {
    if (confirm('This will import translations from all language files. Continue?')) {
        router.post(route('admin.translations.import-all'));
    }
};
</script>

<template>
    <Head title="Translations" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Translations
                </h2>
                <div class="flex items-center gap-2">
                    <button
                        @click="importAllTranslations"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600"
                    >
                        Import All
                    </button>
                    <button
                        @click="openAddModal"
                        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700"
                    >
                        Add Translation
                    </button>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Language</label>
                            <select
                                v-model="locale"
                                class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            >
                                <option v-for="lang in languages" :key="lang.code" :value="lang.code">
                                    {{ lang.name }} ({{ lang.code }})
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Group</label>
                            <select
                                v-model="group"
                                class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            >
                                <option value="">All Groups</option>
                                <option v-for="g in groups" :key="g" :value="g">
                                    {{ g }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Search</label>
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search key or value..."
                                class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            />
                        </div>
                    </div>
                </div>

                <!-- Translations Table -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Group</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Key</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Value</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="translation in translations.data" :key="translation.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 py-1 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-mono text-xs">
                                        {{ translation.group }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white font-mono">
                                    {{ translation.key }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300 max-w-md truncate">
                                    {{ translation.value }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button
                                        @click="openEditModal(translation)"
                                        class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 mr-3"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        @click="deleteTranslation(translation)"
                                        class="text-red-600 dark:text-red-400 hover:text-red-900"
                                    >
                                        Delete
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="translations.data.length === 0">
                                <td colspan="4" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    No translations found.
                                    <button @click="importTranslations" class="text-indigo-600 dark:text-indigo-400 hover:underline ml-1">
                                        Import from file?
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div v-if="translations.links && translations.links.length > 3" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-center space-x-1">
                            <template v-for="link in translations.links" :key="link.label">
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

        <!-- Add Modal -->
        <div v-if="showAddModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black/50" @click="closeAddModal"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-lg w-full p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Add Translation</h3>
                    <form @submit.prevent="submitAdd">
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Language</label>
                                    <select
                                        v-model="addForm.locale"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    >
                                        <option v-for="lang in languages" :key="lang.code" :value="lang.code">
                                            {{ lang.name }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Group</label>
                                    <input
                                        v-model="addForm.group"
                                        type="text"
                                        placeholder="nav, shop, auth..."
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    />
                                    <p v-if="addForm.errors.group" class="mt-1 text-sm text-red-600">{{ addForm.errors.group }}</p>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Key</label>
                                <input
                                    v-model="addForm.key"
                                    type="text"
                                    placeholder="login, register..."
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                />
                                <p v-if="addForm.errors.key" class="mt-1 text-sm text-red-600">{{ addForm.errors.key }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Value</label>
                                <textarea
                                    v-model="addForm.value"
                                    rows="3"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                ></textarea>
                                <p v-if="addForm.errors.value" class="mt-1 text-sm text-red-600">{{ addForm.errors.value }}</p>
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
                                {{ addForm.processing ? 'Adding...' : 'Add Translation' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div v-if="editingTranslation" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black/50" @click="closeEditModal"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-lg w-full p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Edit Translation</h3>
                    <form @submit.prevent="submitEdit">
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Group</label>
                                    <input
                                        :value="editingTranslation.group"
                                        type="text"
                                        disabled
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-600 dark:text-white bg-gray-100"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Key</label>
                                    <input
                                        :value="editingTranslation.key"
                                        type="text"
                                        disabled
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-600 dark:text-white bg-gray-100"
                                    />
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Value</label>
                                <textarea
                                    v-model="editForm.value"
                                    rows="4"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                ></textarea>
                                <p v-if="editForm.errors.value" class="mt-1 text-sm text-red-600">{{ editForm.errors.value }}</p>
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
