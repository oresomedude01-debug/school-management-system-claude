<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LocaleController extends Controller
{
    /**
     * Switch application locale.
     */
    public function switch(Request $request, $locale)
    {
        // Validate locale
        if (!in_array($locale, ['en', 'ar'])) {
            $locale = 'en';
        }

        // Store in session
        Session::put('locale', $locale);

        // If user is authenticated, update their preferred locale
        if ($request->user()) {
            $request->user()->update(['locale' => $locale]);
        }

        return redirect()->back();
    }
}
