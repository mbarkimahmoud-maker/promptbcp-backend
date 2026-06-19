<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PromptController;
use App\Http\Controllers\PromptExecutionController;
use App\Http\Controllers\CategoryController;  // ← ajouter cette ligne

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('prompts')->group(function () {
    Route::get('/',                        [PromptController::class, 'index']);
    Route::post('/',                       [PromptController::class, 'store']);
    Route::post('/upload',                 [PromptController::class, 'upload']);
    Route::get('/{prompt}',                [PromptController::class, 'show']);
    Route::put('/{prompt}',                [PromptController::class, 'update']);
    Route::delete('/{prompt}',             [PromptController::class, 'destroy']);
    Route::get('/{prompt}/form',           [PromptExecutionController::class, 'getForm']);
    Route::post('/{prompt}/execute',       [PromptExecutionController::class, 'execute']);
    Route::get('/{prompt}/history',        [PromptExecutionController::class, 'history']);
});

Route::prefix('categories')->group(function () {
    Route::get('/',                        [CategoryController::class, 'index']);
    Route::post('/',                       [CategoryController::class, 'store']);
    Route::get('/{category}/prompts',      [PromptController::class, 'byCategory']);
    Route::delete('/{category}', [CategoryController::class, 'destroy']);
});

Route::prefix('executions')->group(function () {
    Route::get('/{execution}',          [PromptExecutionController::class, 'show']);
    Route::get('/{execution}/download', [PromptExecutionController::class, 'download']);
});