import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function useTranslations() {
    const page = usePage();

    const locale = computed(() => page.props.locale || 'en');
    const locales = computed(() => page.props.locales || {});
    const translations = computed(() => page.props.translations || {});

    /**
     * Get translation by key using dot notation
     * @param {string} key - Translation key (e.g., 'nav.categories')
     * @param {object} replacements - Optional replacements for placeholders
     * @returns {string}
     */
    const t = (key, replacements = {}) => {
        const keys = key.split('.');
        let value = translations.value;

        for (const k of keys) {
            if (value && typeof value === 'object' && k in value) {
                value = value[k];
            } else {
                return key; // Return key if translation not found
            }
        }

        if (typeof value !== 'string') {
            return key;
        }

        // Replace placeholders like :name with actual values
        let result = value;
        for (const [placeholder, replacement] of Object.entries(replacements)) {
            result = result.replace(new RegExp(`:${placeholder}`, 'g'), replacement);
        }

        return result;
    };

    /**
     * Check if current locale matches
     * @param {string} loc - Locale to check
     * @returns {boolean}
     */
    const isLocale = (loc) => locale.value === loc;

    return {
        locale,
        locales,
        translations,
        t,
        isLocale,
    };
}
