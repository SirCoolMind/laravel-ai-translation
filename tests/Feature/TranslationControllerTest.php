<?php

namespace SirCoolMind\AiTranslation\Tests\Feature;

use SirCoolMind\AiTranslation\Facades\AiTranslator;
use SirCoolMind\AiTranslation\Tests\TestCase;

class TranslationControllerTest extends TestCase
{
    public function test_index_page_loads()
    {
        // 1. Create a REAL dummy file
        file_put_contents(lang_path('en.json'), json_encode(['hello' => 'world']));

        // 2. Hit the CORRECT route (ai-translations)
        $response = $this->get('/ai-translations');

        // 3. Assert
        $response->assertStatus(200);
        $response->assertSee('AI Translation Manager');
        $response->assertSee('hello');
    }

    public function test_store_route_triggers_translation_service()
    {
        // Mock the Facade
        AiTranslator::shouldReceive('translateAndSave')
            ->once()
            ->with('new_key', 'New Word', 'en');

        // Hit the CORRECT route (ai-translations)
        $response = $this->post('/ai-translations', [
            'key' => 'new_key',
            'word' => 'New Word'
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }
}
