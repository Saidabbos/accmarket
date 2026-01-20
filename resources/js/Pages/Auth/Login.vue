<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Checkbox from '@/Components/Checkbox.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { useTranslations } from '@/composables/useTranslations';
import { onMounted, ref } from 'vue';

const { t } = useTranslations();

const props = defineProps({
    canResetPassword: Boolean,
    status: String,
    telegramBotUsername: String,
});

const page = usePage();
const telegramError = ref(page.props.errors?.telegram || null);

// Email/Password form
const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

// Telegram Login Widget callback
const onTelegramAuth = (user) => {
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
                        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                        </div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('auth.login') }}</h1>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ t('auth.login_subtitle') }}</p>
                    </div>

                    <!-- Status Message -->
                    <div v-if="status" class="mb-4 p-3 rounded-lg bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800">
                        <p class="text-sm font-medium text-green-600 dark:text-green-400">{{ status }}</p>
                    </div>

                    <!-- Error Message -->
                    <div v-if="telegramError || form.errors.email || form.errors.telegram" class="mb-6 p-4 rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-sm text-red-600 dark:text-red-400">
                                {{ telegramError || form.errors.email || form.errors.telegram }}
                            </p>
                        </div>
                    </div>

                    <!-- Social Login Buttons -->
                    <div v-if="telegramBotUsername" class="space-y-3 mb-6">
                        <!-- Telegram Widget Container -->
                        <div id="telegram-login-container" class="flex justify-center min-h-[40px]">
                            <!-- Telegram widget bu yerga dinamik yuklanadi -->
                        </div>
                    </div>

                    <!-- Divider -->
                    <div class="relative my-6">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-200 dark:border-gray-700"></div>
                        </div>
                        <div class="relative flex justify-center text-xs uppercase">
                            <span class="bg-white dark:bg-gray-800 px-3 text-gray-400">{{ t('auth.or_continue_with') }}</span>
                        </div>
                    </div>

                    <!-- Email/Password Form -->
                    <form @submit.prevent="submit" class="space-y-5">
                        <div>
                            <InputLabel for="email" :value="t('auth.email')" class="dark:text-gray-300" />
                            <TextInput
                                id="email"
                                type="email"
                                class="mt-1 block w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                v-model="form.email"
                                required
                                autofocus
                                autocomplete="username"
                                :placeholder="t('auth.email_placeholder')"
                            />
                        </div>

                        <div>
                            <InputLabel for="password" :value="t('auth.password')" class="dark:text-gray-300" />
                            <TextInput
                                id="password"
                                type="password"
                                class="mt-1 block w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                v-model="form.password"
                                required
                                autocomplete="current-password"
                                :placeholder="t('auth.password_placeholder')"
                            />
                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>

                        <div class="flex items-center justify-between">
                            <label class="flex items-center">
                                <Checkbox name="remember" v-model:checked="form.remember" />
                                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ t('auth.remember_me') }}</span>
                            </label>

                            <Link
                                v-if="canResetPassword"
                                :href="route('password.request')"
                                class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors"
                            >
                                {{ t('auth.forgot_password') }}
                            </Link>
                        </div>

                        <PrimaryButton
                            class="w-full justify-center py-3"
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ form.processing ? t('auth.logging_in') : t('auth.login') }}
                        </PrimaryButton>
                    </form>

                    <!-- Register Link -->
                    <div class="mt-6 text-center">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ t('auth.no_account') }}
                            <Link :href="route('register')" class="font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors">
                                {{ t('auth.register') }}
                            </Link>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
