<?php

namespace App\Http\Controllers;

use App\Models\TechnicalEvaluation;
use App\Models\TechnicalEvaluationResult;
use App\Models\TechnicalRequirement;
use App\Services\TechnicalEvaluation\TechnicalEvaluationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;

class TechnicalEvaluationController extends Controller
{
    public function __construct(
        private TechnicalEvaluationService $technicalEvaluationService
    ) {
    }

    /**
     * Lancer une nouvelle évaluation technique.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom_fournisseur' => 'required|string|max:255',
            'cahier_des_charges' => 'required|file|mimes:pdf|max:20480',
            'offre_fournisseur' => 'required|file|mimes:pdf|max:20480',
        ]);

        DB::beginTransaction();

        try {
            /*
             * 1. Enregistrer les deux PDF
             */
            $cahierPath = $request->file('cahier_des_charges')
                ->store('technical-evaluations/cahiers', 'local');

            $offrePath = $request->file('offre_fournisseur')
                ->store('technical-evaluations/offres', 'local');

            /*
             * 2. Extraire le texte des PDF
             */
            $cahierText = $this->extractPdfText(
                Storage::disk('local')->path($cahierPath)
            );

            $offreText = $this->extractPdfText(
                Storage::disk('local')->path($offrePath)
            );

            if (trim($cahierText) === '') {
                throw new \Exception(
                    'Impossible d\'extraire le texte du cahier des charges.'
                );
            }

            if (trim($offreText) === '') {
                throw new \Exception(
                    'Impossible d\'extraire le texte de l\'offre fournisseur.'
                );
            }

            /*
             * 3. Créer l'évaluation
             */
            $evaluation = TechnicalEvaluation::create([
                'nom_fournisseur' => $request->nom_fournisseur,
                'cahier_des_charges_path' => $cahierPath,
                'offre_fournisseur_path' => $offrePath,
                'note_finale' => 0,
                'note_maximale' => 0,
                'pourcentage' => 0,
                'statut' => 'EN COURS',
            ]);

            /*
             * 4. GEMINI #1
             * Extraction des exigences du cahier des charges
             */
            $requirementsResponse =
                $this->technicalEvaluationService
                    ->extractRequirements($cahierText);

            if (!$requirementsResponse['success']) {
                throw new \Exception(
                    $requirementsResponse['error']
                );
            }

            $requirements = $requirementsResponse['data'];

            /*
             * 5. Enregistrer les exigences en base
             */
            $requirementModels = [];

            foreach ($requirements as $requirement) {
                $requirementModels[] = TechnicalRequirement::create([
                    'technical_evaluation_id' => $evaluation->id,
                    'description' => $requirement['description'] ?? '',
                    'valeur_demandee' => $requirement['valeur_demandee'] ?? null,
                    'obligatoire' => $requirement['obligatoire'] ?? true,
                ]);
            }

            /*
             * 6. GEMINI #2
             * Extraction des caractéristiques de l'offre
             */
            $offerResponse =
                $this->technicalEvaluationService
                    ->extractOfferCharacteristics($offreText);

            if (!$offerResponse['success']) {
                throw new \Exception(
                    $offerResponse['error']
                );
            }

            $offerCharacteristics = $offerResponse['data'];

            /*
             * 7. GEMINI #3
             * Comparaison exigences / offre
             */
            $comparisonResponse =
                $this->technicalEvaluationService
                    ->compareRequirementsWithOffer(
                        $requirements,
                        $offerCharacteristics
                    );

            if (!$comparisonResponse['success']) {
                throw new \Exception(
                    $comparisonResponse['error']
                );
            }

            $comparisons = $comparisonResponse['data'];

            /*
             * 8. Enregistrer les résultats
             */
            $resultsForScore = [];

            foreach ($requirementModels as $index => $requirement) {

                $comparison = $comparisons[$index] ?? null;

                if (!$comparison) {
                    $conforme = false;
                    $valeurOfferte = null;
                    $justification = 'Aucun résultat retourné par Gemini.';
                } else {
                    $conforme = (bool) ($comparison['conforme'] ?? false);
                    $valeurOfferte = $comparison['valeur_offerte'] ?? null;
                    $justification = $comparison['justification'] ?? null;
                }

                TechnicalEvaluationResult::create([
                    'technical_requirement_id' => $requirement->id,
                    'valeur_offerte' => $valeurOfferte,
                    'conforme' => $conforme,
                    'points_obtenus' => $conforme ? 1 : 0,
                ]);

                $resultsForScore[] = [
                    'conforme' => $conforme,
                    'justification' => $justification,
                ];
            }

            /*
             * 9. Calcul de la note finale
             */
            $score =
                $this->technicalEvaluationService
                    ->calculateFinalScore($resultsForScore);

            /*
             * 10. Mise à jour de l'évaluation
             */
            $evaluation->update([
                'note_finale' => $score['note_finale'],
                'note_maximale' => $score['note_maximale'],
                'pourcentage' => $score['pourcentage'],
                'statut' => $score['statut'],
            ]);

            DB::commit();

            /*
             * 11. Retourner le résultat complet
             */
            $evaluation->load([
                'requirements.result'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Évaluation technique terminée avec succès.',
                'evaluation' => $evaluation,
            ], 201);

        } catch (\Throwable $e) {

            DB::rollBack();

            /*
             * Supprimer les fichiers uploadés si l'évaluation échoue.
             */
            if (isset($cahierPath)) {
                Storage::disk('local')->delete($cahierPath);
            }

            if (isset($offrePath)) {
                Storage::disk('local')->delete($offrePath);
            }

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'évaluation technique.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Afficher une évaluation existante.
     */
    public function show(TechnicalEvaluation $technicalEvaluation)
    {
        $technicalEvaluation->load([
            'requirements.result'
        ]);

        return response()->json([
            'success' => true,
            'evaluation' => $technicalEvaluation,
        ]);
    }

    /**
     * Liste des évaluations.
     */
    public function index()
    {
        $evaluations = TechnicalEvaluation::with([
            'requirements.result'
        ])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'evaluations' => $evaluations,
        ]);
    }

    /**
     * Extraction du texte d'un PDF.
     */
    private function extractPdfText(string $path): string
    {
        $parser = new Parser();

        $pdf = $parser->parseFile($path);

        return $pdf->getText();
    }
}