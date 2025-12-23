<?php
namespace SirCoolMind\AiTranslation\Facades;

use Illuminate\Support\Facades\Facade;

class AiTranslator extends Facade
{
    protected static function getFacadeAccessor()
    {
        // This matches the class binding or class name
        return \SirCoolMind\AiTranslation\Services\AiTranslationService::class;
    }
}
