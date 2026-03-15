<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LanguageController extends Controller
{
    /**
     * Switch the application language
     *
     * @param Request $request
     * @param string $language
     * @return \Illuminate\Http\RedirectResponse
     */
    public function switch(Request $request, $language)
    {
        // Validate the language parameter
        $supportedLanguages = config('app.supported_locales', ['en', 'ar']);

        if (! in_array($language, $supportedLanguages, true)) {
            return redirect()->back()->with('error', __('admin.language.unsupported'));
        }

        // Store language in session
        session(['locale' => $language]);

        // Set application locale
        app()->setLocale($language);

        return redirect()->back()->with('info', __('admin.language.changed'));
    }
}

