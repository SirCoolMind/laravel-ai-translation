<?php

namespace SirCoolMind\AiTranslation\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;
use SirCoolMind\AiTranslation\Facades\AiTranslator;

class TranslationController extends Controller
{
    public function index()
    {
        $langs = config('ai-translation.supported_locales', ['en']);
        $source = config('ai-translation.source_locale', 'en');

        // 1. Load all JSON files
        $data = [];
        foreach ($langs as $lang) {
            $path = lang_path("$lang.json");
            if (File::exists($path)) {
                $data[$lang] = json_decode(File::get($path), true);
            } else {
                $data[$lang] = [];
            }
        }

        // 2. Pivot data to be Key-centric
        // Structure: ['key_name' => ['en' => 'Val', 'ms' => 'Val']]
        $keys = array_keys($data[$source] ?? []); // Use source as master list

        // Merge keys from other langs in case source is missing some
        foreach($data as $langData) {
            $keys = array_merge($keys, array_keys($langData));
        }
        $keys = array_unique($keys);
        sort($keys);

        $translations = [];
        foreach ($keys as $key) {
            foreach ($langs as $lang) {
                $translations[$key][$lang] = $data[$lang][$key] ?? '';
            }
        }

        return view('ai-translation::index', compact('translations', 'langs', 'source'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
            'word' => 'nullable|string',
        ]);

        // Use your Facade/Service logic
        AiTranslator::translateAndSave(
            $request->key,
            $request->word,
            config('ai-translation.source_locale')
        );

        return back()->with('success', 'Translation added and auto-translated!');
    }

    public function destroy(Request $request)
    {
        $key = $request->input('key');
        $langs = config('ai-translation.supported_locales', ['en']);

        foreach ($langs as $lang) {
            $path = lang_path("$lang.json");
            if (File::exists($path)) {
                $json = json_decode(File::get($path), true);
                if (isset($json[$key])) {
                    unset($json[$key]);
                    File::put($path, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                }
            }
        }

        return back()->with('success', "Key '{$key}' deleted from all files.");
    }
}
