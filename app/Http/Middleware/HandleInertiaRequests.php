<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
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
            'locales' => fn () => [
                'en' => 'English',
                'ru' => 'Русский',
            ],
            'translations' => fn () => $this->getTranslations(),
        ];
    }

    /**
     * Get translations for current locale.
     */
    private function getTranslations(): array
    {
        $locale = App::getLocale();
        $path = lang_path("{$locale}/app.php");

        if (file_exists($path)) {
            return require $path;
        }

        return require lang_path('en/app.php');
    }
}
