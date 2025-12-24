<script setup>
import { ref, computed, onMounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    categories: Array,
    currentCategory: String,
});

const page = usePage();
const expandedCategories = ref(new Set());

// Auto-expand parent category if a child is currently selected
onMounted(() => {
    if (props.currentCategory) {
        const currentCat = props.categories?.find(cat => cat.slug === props.currentCategory);
        if (currentCat && currentCat.parent_id) {
            expandedCategories.value.add(currentCat.parent_id);
        }
    }
});

// Group categories by parent
const parentCategories = computed(() => {
    return props.categories?.filter(cat => !cat.parent_id) || [];
});

const getChildCategories = (parentId) => {
    return props.categories?.filter(cat => cat.parent_id === parentId) || [];
};

const toggleCategory = (categoryId) => {
    if (expandedCategories.value.has(categoryId)) {
        expandedCategories.value.delete(categoryId);
    } else {
        expandedCategories.value.add(categoryId);
    }
};

const isExpanded = (categoryId) => {
    return expandedCategories.value.has(categoryId);
};

const isActive = (categorySlug) => {
    return props.currentCategory === categorySlug;
};

// Icon mapping based on category name
const getCategoryIcon = (categoryName) => {
    const name = categoryName.toLowerCase();

    // Gaming platforms
    if (name.includes('steam')) return '🎮';
    if (name.includes('playstation') || name.includes('ps')) return '🎮';
    if (name.includes('xbox')) return '🎮';
    if (name.includes('epic') || name.includes('game')) return '🎮';

    // Streaming services
    if (name.includes('netflix')) return '📺';
    if (name.includes('spotify')) return '🎵';
    if (name.includes('disney')) return '🎬';
    if (name.includes('hbo') || name.includes('hulu')) return '📺';

    // Social media
    if (name.includes('instagram')) return '📷';
    if (name.includes('twitter') || name.includes('x')) return '🐦';
    if (name.includes('tiktok')) return '🎵';
    if (name.includes('facebook')) return '👥';
    if (name.includes('linkedin')) return '💼';
    if (name.includes('telegram')) return '✈️';

    // Software
    if (name.includes('windows')) return '🪟';
    if (name.includes('office')) return '📄';
    if (name.includes('antivirus')) return '🛡️';
    if (name.includes('vpn')) return '🔒';

    // Gift cards
    if (name.includes('gift') || name.includes('card')) return '🎁';
    if (name.includes('amazon')) return '📦';
    if (name.includes('itunes') || name.includes('apple')) return '🍎';
    if (name.includes('google')) return '🔍';

    // Default
    return '📦';
};
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="p-4 border-b border-gray-100 dark:border-gray-700">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                </svg>
                Categories
            </h2>
        </div>

        <div class="overflow-y-auto max-h-[calc(100vh-200px)]">
            <!-- All Products Link -->
            <Link
                :href="route('shop.index')"
                class="flex items-center px-4 py-2.5 text-sm font-medium transition-colors border-b border-gray-100 dark:border-gray-700"
                :class="!currentCategory
                    ? 'text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30'
                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50'"
            >
                <span class="mr-2">🏪</span>
                All Products
            </Link>

            <!-- Parent Categories with Children -->
            <div v-for="parent in parentCategories" :key="parent.id" class="border-b border-gray-100 dark:border-gray-700">
                <button
                    @click="toggleCategory(parent.id)"
                    class="w-full flex items-center justify-between px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                >
                    <div class="flex items-center">
                        <span class="mr-2">{{ getCategoryIcon(parent.name) }}</span>
                        <span>{{ parent.name }}</span>
                    </div>
                    <svg
                        class="w-4 h-4 transition-transform"
                        :class="isExpanded(parent.id) ? 'rotate-180' : ''"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <!-- Child Categories -->
                <div
                    v-if="isExpanded(parent.id)"
                    class="bg-gray-50 dark:bg-gray-900/50"
                >
                    <Link
                        v-for="child in getChildCategories(parent.id)"
                        :key="child.id"
                        :href="route('shop.index', { category: child.slug })"
                        class="flex items-center px-4 pl-10 py-2 text-sm transition-colors"
                        :class="isActive(child.slug)
                            ? 'text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 font-medium'
                            : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800/50'"
                    >
                        {{ child.name }}
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
