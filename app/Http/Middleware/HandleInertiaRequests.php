<?php

namespace App\Http\Middleware;

use App\Models\Language;
use App\Models\Translation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? $request->user()->load('roles:id,name') : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'locale' => fn () => App::getLocale(),
            'locales' => fn () => $this->getActiveLocales(),
            'translations' => fn () => $this->getTranslations(),
        ];
    }

    /**
     * Get active locales from database or fallback to defaults.
     */
    private function getActiveLocales(): array
    {
        try {
            if (Schema::hasTable('languages')) {
                $languages = Language::getActive();
                if ($languages->count() > 0) {
                    return $languages->pluck('native_name', 'code')->toArray();
                }
            }
        } catch (\Exception $e) {
            // Fallback if database not available
        }

        return [
            'en' => 'English',
            'ru' => 'Русский',
        ];
    }

    /**
     * Get translations for current locale.
     */
    private function getTranslations(): array
    {
        $locale = App::getLocale();

        // Try to get translations from database first
        try {
            if (Schema::hasTable('translations')) {
                $dbTranslations = Translation::getForLocale($locale);
                if (!empty($dbTranslations)) {
                    return $dbTranslations;
                }
            }
        } catch (\Exception $e) {
            // Fallback if database not available
        }

        // Fallback to file-based translations
        $path = lang_path("{$locale}/app.php");

        if (file_exists($path)) {
            return require $path;
        }

        return require lang_path('en/app.php');
    }
}
