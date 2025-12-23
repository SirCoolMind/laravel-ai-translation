<?php

namespace SirCoolMind\AiTranslation\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use SirCoolMind\AiTranslation\AiTranslationServiceProvider;

class TestCase extends Orchestra
{
    protected $tempLangPath;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Define the absolute path
        $this->tempLangPath = __DIR__ . '/temp/lang';

        // 2. Register it with Laravel
        $this->app->useLangPath($this->tempLangPath);

        // 3. PHYSICALLY create the folder structure
        if (!is_dir($this->tempLangPath)) {
            mkdir($this->tempLangPath, 0777, true);
        }
    }

    protected function tearDown(): void
    {
        // 4. Clean up: Delete temp files and folders
        if (is_dir($this->tempLangPath)) {
            $files = glob($this->tempLangPath . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($this->tempLangPath);

            // Try to remove parent /temp if empty
            if (is_dir(dirname($this->tempLangPath))) {
                @rmdir(dirname($this->tempLangPath));
            }
        }

        parent::tearDown();
    }

    protected function getPackageProviders($app)
    {
        return [
            AiTranslationServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('ai-translation.source_locale', 'en');
        config()->set('ai-translation.supported_locales', ['en', 'ms']);
        config()->set('ai-translation.gemini_api_key', 'TEST_API_KEY');

        // Fix for View not finding the package views
        $app['config']->set('view.paths', [
            __DIR__.'/../resources/views',
            resource_path('views'),
        ]);
    }
}
