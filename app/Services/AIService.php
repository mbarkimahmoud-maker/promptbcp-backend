<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AIService
{
    /**
     * Envoyer un prompt à Gemini et récupérer la réponse
     */
    public function askGemini(string $prompt): array
{
    $apiKey = config('services.gemini.api_key');
    $apiUrl = config('services.gemini.api_url');

    $enhancedPrompt = $this->buildEnhancedPrompt($prompt);

    try {
        $response = Http::timeout(180)->connectTimeout(10)->post($apiUrl . '?key=' . $apiKey, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $enhancedPrompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'maxOutputTokens' => 8192,
            ],
        ]);

        if ($response->failed()) {
            return [
                'success' => false,
                'error'   => $response->json('error.message') ?? 'Erreur inconnue',
            ];
        }

        $data = $response->json();
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (!$text) {
            return [
                'success' => false,
                'error'   => 'Réponse vide de Gemini',
            ];
        }

        return [
            'success' => true,
            'text'    => $text,
        ];

    } catch (\Exception $e) {
        return [
            'success' => false,
            'error'   => $e->getMessage(),
        ];
    }
}
    /**
 * Envoyer un prompt à Groq (Llama) et récupérer la réponse
 */

public function askGroq(string $prompt): array
{
    $apiKey = config('services.groq.api_key');
    $apiUrl = config('services.groq.api_url');

    $enhancedPrompt = $this->buildEnhancedPrompt($prompt);

    try {
        $response = Http::timeout(180)->connectTimeout(10)->withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type'  => 'application/json',
        ])->post($apiUrl, [
            'model' => 'llama-3.3-70b-versatile',
            'messages' => [
                ['role' => 'user', 'content' => $enhancedPrompt]
            ],
            'max_tokens' => 8000,
        ]);

        if ($response->failed()) {
            return [
                'success' => false,
                'error'   => $response->json('error.message') ?? 'Erreur inconnue',
            ];
        }

        $data = $response->json();
        $text = $data['choices'][0]['message']['content'] ?? null;

        if (!$text) {
            return [
                'success' => false,
                'error'   => 'Réponse vide de Groq',
            ];
        }

        return [
            'success' => true,
            'text'    => $text,
        ];

    } catch (\Exception $e) {
        return [
            'success' => false,
            'error'   => $e->getMessage(),
        ];
    }
}
/**
 * Instruction renforcée commune à toutes les IA pour éviter les réponses tronquées
 */
private function buildEnhancedPrompt(string $prompt): string
{
    return $prompt . "\n\nIMPORTANT : Tu dois répondre de façon complète et exhaustive, sans aucune troncature. Ne JAMAIS utiliser '...', '[...]', ou des abréviations dans les tableaux ou les listes, même si la demande est longue ou complexe. Si une information exacte n'est pas disponible, fournis une estimation plausible et réaliste plutôt que d'omettre la donnée ou de la remplacer par des points de suspension. Génère INTÉGRALEMENT toutes les lignes, colonnes et éléments demandés, du début à la fin, sans raccourci ni résumé.";
}
}