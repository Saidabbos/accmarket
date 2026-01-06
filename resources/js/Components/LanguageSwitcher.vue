<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useTranslations } from '@/composables/useTranslations';

const { locale, locales } = useTranslations();
const isOpen = ref(false);
const dropdownRef = ref(null);

const flags = {
    en: '🇬🇧',
    ru: '🇷🇺',
    uz: '🇺🇿',
};

const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
};

const closeDropdown = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', closeDropdown);
});

onUnmounted(() => {
    document.removeEventListener('click', closeDropdown);
});
</script>

<template>
    <div ref="dropdownRef" class="relative">
        <button
            @click="toggleDropdown"
            class="flex items-center space-x-1 px-2 py-1.5 text-sm font-medium rounded-lg text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
        >
            <span class="text-base">{{ flags[locale] }}</span>
            <span class="hidden sm:inline">{{ locales[locale] }}</span>
            <svg
                class="w-4 h-4 transition-transform"
                :class="isOpen ? 'rotate-180' : ''"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <!-- Dropdown -->
        <div
            v-if="isOpen"
            class="absolute right-0 top-full mt-1 w-40 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-100 dark:border-gray-700 py-1 z-50"
        >
            <Link
                v-for="(name, code) in locales"
                :key="code"
                :href="route('language.switch', { locale: code })"
                class="flex items-center space-x-2 px-3 py-2 text-sm transition-colors"
                :class="[
                    locale === code
                        ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400'
                        : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                ]"
                @click="isOpen = false"
            >
                <span class="text-base">{{ flags[code] }}</span>
                <span>{{ name }}</span>
                <svg
                    v-if="locale === code"
                    class="w-4 h-4 ml-auto"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                >
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
            </Link>
        </div>
    </div>
</template>
