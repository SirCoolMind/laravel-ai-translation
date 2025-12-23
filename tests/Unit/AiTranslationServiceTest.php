<?php

namespace SirCoolMind\AiTranslation\Tests\Unit;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use SirCoolMind\AiTranslation\Services\AiTranslationService;
use SirCoolMind\AiTranslation\Tests\TestCase;

class AiTranslationServiceTest extends TestCase
{
    public function test_it_translates_and_saves_keys()
    {
        // 1. Mock the API
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [['text' => 'Selamat Datang']]
                        ]
                    ]
                ]
            ], 200),
        ]);

        // 2. Mock File System
        File::shouldReceive('exists')->andReturn(false); // Pretend files don't exist
        File::shouldReceive('get')->andReturn('{}');     // Pretend files are empty

        // --- FIX STARTS HERE ---

        // A. Expect the "Initialization" calls (creating the empty {} files)
        // We expect this twice (once for 'en', once for 'ms')
        File::shouldReceive('put')
            ->withArgs(function ($path, $content) {
                return $content === '{}';
            })
            ->times(2);

        // B. Expect the "Saving" call for Source (English)
        File::shouldReceive('put')
            ->withArgs(function ($path, $content) {
                return str_contains($path, 'en.json') && str_contains($content, 'Welcome');
            })
            ->once();

        // C. Expect the "Saving" call for Target (Malay)
        File::shouldReceive('put')
            ->withArgs(function ($path, $content) {
                return str_contains($path, 'ms.json') && str_contains($content, 'Selamat Datang');
            })
            ->once();

        // --- FIX ENDS HERE ---

        // 3. Run the Service
        $service = new AiTranslationService();
        $report = $service->translateAndSave('welcome_msg', 'Welcome');

        // 4. Assertions
        $this->assertEquals('Welcome', $report['word']);
        $this->assertEquals('Selamat Datang', $report['details']['ms']['text']);
        $this->assertEquals('Gemini', $report['details']['ms']['provider']);
    }

    public function test_it_falls_back_to_google_if_gemini_fails()
    {
        // 1. Mock Gemini failing (500 error)
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response(null, 500),
            'translate.googleapis.com/*' => Http::response([
                [['Google Translated', 'Original', null, null, 1]]
            ], 200),
        ]);

        File::shouldReceive('exists')->andReturn(false);
        File::shouldReceive('get')->andReturn('{}');

        // --- ADD FIX HERE TOO ---
        // Expect initialization calls
        File::shouldReceive('put')
            ->withArgs(function ($path, $content) {
                return $content === '{}';
            })
            ->times(2);
        // ------------------------

        // Expect actual saves
        File::shouldReceive('put')
            ->withArgs(function ($path, $content) {
                return $content !== '{}'; // Any put that isn't initialization
            })
            ->times(2);

        // 2. Run Service
        $service = new AiTranslationService();
        $report = $service->translateAndSave('welcome_msg', 'Welcome');

        // 3. Assert it used Google
        $this->assertEquals('Google Translated', $report['details']['ms']['text']);
        $this->assertEquals('Google', $report['details']['ms']['provider']);
    }
}
