<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    private const SESSION_KEY = 'locale';

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $supported = config('app.supported_locales', ['en', 'ar']);
        $defaultLocale = config('app.locale', 'en');

        $sessionLocale = session(self::SESSION_KEY) ?? session('language');
        $queryLocale = $request->query('lang');

        $locale = $sessionLocale;

        if ($queryLocale && in_array($queryLocale, $supported, true)) {
            $locale = $queryLocale;
        }

        if (! in_array((string) $locale, $supported, true)) {
            $locale = $defaultLocale;
        }

        session([self::SESSION_KEY => $locale]);
        app()->setLocale($locale);

        return $next($request);
    }
}

