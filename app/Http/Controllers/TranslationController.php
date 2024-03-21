<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stichoza\GoogleTranslate\GoogleTranslate;

class TranslationController extends Controller
{
    public function translate(Request $request)
    {
        $textToTranslate = $request->input('text');
        $targetLanguage = $request->input('target_language');

        $translator = new GoogleTranslate($targetLanguage);
        $translation = $translator->translate($textToTranslate);

        return response()->json(['translation' => $translation]);
    }
}
