<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { useTranslations } from '@/composables/useTranslations';

const { t } = useTranslations();

const props = defineProps({
    show: Boolean,
    product: Object,
});

const emit = defineEmits(['close']);

const quantity = ref(1);
const agreedToTerms = ref(false);
const isProcessing = ref(false);

const maxQuantity = computed(() => props.product?.available_items_count || 0);
const unitPrice = computed(() => parseFloat(props.product?.price || 0));
const totalPrice = computed(() => (unitPrice.value * quantity.value).toFixed(2));

// Reset when modal opens
watch(() => props.show, (newVal) => {
    if (newVal) {
        quantity.value = 1;
        agreedToTerms.value = false;
        isProcessing.value = false;
    }
});

const increaseQty = () => {
    if (quantity.value < maxQuantity.value) {
        quantity.value++;
    }
};

const decreaseQty = () => {
    if (quantity.value > 1) {
        quantity.value--;
    }
};

const handleQuantityInput = (e) => {
    let val = parseInt(e.target.value) || 1;
    if (val < 1) val = 1;
    if (val > maxQuantity.value) val = maxQuantity.value;
    quantity.value = val;
};

const canProceed = computed(() => agreedToTerms.value && quantity.value >= 1 && quantity.value <= maxQuantity.value);

const proceedToCheckout = () => {
    if (!canProceed.value) return;

    isProcessing.value = true;

    // Direct checkout without cart
    router.post(route('checkout.direct'), {
        product_id: props.product.id,
        quantity: quantity.value,
    }, {
        onFinish: () => {
            isProcessing.value = false;
        },
    });
};

const closeModal = () => {
    if (!isProcessing.value) {
        emit('close');
    }
};
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="closeModal"></div>

                <!-- Modal -->
                <div class="flex min-h-full items-center justify-center p-4">
                    <Transition
                        enter-active-class="duration-200 ease-out"
                        enter-from-class="opacity-0 scale-95"
                        enter-to-class="opacity-100 scale-100"
                        leave-active-class="duration-150 ease-in"
                        leave-from-class="opacity-100 scale-100"
                        leave-to-class="opacity-0 scale-95"
                    >
                        <div
                            v-if="show"
                            class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-2xl"
                            @click.stop
                        >
                            <!-- Close button -->
                            <button
                                @click="closeModal"
                                class="absolute top-4 right-4 p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                                :disabled="isProcessing"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>

                            <div class="p-6">
                                <!-- Header -->
                                <div class="text-center mb-6">
                                    <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                    </div>
                                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                                        {{ product?.name }}
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        {{ t('buy.select_quantity') }}
                                    </p>
                                </div>

                                <!-- Price per unit -->
                                <div class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-gray-700">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ t('buy.price_per_item') }}</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">${{ unitPrice.toFixed(2) }}</span>
                                </div>

                                <!-- Quantity selector -->
                                <div class="py-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        {{ t('buy.quantity') }} <span class="text-gray-400 font-normal">({{ t('buy.max') }}: {{ maxQuantity }})</span>
                                    </label>
                                    <div class="flex items-center space-x-3">
                                        <button
                                            @click="decreaseQty"
                                            :disabled="quantity <= 1"
                                            class="w-10 h-10 rounded-lg border border-gray-200 dark:border-gray-600 flex items-center justify-center text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                            </svg>
                                        </button>
                                        <input
                                            type="number"
                                            :value="quantity"
                                            @input="handleQuantityInput"
                                            min="1"
                                            :max="maxQuantity"
                                            class="flex-1 text-center text-lg font-semibold rounded-lg border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500"
                                        />
                                        <button
                                            @click="increaseQty"
                                            :disabled="quantity >= maxQuantity"
                                            class="w-10 h-10 rounded-lg border border-gray-200 dark:border-gray-600 flex items-center justify-center text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Total -->
                                <div class="flex items-center justify-between py-4 border-t border-b border-gray-100 dark:border-gray-700 mb-4">
                                    <span class="text-base font-medium text-gray-900 dark:text-white">{{ t('buy.total') }}</span>
                                    <span class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">${{ totalPrice }}</span>
                                </div>

                                <!-- Terms checkbox -->
                                <div class="mb-6">
                                    <label class="flex items-start space-x-3 cursor-pointer">
                                        <input
                                            type="checkbox"
                                            v-model="agreedToTerms"
                                            class="mt-0.5 w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700"
                                        />
                                        <span class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ t('buy.agree_to') }}
                                            <a href="#" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ t('common.terms') }}</a>
                                            {{ t('buy.and') }}
                                            <a href="#" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ t('common.privacy') }}</a>
                                        </span>
                                    </label>
                                </div>

                                <!-- Action buttons -->
                                <div class="flex space-x-3">
                                    <button
                                        @click="closeModal"
                                        :disabled="isProcessing"
                                        class="flex-1 px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 disabled:opacity-50 transition-colors"
                                    >
                                        {{ t('common.cancel') }}
                                    </button>
                                    <button
                                        @click="proceedToCheckout"
                                        :disabled="!canProceed || isProcessing"
                                        class="flex-1 px-4 py-3 text-sm font-medium text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center justify-center"
                                    >
                                        <svg v-if="isProcessing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        {{ isProcessing ? t('buy.processing') : t('buy.proceed_to_payment') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
