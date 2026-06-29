<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AIService;
use App\Models\PromptExecution;

class AIController extends Controller
{
    protected AIService $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Envoyer le prompt final d'une exécution vers Gemini
     */
    public function askGemini(Request $request, PromptExecution $execution)
    {
        $result = $this->aiService->askGemini($execution->final_content);

        if (!$result['success']) {
            return response()->json([
                'message' => 'Erreur lors de l\'appel à Gemini',
                'error'   => $result['error'],
            ], 500);
        }

        return response()->json([
            'message'  => 'Réponse générée avec succès',
            'response' => $result['text'],
        ]);
    }
    /**
 * Envoyer le prompt final d'une exécution vers Groq
 */
public function askGroq(Request $request, PromptExecution $execution)
{
    $result = $this->aiService->askGroq($execution->final_content);

    if (!$result['success']) {
        return response()->json([
            'message' => 'Erreur lors de l\'appel à Groq',
            'error'   => $result['error'],
        ], 500);
    }

    return response()->json([
        'message'  => 'Réponse générée avec succès',
        'response' => $result['text'],
    ]);
}
}