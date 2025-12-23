# Laravel AI Translation Tool

This package automates your Laravel localization process. It uses **Google Gemini AI** to automatically translate your language keys from English to other supported languages (e.g., Malay, Chinese) and saves them directly to your JSON language files (`lang/ms.json`, `lang/zh_CN.json`, etc.).

It comes with a **built-in UI**, a **Command Line tool**, and a **Facade** for programmatic usage.

## Installation

You can install the package via composer:

```bash
composer require sircoolmind/laravel-ai-translation

```

### 1. Publish Configuration

Publish the configuration file to setup your API keys and supported languages.

```bash
php artisan vendor:publish --tag="ai-translation-config"
```

### 2. Configure API Key

Open your `.env` file and add your Google Gemini API Key:

```env
GEMINI_API_KEY=your_api_key_here
```

You can also customize supported languages in `config/ai-translation.php`.

---

## Usage

### 1. Via Visual Interface (UI)

This package comes with a built-in dashboard to manage translations easily.

1. Open your browser and visit: `http://your-app.test/ai-translations`
2. Enter a **Key** (e.g., `welcome_message`) and the **Word** (e.g., "Welcome to our system").
3. Click **Auto Translate**.
4. The system will generate translations for all configured languages and save them to your JSON files.

### 2. Via Command Line (CLI)

Perfect for developers who want to add translations quickly without leaving the terminal.

**Syntax:**

```bash
php artisan ai-translator-lang {key} "{word}"
```

**Example:**
Translate "Dashboard" to all supported languages:

```bash
php artisan ai-translator-lang dashboard_title "Dashboard"
```

**Override Source Language:**
If your input word is in Malay:

```bash
php artisan ai-translator-lang dashboard_title "Papan Pemuka" --source=ms
```

### 3. Via Facade (Code)

You can use the `AiTranslator` facade inside your Controllers, Jobs, or Seeders to trigger translations programmatically.

```php
use SirCoolMind\AiTranslation\Facades\AiTranslator;

public function store(Request $request) 
{
    // ... create product logic ...

    // Automatically translate the success message
    AiTranslator::translateAndSave('product_created', 'Product created successfully');

    return back();
}

```

---

## Configuration (`config/ai-translation.php`)

```php
return [
    // Default source language for input
    'source_locale' => 'en',

    // Languages to translate into
    'supported_locales' => ['en', 'ms', 'zh'],

    // Mapping Laravel locales to Google Translate codes
    'google_map' => [
        'zh' => 'zh-CN',
        'ms' => 'ms',
    ],

    'api_key' => env('GEMINI_API_KEY'),
];
```

## Testing

To run the test suite included in this package:

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

* [Hafiz Ruslan](https://github.com/hafizunijaya)
* [Ahyew Unijaya](https://github.com/ahyewunijaya)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
