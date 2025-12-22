<?php

namespace SirCoolMind\AiTranslation;

use Illuminate\Support\ServiceProvider;
use SirCoolMind\AiTranslation\Console\TranslateCommand;

class AiTranslationServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/ai-translation.php' => config_path('ai-translation.php'),
        ], 'config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                TranslateCommand::class,
            ]);
        }
    }

    public function register()
    {
        // Merge config so it works even if user doesn't publish it
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/ai-translation.php', 'ai-translation'
        );
    }
}
