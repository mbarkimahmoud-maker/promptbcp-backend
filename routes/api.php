<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PromptController;
use App\Http\Controllers\PromptExecutionController;
use App\Http\Controllers\CategoryController;  
use App\Http\Controllers\AIController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\PDFController;




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

Route::prefix('ai')->group(function () {
    Route::post('/executions/{execution}/gemini', [AIController::class, 'askGemini']);
    Route::post('/executions/{execution}/groq',   [AIController::class, 'askGroq']);
});

Route::post('/executions/{execution}/send-email', [EmailController::class, 'sendAIResponse']);
Route::post('/executions/{execution}/generate-pdf', [PDFController::class, 'generatePDF']);
