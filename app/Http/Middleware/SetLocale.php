<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    protected array $supportedLocales = ['en', 'ru'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->getLocale($request);

        App::setLocale($locale);
        Session::put('locale', $locale);

        return $next($request);
    }

    protected function getLocale(Request $request): string
    {
        // 1. Check URL parameter
        if ($request->has('lang') && in_array($request->get('lang'), $this->supportedLocales)) {
            return $request->get('lang');
        }

        // 2. Check session
        if (Session::has('locale') && in_array(Session::get('locale'), $this->supportedLocales)) {
            return Session::get('locale');
        }

        // 3. Check browser language
        $browserLocale = $request->getPreferredLanguage($this->supportedLocales);
        if ($browserLocale && in_array($browserLocale, $this->supportedLocales)) {
            return $browserLocale;
        }

        // 4. Default locale
        return config('app.locale', 'en');
    }
}
