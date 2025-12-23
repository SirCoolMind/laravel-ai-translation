<?php

use Illuminate\Support\Facades\Route;
use SirCoolMind\AiTranslation\Http\Controllers\TranslationController;

Route::group(['middleware' => ['web'], 'prefix' => 'ai-translations'], function () {
    Route::get('/', [TranslationController::class, 'index'])->name('ai-translation.index');
    Route::post('/', [TranslationController::class, 'store'])->name('ai-translation.store');
    Route::delete('/', [TranslationController::class, 'destroy'])->name('ai-translation.destroy');
});
