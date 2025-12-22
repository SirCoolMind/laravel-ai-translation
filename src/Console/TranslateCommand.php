<?php

namespace SirCoolMind\AiTranslation\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class TranslateCommand extends Command
{
    protected $signature = 'tw
        {key : The language key}
        {word? : The text to translate (defaults to key)}
        {--source= : Override default source language}';

    protected $description = 'Add translation word to lang JSON files using Gemini AI.';

    // Top keys to keep organized (You can also move this to config if you want strictly no hardcoding)
    protected array $topKeys = [
        "The :attribute must contain at least one letter." => "The :attribute must contain at least one letter.",
    ];

    public function handle()
    {
        $key = $this->argument('key');
        $word = $this->argument('word') ?? $key;

        // 1. Get Config
        $defaultSource = config('ai-translation.source_locale', 'en');
        $targetLangs = config('ai-translation.supported_locales', ['en']);

        // Use option if provided, otherwise use config default
        $sourceLang = $this->option('source') ?: $defaultSource;

        $this->info("Processing: '{$word}' (Source: {$sourceLang})");

        // 2. Prepare Translations
        $translations = [];
        $translations[$sourceLang] = $word;

        foreach ($targetLangs as $lang) {
            if ($lang === $sourceLang) continue;

            // AI Translate
            $translated = $this->translateWithGemini($word, $lang, $sourceLang);

            if (!$translated) {
                $translated = $this->translateWithGoogleFree($word, $lang, $sourceLang);
                $this->line(" [Google] Auto [$lang]: $translated");
            } else {
                $this->line(" [Gemini] Auto [$lang]: $translated");
            }

            $translations[$lang] = $translated;
        }

        // 3. Update Files
        foreach ($translations as $lang => $value) {
            $this->updateJsonFile($lang, $key, $value);
        }

        $this->info("Done.");
    }

    protected function translateWithGemini($text, $targetLang, $sourceLang)
    {
        $apiKey = config('ai-translation.gemini_api_key');

        if (!$apiKey) {
            $this->info("GEMINI_API_KEY is missing. Change into Google Free Translation");
            return null;
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}";

        $prompt = "Translate '{$text}' from {$sourceLang} to {$targetLang}. Return ONLY the translated string.";

        try {
            $response = Http::post($url, [
                'contents' => [['parts' => [['text' => $prompt]]]]
            ]);

            return trim($response->json('candidates.0.content.parts.0.text'));
        } catch (\Exception $e) {
            // Silently fail to fallback
            return null;
        }
    }

    protected function translateWithGoogleFree($text, $targetLang, $sourceLang)
    {
        // Get mapping from config
        $map = config('ai-translation.google_map', []);

        $tl = $map[$targetLang] ?? $targetLang;
        $sl = $map[$sourceLang] ?? $sourceLang;

        try {
            $response = Http::get('https://translate.googleapis.com/translate_a/single', [
                'client' => 'gtx',
                'sl' => $sl,
                'tl' => $tl,
                'dt' => 't',
                'q' => $text
            ]);

            return $response->json()[0][0][0] ?? $text;
        } catch (\Exception $e) {
            return $text;
        }
    }

    protected function updateJsonFile($lang, $key, $value)
    {
        $path = lang_path("$lang.json");

        if (!File::exists($path)) {
            File::put($path, '{}');
        }

        $json = json_decode(File::get($path), true) ?? [];

        if (isset($json[$key])) {
            $this->warn(" [SKIP] [$lang] Key exists.");
            return;
        }

        $json[$key] = $value;

        // Sort: Top keys first, then alphabetical
        $finalJson = [];

        foreach ($this->topKeys as $topKey => $topVal) {
            if (isset($json[$topKey])) {
                $finalJson[$topKey] = $json[$topKey];
                unset($json[$topKey]);
            }
        }

        ksort($json);
        $finalJson = array_merge($finalJson, $json);

        File::put($path, json_encode($finalJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $this->info(" [OK] [$lang] Updated.");
    }
}
