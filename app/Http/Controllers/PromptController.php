<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PromptService;
use App\Models\Prompt;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;



class PromptController extends Controller
{
    protected PromptService $promptService;

    public function __construct(PromptService $promptService)
    {
        $this->promptService = $promptService;
    }

    /**
     * Upload un fichier Word
     */
        public function upload(Request $request)
    {
        $request->validate([
            'file'        => 'required|file|mimes:docx|max:10240',
            'title'       => 'required|string|max:191',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $file = $request->file('file');
        
        // Récupérer le chemin temporaire du fichier AVANT le store
        $tempPath = $file->getRealPath();
        
        // Extraire le texte depuis le fichier temporaire
        $content = $this->promptService->extractTextFromWord($tempPath);
        
        // Stocker le fichier après
        $filePath = $file->store('prompts', 'local');

        // Sauvegarder en DB
        $prompt = $this->promptService->savePrompt([
            'title'       => $request->title,
            'category_id' => $request->category_id,
            'content'     => $content,
        ], $filePath);

        return response()->json([
            'message' => 'Prompt uploadé avec succès',
            'prompt'  => $prompt->load('variables'),
        ], 201);
    }
    

    /**
     * Créer un prompt manuellement
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:191',
            'content'     => 'required|string',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $prompt = $this->promptService->savePrompt($request->only([
            'title', 'content', 'category_id'
        ]));

        return response()->json([
            'message' => 'Prompt créé avec succès',
            'prompt'  => $prompt->load('variables'),
        ], 201);
    }

    /**
     * Lister tous les prompts
     */
    public function index()
    {
        $prompts = Prompt::with(['category', 'variables'])->latest()->get();
        return response()->json($prompts);
    }

    /**
     * Afficher un prompt
     */
    public function show(Prompt $prompt)
    {
        return response()->json($prompt->load(['category', 'variables']));
    }
    /**
 * Modifier un prompt
 */
public function update(Request $request, Prompt $prompt)
{
    $request->validate([
        'title'       => 'sometimes|string|max:191',
        'content'     => 'sometimes|string',
        'category_id' => 'nullable|exists:categories,id',
    ]);

    $prompt->update($request->only(['title', 'content', 'category_id']));

    // Si le contenu a changé, re-extraire les variables
    if ($request->has('content')) {
        // Supprimer les anciennes variables
        $prompt->variables()->delete();

        // Extraire et sauvegarder les nouvelles
        $variables = $this->promptService->extractVariables($request->content);
        foreach ($variables as $variable) {
            \App\Models\PromptVariable::create([
                'prompt_id'     => $prompt->id,
                'name'          => $variable,
                'label'         => $variable,
                'default_value' => null,
            ]);
        }
    }

    return response()->json([
        'message' => 'Prompt modifié avec succès',
        'prompt'  => $prompt->load(['category', 'variables']),
    ]);
}

/**
 * Supprimer un prompt
 */
public function destroy(Prompt $prompt)
{
    // Supprimer le fichier Word original si existe
    if ($prompt->original_file && Storage::exists($prompt->original_file)) {
        Storage::delete($prompt->original_file);
    }

    $prompt->delete();

    return response()->json([
        'message' => 'Prompt supprimé avec succès',
    ]);
}

/**
 * Lister les prompts par catégorie
 */
public function byCategory(Category $category)
{
    $prompts = $category->prompts()->with('variables')->latest()->get();

    return response()->json([
        'category' => $category->name,
        'prompts'  => $prompts,
    ]);
}
}