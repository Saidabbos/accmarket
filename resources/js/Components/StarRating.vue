<script setup>
import { computed } from 'vue';

const props = defineProps({
    rating: {
        type: Number,
        default: 0,
    },
    reviewsCount: {
        type: Number,
        default: 0,
    },
    showCount: {
        type: Boolean,
        default: true,
    },
    size: {
        type: String,
        default: 'sm', // sm, md, lg
    },
});

const sizeClasses = computed(() => {
    const sizes = {
        xs: 'w-3 h-3',
        sm: 'w-4 h-4',
        md: 'w-5 h-5',
        lg: 'w-6 h-6',
    };
    return sizes[props.size] || sizes.sm;
});

const textSizeClasses = computed(() => {
    const sizes = {
        xs: 'text-xs',
        sm: 'text-sm',
        md: 'text-base',
        lg: 'text-lg',
    };
    return sizes[props.size] || sizes.sm;
});

const roundedRating = computed(() => {
    return Math.round(props.rating * 10) / 10;
});
</script>

<template>
    <div class="flex items-center space-x-1">
        <!-- Stars -->
        <div class="flex items-center">
            <svg
                v-for="star in 5"
                :key="star"
                :class="[
                    sizeClasses,
                    star <= Math.floor(rating) ? 'text-amber-500 fill-current' :
                    star === Math.ceil(rating) && rating % 1 !== 0 ? 'text-amber-500' :
                    'text-gray-300 dark:text-gray-600'
                ]"
                viewBox="0 0 20 20"
            >
                <defs v-if="star === Math.ceil(rating) && rating % 1 !== 0">
                    <linearGradient :id="`half-${star}`">
                        <stop offset="50%" stop-color="currentColor" class="text-amber-500" />
                        <stop offset="50%" stop-color="currentColor" class="text-gray-300 dark:text-gray-600" />
                    </linearGradient>
                </defs>
                <path
                    :fill="star === Math.ceil(rating) && rating % 1 !== 0 ? `url(#half-${star})` : 'currentColor'"
                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                />
            </svg>
        </div>

        <!-- Rating Number & Count -->
        <div v-if="rating > 0" class="flex items-center space-x-1">
            <span :class="[textSizeClasses, 'font-medium text-gray-700 dark:text-gray-300']">
                {{ roundedRating }}
            </span>
            <span v-if="showCount && reviewsCount > 0" :class="[textSizeClasses, 'text-gray-500 dark:text-gray-400']">
                ({{ reviewsCount }})
            </span>
        </div>
        <span v-else :class="[textSizeClasses, 'text-gray-500 dark:text-gray-400']">
            No reviews yet
        </span>
    </div>
</template>
