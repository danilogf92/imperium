<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetUserLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $preference = $request->user()
            ?->preferences()
            ->where('key', 'locale')
            ->first()?->value;

        $locale = is_array($preference)
            ? ($preference['locale'] ?? null)
            : $preference;

        $supportedLocales = array_keys(config('locales.supported', []));

        if (! in_array($locale, $supportedLocales, true)) {
            $locale = config('locales.default', config('app.locale', 'en'));
        }

        App::setLocale($locale);

        return $next($request);
    }
}
