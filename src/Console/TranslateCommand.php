<?php

namespace SirCoolMind\AiTranslation\Console;

use Illuminate\Console\Command;
use SirCoolMind\AiTranslation\Services\AiTranslationService;

class TranslateCommand extends Command
{
    protected $signature = 'ai-translator-lang
        {key : The language key}
        {word? : The text to translate (defaults to key)}
        {--source= : Override default source language}';

    protected $description = 'Add translation word to lang JSON files using Gemini AI.';

    public function handle(AiTranslationService $service)
    {
        $key = $this->argument('key');
        $word = $this->argument('word');
        $source = $this->option('source');

        $this->info("Processing key: '{$key}'...");

        // Call the Service
        $report = $service->translateAndSave($key, $word, $source);

        $this->info("Word: '{$report['word']}' (Source: {$report['source']})");

        // Loop through results to print pretty output
        foreach ($report['details'] as $lang => $detail) {
            $status = $detail['status'];
            $provider = $detail['provider'];
            $text = $detail['text'];

            if ($status === 'SKIPPED') {
                $this->warn(" [SKIP] [$lang] Key exists.");
            } else {
                // Example: [Gemini] Auto [ms]: Selamat Datang
                $this->line(" [$provider] Auto [$lang]: $text");
                $this->info(" [OK] [$lang] Updated.");
            }
        }

        $this->info("Done.");
    }
}
