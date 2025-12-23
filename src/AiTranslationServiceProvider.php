<?php

namespace SirCoolMind\AiTranslation;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use SirCoolMind\AiTranslation\Console\TranslateCommand;
use SirCoolMind\AiTranslation\Services\AiTranslationService;

class AiTranslationServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-ai-translation')
            ->hasConfigFile('ai-translation')
            ->hasCommands([TranslateCommand::class])
            ->hasViews('ai-translation')
            ->hasRoute('web');
    }

    public function packageRegistered()
    {
        // Optional: Bind it if you want a shorter alias,
        // but typically PackageServiceProvider handles simple bindings auto-magically.
        $this->app->singleton(AiTranslationService::class, function () {
            return new AiTranslationService();
        });
    }
}
