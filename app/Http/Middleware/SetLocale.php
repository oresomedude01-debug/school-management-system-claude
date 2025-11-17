<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if locale is in session
        $locale = Session::get('locale', config('app.locale'));

        // Validate locale (only allow en and ar)
        if (!in_array($locale, ['en', 'ar'])) {
            $locale = 'en';
        }

        // Set the application locale
        App::setLocale($locale);

        // If user is authenticated, use their preferred locale
        if ($request->user()) {
            $userLocale = $request->user()->locale ?? 'en';
            if (in_array($userLocale, ['en', 'ar'])) {
                App::setLocale($userLocale);
            }
        }

        return $next($request);
    }
}
