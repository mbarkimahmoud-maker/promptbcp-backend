<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PromptService;
use App\Models\Prompt;
use App\Models\PromptExecution;

class PromptExecutionController extends Controller
{
    protected PromptService $promptService;

    public function __construct(PromptService $promptService)
    {
        $this->promptService = $promptService;
    }

    /**
     * Récupérer le formulaire dynamique d'un prompt
     * (retourne les variables à remplir)
     */
    public function getForm(Prompt $prompt)
    {
        return response()->json([
            'prompt'    => [
                'id'    => $prompt->id,
                'title' => $prompt->title,
            ],
            'variables' => $prompt->variables,
        ]);
    }

    /**
     * Soumettre le formulaire et générer le prompt final
     */
    public function execute(Request $request, Prompt $prompt)
    {
        $request->validate([
            'variables' => 'required|array',
        ]);

        // Remplacer les variables dans le contenu
        $finalContent = $this->promptService->replaceVariables(
            $prompt->content,
            $request->variables
        );

        // Générer le fichier Word
        $generatedFile = $this->promptService->generateWordFile($finalContent, $prompt->title);

        // Sauvegarder l'exécution
        $execution = PromptExecution::create([
            'prompt_id'        => $prompt->id,
            'variables_values' => $request->variables,
            'final_content'    => $finalContent,
            'generated_file'   => $generatedFile,
            'status'           => 'draft',
        ]);

        return response()->json([
            'message'        => 'Prompt généré avec succès',
            'execution'      => $execution,
            'final_content'  => $finalContent,
            'download_url'   => url('api/executions/' . $execution->id . '/download'),
        ], 201);
    }

    /**
     * Télécharger le fichier Word généré
     */
    public function download(PromptExecution $execution)
    {
        $filePath = storage_path('app/' . $execution->generated_file);

        if (!file_exists($filePath)) {
            return response()->json(['message' => 'Fichier introuvable'], 404);
        }

        return response()->download($filePath, $execution->prompt->title . '.docx');
    }

    /**
     * Historique des exécutions d'un prompt
     */
    public function history(Prompt $prompt)
    {
        $executions = $prompt->executions()->latest()->get();
        return response()->json($executions);
    }
    public function show(PromptExecution $execution)
{
    return response()->json($execution->load('prompt'));
}
}