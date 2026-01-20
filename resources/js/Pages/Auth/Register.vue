<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useTranslations } from '@/composables/useTranslations';

const { t } = useTranslations();

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head :title="t('auth.register')" />

    <AppLayout>
        <div class="min-h-[70vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
            <div class="w-full max-w-md">
                <!-- Card -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 p-8">
                    <!-- Header -->
                    <div class="text-center mb-8">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                        </div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('auth.register') }}</h1>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ t('auth.register_subtitle') }}</p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-5">
                        <div>
                            <InputLabel for="name" :value="t('auth.name')" class="dark:text-gray-300" />
                            <TextInput
                                id="name"
                                type="text"
                                class="mt-1 block w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                v-model="form.name"
                                required
                                autofocus
                                autocomplete="name"
                                :placeholder="t('auth.name_placeholder')"
                            />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <div>
                            <InputLabel for="email" :value="t('auth.email')" class="dark:text-gray-300" />
                            <TextInput
                                id="email"
                                type="email"
                                class="mt-1 block w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                v-model="form.email"
                                required
                                autocomplete="username"
                                :placeholder="t('auth.email_placeholder')"
                            />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>

                        <div>
                            <InputLabel for="password" :value="t('auth.password')" class="dark:text-gray-300" />
                            <TextInput
                                id="password"
                                type="password"
                                class="mt-1 block w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                v-model="form.password"
                                required
                                autocomplete="new-password"
                                :placeholder="t('auth.password_placeholder')"
                            />
                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>

                        <div>
                            <InputLabel for="password_confirmation" :value="t('auth.confirm_password')" class="dark:text-gray-300" />
                            <TextInput
                                id="password_confirmation"
                                type="password"
                                class="mt-1 block w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                v-model="form.password_confirmation"
                                required
                                autocomplete="new-password"
                                :placeholder="t('auth.confirm_password_placeholder')"
                            />
                            <InputError class="mt-2" :message="form.errors.password_confirmation" />
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
                            {{ form.processing ? t('auth.registering') : t('auth.register') }}
                        </PrimaryButton>
                    </form>

                    <!-- Login Link -->
                    <div class="mt-6 text-center">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ t('auth.have_account') }}
                            <Link :href="route('login')" class="font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors">
                                {{ t('auth.login') }}
                            </Link>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
