<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class LangController extends Controller
{
    public function switch(string $lang): RedirectResponse
    {
        session(['locale' => $lang]);
        app()->setLocale($lang);
        return back();
    }
}
