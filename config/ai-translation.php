<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Source Locale
    |--------------------------------------------------------------------------
    | The default language code that your input words are in.
    |
    */
    'source_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Supported Locales
    |--------------------------------------------------------------------------
    | List of all languages you want to translate into.
    |
    */
    'supported_locales' => ['en', 'ms', 'zh'],

    /*
    |--------------------------------------------------------------------------
    | Google Translate Code Mapping
    |--------------------------------------------------------------------------
    | Sometimes your Laravel locale (e.g., 'zh') does not match Google's
    | locale code (e.g., 'zh-CN'). Map them here.
    |
    */
    'google_map' => [
        'zh' => 'zh-CN',
        'ms' => 'ms',
        // 'en' => 'en' is implied if not set
    ],

    /*
    |--------------------------------------------------------------------------
    | Gemini API Key
    |--------------------------------------------------------------------------
    */
    'gemini_api_key' => env('GEMINI_API_KEY'),
];
