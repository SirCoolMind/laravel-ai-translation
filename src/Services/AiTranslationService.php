<?php

namespace SirCoolMind\AiTranslation\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class AiTranslationService
{
    protected array $topKeys = [
        "The :attribute must contain at least one letter." => "The :attribute must contain at least one letter.",
    ];

    /**
     * Main entry point to translate and save a key.
     *
     * @return array Returns a report of actions taken for each language.
     */
    public function translateAndSave(string $key, ?string $word = null, ?string $sourceLang = null): array
    {
        $word = $word ?? $key;

        // 1. Config
        $defaultSource = config('ai-translation.source_locale', 'en');
        $targetLangs = config('ai-translation.supported_locales', ['en']);
        $sourceLang = $sourceLang ?: $defaultSource;

        $report = [
            'word' => $word,
            'source' => $sourceLang,
            'details' => []
        ];

        // 2. Prepare Translations
        $translations = [];
        $translations[$sourceLang] = $word;

        foreach ($targetLangs as $lang) {
            if ($lang === $sourceLang) continue;

            // Try Gemini
            $translated = $this->translateWithGemini($word, $lang, $sourceLang);
            $provider = 'Gemini';

            // Fallback to Google
            if (!$translated) {
                $translated = $this->translateWithGoogleFree($word, $lang, $sourceLang);
                $provider = 'Google';
            }

            $translations[$lang] = $translated;

            // Add to report
            $report['details'][$lang] = [
                'provider' => $provider,
                'text' => $translated
            ];
        }

        // 3. Update Files
        foreach ($translations as $lang => $value) {
            $status = $this->updateJsonFile($lang, $key, $value);

            // If we didn't translate it (it was the source), we just mark it as source
            if ($lang === $sourceLang) {
                 $report['details'][$lang] = ['status' => $status, 'provider' => 'Source', 'text' => $value];
            } else {
                 $report['details'][$lang]['status'] = $status;
            }
        }

        return $report;
    }

    protected function translateWithGemini($text, $targetLang, $sourceLang)
    {
        $apiKey = config('ai-translation.gemini_api_key');

        if (!$apiKey) return null;

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}";
        $prompt = "Translate '{$text}' from {$sourceLang} to {$targetLang}. Return ONLY the translated string.";

        try {
            $response = Http::post($url, [
                'contents' => [['parts' => [['text' => $prompt]]]]
            ]);

            return trim($response->json('candidates.0.content.parts.0.text'));
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function translateWithGoogleFree($text, $targetLang, $sourceLang)
    {
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

    protected function updateJsonFile($lang, $key, $value): string
    {
        $path = lang_path("$lang.json");

        if (!File::exists($path)) {
            File::put($path, '{}');
        }

        $json = json_decode(File::get($path), true) ?? [];

        if (isset($json[$key])) {
            return 'SKIPPED'; // Key already exists
        }

        $json[$key] = $value;

        // Sorting Logic
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

        return 'UPDATED';
    }
}
