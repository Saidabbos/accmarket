<script setup>
import { ref, watch, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const toasts = ref([]);
let toastId = 0;

const addToast = (message, type = 'success') => {
    const id = ++toastId;
    toasts.value.push({ id, message, type });

    setTimeout(() => {
        removeToast(id);
    }, 5000);
};

const removeToast = (id) => {
    toasts.value = toasts.value.filter(t => t.id !== id);
};

watch(() => page.props.flash, (flash) => {
    if (flash?.success) {
        addToast(flash.success, 'success');
    }
    if (flash?.error) {
        addToast(flash.error, 'error');
    }
}, { immediate: true, deep: true });

onMounted(() => {
    if (page.props.flash?.success) {
        addToast(page.props.flash.success, 'success');
    }
    if (page.props.flash?.error) {
        addToast(page.props.flash.error, 'error');
    }
});
</script>

<template>
    <div class="fixed top-4 right-4 z-[100] flex flex-col gap-2">
        <TransitionGroup name="toast">
            <div
                v-for="toast in toasts"
                :key="toast.id"
                class="flex items-center gap-3 px-4 py-3 rounded-lg shadow-lg min-w-[300px] max-w-md"
                :class="{
                    'bg-green-600 text-white': toast.type === 'success',
                    'bg-red-600 text-white': toast.type === 'error',
                }"
            >
                <!-- Icon -->
                <div class="flex-shrink-0">
                    <svg v-if="toast.type === 'success'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>

                <!-- Message -->
                <p class="flex-1 text-sm font-medium">{{ toast.message }}</p>

                <!-- Close button -->
                <button
                    @click="removeToast(toast.id)"
                    class="flex-shrink-0 p-1 rounded hover:bg-white/20 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>

<style scoped>
.toast-enter-active {
    animation: slideIn 0.3s ease-out;
}

.toast-leave-active {
    animation: slideOut 0.3s ease-in;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(100%);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes slideOut {
    from {
        opacity: 1;
        transform: translateX(0);
    }
    to {
        opacity: 0;
        transform: translateX(100%);
    }
}
</style>
