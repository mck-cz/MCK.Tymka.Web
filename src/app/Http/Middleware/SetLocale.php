<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);

        App::setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }

    /**
     * Resolve the locale for the current request.
     */
    protected function resolveLocale(Request $request): string
    {
        $supportedLocales = ['cs', 'en', 'sk'];

        // 1. Authenticated user preference
        if ($request->user() && !empty($request->user()->locale)) {
            $locale = $request->user()->locale;
            if (in_array($locale, $supportedLocales)) {
                return $locale;
            }
        }

        // 2. Session locale
        if (session()->has('locale')) {
            $locale = session('locale');
            if (in_array($locale, $supportedLocales)) {
                return $locale;
            }
        }

        // 3. Accept-Language header
        $preferredLanguage = $request->getPreferredLanguage($supportedLocales);
        if ($preferredLanguage) {
            return $preferredLanguage;
        }

        // 4. Fallback to config
        return config('app.locale', 'cs');
    }
}
