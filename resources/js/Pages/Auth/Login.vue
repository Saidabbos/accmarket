<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { useTranslations } from '@/composables/useTranslations';
import { onMounted, ref } from 'vue';

const { t } = useTranslations();

const props = defineProps({
    telegramBotUsername: String,
});

const page = usePage();
const telegramError = ref(page.props.errors?.telegram || null);

// Telegram Login Widget callback
const onTelegramAuth = (user) => {
    // Build callback URL with user data
    const params = new URLSearchParams({
        id: user.id,
        first_name: user.first_name || '',
        last_name: user.last_name || '',
        username: user.username || '',
        photo_url: user.photo_url || '',
        auth_date: user.auth_date,
        hash: user.hash,
    });

    window.location.href = route('auth.telegram.callback') + '?' + params.toString();
};

// Make callback available globally for Telegram widget
onMounted(() => {
    window.onTelegramAuth = onTelegramAuth;

    // Telegram widget scriptini dinamik yuklash
    if (props.telegramBotUsername) {
        const container = document.getElementById('telegram-login-container');
        if (container) {
            container.innerHTML = '';
            const script = document.createElement('script');
            script.src = 'https://telegram.org/js/telegram-widget.js?22';
            script.setAttribute('data-telegram-login', props.telegramBotUsername);
            script.setAttribute('data-size', 'large');
            script.setAttribute('data-radius', '12');
            script.setAttribute('data-onauth', 'onTelegramAuth(user)');
            script.setAttribute('data-request-access', 'write');
            script.async = true;
            container.appendChild(script);
        }
    }
});
</script>

<template>
    <Head :title="t('auth.login')" />

    <AppLayout>
        <div class="min-h-[70vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
            <div class="w-full max-w-md">
                <!-- Card -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 p-8">
                    <!-- Header -->
                    <div class="text-center mb-8">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-[#0088cc] to-[#229ED9] flex items-center justify-center">
                            <!-- Telegram Icon -->
                            <svg class="w-9 h-9 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                            </svg>
                        </div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('auth.login') }}</h1>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ t('auth.telegram_login_subtitle') }}</p>
                    </div>

                    <!-- Error Message -->
                    <div v-if="telegramError" class="mb-6 p-4 rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-sm text-red-600 dark:text-red-400">{{ telegramError }}</p>
                        </div>
                    </div>

                    <!-- Telegram Login Button -->
                    <div class="flex flex-col items-center space-y-6">
                        <!-- Telegram Widget Container -->
                        <div id="telegram-login-container" class="flex justify-center min-h-[40px]">
                            <!-- Telegram widget bu yerga dinamik yuklanadi -->
                        </div>

                        <!-- Manual Telegram Button (fallback) -->
                        <a
                            v-if="telegramBotUsername"
                            :href="`https://t.me/${telegramBotUsername}?start=login`"
                            target="_blank"
                            class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-[#0088cc] hover:bg-[#006da8] text-white font-medium rounded-xl transition-colors w-full"
                        >
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                            </svg>
                            {{ t('auth.login_with_telegram') }}
                        </a>

                        <!-- Info -->
                        <div class="text-center">
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ t('auth.telegram_login_info') }}
                            </p>
                        </div>
                    </div>

                    <!-- Divider -->
                    <div class="relative my-8">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-200 dark:border-gray-700"></div>
                        </div>
                        <div class="relative flex justify-center text-xs uppercase">
                            <span class="bg-white dark:bg-gray-800 px-3 text-gray-400">{{ t('auth.secure_login') }}</span>
                        </div>
                    </div>

                    <!-- Security Info -->
                    <div class="space-y-3">
                        <div class="flex items-start gap-3 text-sm text-gray-600 dark:text-gray-400">
                            <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            <span>{{ t('auth.no_password_needed') }}</span>
                        </div>
                        <div class="flex items-start gap-3 text-sm text-gray-600 dark:text-gray-400">
                            <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <span>{{ t('auth.telegram_encrypted') }}</span>
                        </div>
                        <div class="flex items-start gap-3 text-sm text-gray-600 dark:text-gray-400">
                            <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            <span>{{ t('auth.instant_access') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
