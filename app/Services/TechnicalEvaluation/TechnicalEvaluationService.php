<?php

namespace App\Services\TechnicalEvaluation;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class TechnicalEvaluationService
{
    /**
     * Appel direct à Gemini.
     *
     * On ne modifie pas AIService.php.
     * Cette méthode est réservée à l'évaluation technique.
     */
    private function callGemini(string $prompt): array
{
    $apiKey = config('services.gemini.api_key');
    $apiUrl = config('services.gemini.api_url');

    if (!$apiKey || !$apiUrl) {
        return [
            'success' => false,
            'error' => 'Configuration Gemini absente.',
        ];
    }

    try {
        $response = Http::timeout(180)
            ->connectTimeout(10)
            ->post($apiUrl . '?key=' . $apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => $prompt
                            ]
                        ]
                    ]
                ],

                'generationConfig' => [
                    'temperature' => 0,
                    'maxOutputTokens' => 16384,
                    'responseMimeType' => 'application/json',
                ],
            ]);

        if ($response->failed()) {
            return [
                'success' => false,
                'error' => $response->json('error.message')
                    ?? 'Erreur inconnue de Gemini.',
            ];
        }

        $data = $response->json();

        $text = $data['candidates'][0]['content']['parts'][0]['text']
            ?? null;

        if (!$text) {
            return [
                'success' => false,
                'error' => 'Réponse vide de Gemini.',
            ];
        }

        return [
            'success' => true,
            'text' => $text,
        ];

    } catch (\Throwable $e) {
        return [
            'success' => false,
            'error' => $e->getMessage(),
        ];
    }
}
    /**
     * ============================================================
     * 1. EXTRACTION DES EXIGENCES DU CAHIER DES CHARGES
     * ============================================================
     */
    public function extractRequirements(string $text): array
    {
        $text = $this->cleanDocumentText($text);

        $prompt = <<<PROMPT
Tu es un système d'extraction de données pour l'analyse technique
d'un appel d'offres informatique.

Ta mission est d'extraire UNIQUEMENT les exigences techniques
EXPLICITEMENT présentes dans le cahier des charges.

Le texte fourni correspond à l'ensemble du cahier des charges.
Analyse l'ensemble du texte avant de répondre.

TEXTE DU CAHIER DES CHARGES :

------------------------------
{$text}
------------------------------

RÈGLES ABSOLUES :

1. Retourne UNIQUEMENT un tableau JSON valide.

2. Chaque objet doit contenir exactement :
- description
- valeur_demandee
- obligatoire

3. EXTRAIS UNIQUEMENT les exigences qui doivent réellement être
évaluées techniquement pour décider si le matériel proposé respecte
le cahier des charges.

4. Une exigence doit être une caractéristique technique ou une
condition technique directement vérifiable dans l'offre fournisseur.

5. PRIORITÉ aux éléments suivants :
- processeur
- nombre de cœurs
- fréquence
- mémoire RAM
- technologie RAM
- stockage
- capacité stockage
- écran
- résolution
- luminosité
- carte graphique
- VRAM
- connectivité
- ports
- réseau
- système d'exploitation
- sécurité
- autonomie
- dimensions
- poids
- accessoires techniques
- garantie technique lorsqu'elle constitue une exigence explicite

6. Ne crée PAS une exigence pour une simple information
administrative ou documentaire.

7. Ne transforme PAS automatiquement chaque document demandé
en une exigence de scoring.

Par exemple, ne crée pas automatiquement une exigence pour :
- "fournir une fiche technique"
- "fournir une documentation constructeur"
- "fournir des références commerciales"
- "fournir des justificatifs"
- "fournir des certificats"

SAUF si le contenu demandé correspond lui-même à une
caractéristique technique précise qui doit être vérifiée.

8. Ne crée PAS plusieurs exigences qui expriment exactement
la même caractéristique.

Exemple :

"32 Go RAM"
et
"RAM DDR5"
peuvent être deux exigences distinctes uniquement si le cahier
des charges impose réellement séparément la capacité et la technologie.

9. Ne jamais inventer une valeur.

10. Ne jamais déduire une valeur qui n'est pas écrite explicitement.

11. Conserve exactement les nombres, unités et quantités présents
dans le document.

12. Une quantité est une information importante lorsqu'elle concerne
directement le matériel ou les accessoires techniques.

13. Ignore :
- titres seuls
- numéros de pages
- numéros de sections
- références administratives
- références de documents
- objets de marchés
- informations purement administratives
- informations commerciales non techniques

14. Si une ligne contient seulement un titre comme :
"Processeur"
"RAM"
"Stockage"
"Écran"

et qu'aucune valeur technique n'est indiquée,
ne crée pas d'exigence.

15. Une exigence doit contenir une information suffisamment précise
pour pouvoir être comparée avec une offre fournisseur.

16. Pour "obligatoire" :
- true si l'exigence est obligatoire, minimale, requise,
  exigée ou impérative.
- false seulement si le caractère non obligatoire est explicitement indiqué.
- En cas de doute, utiliser true.

17. Ne pas regrouper plusieurs caractéristiques indépendantes
lorsqu'elles peuvent être comparées séparément.

18. Si aucune exigence technique complète et vérifiable n'est trouvée,
retourne [].

19. Ne jamais utiliser :
- "..."
- "[...]"
- "inconnu"
- "non précisé"
- une valeur inventée

20. Ne réponds avec aucune explication.

FORMAT ATTENDU :

[
    {
        "description": "Mémoire vive",
        "valeur_demandee": "32 GB DDR5 4800 MHz minimum",
        "obligatoire": true
    }
]
PROMPT;

        $response = $this->callGemini($prompt);

        if (!$response['success']) {
            return $response;
        }

        try {
            $requirements = $this->decodeJsonResponse(
                $response['text']
            );
        } catch (RuntimeException $e) {
            return [
                'success' => false,
                'error' => 'Gemini a retourné un JSON invalide pour les exigences techniques.',
                'raw' => $response['text'],
            ];
        }

        $cleanRequirements = [];

        foreach ($requirements as $requirement) {

            if (
                !isset($requirement['description']) ||
                !isset($requirement['valeur_demandee'])
            ) {
                continue;
            }

            $description = trim(
                (string) $requirement['description']
            );

            $value = trim(
                (string) $requirement['valeur_demandee']
            );

            if ($description === '' || $value === '') {
                continue;
            }

            /*
             * Ignore les valeurs qui sont uniquement
             * des numéros de sections.
             *
             * Exemple :
             * 1.2.3
             * 3.4
             */
            if (preg_match('/^\d+(?:\.\d+)+$/', $value)) {
                continue;
            }

            $cleanRequirements[] = [
                'description' => $description,
                'valeur_demandee' => $value,
                'obligatoire' => (bool) (
                    $requirement['obligatoire'] ?? true
                ),
            ];
        }

        $cleanRequirements = $this->removeDuplicateRequirements(
            $cleanRequirements
        );

        return [
            'success' => true,
            'data' => $cleanRequirements,
        ];
    }

    /**
     * ============================================================
     * 2. EXTRACTION DES CARACTÉRISTIQUES DE L'OFFRE
     * ============================================================
     */
    public function extractOfferCharacteristics(string $text): array
    {
        $text = $this->cleanDocumentText($text);

        $prompt = <<<PROMPT
Tu es un système d'extraction de données techniques provenant
d'une offre fournisseur informatique.

Ta mission est d'extraire uniquement les caractéristiques techniques
EXPLICITEMENT présentes dans l'offre.

Le texte fourni correspond à l'ensemble de l'offre fournisseur.
Analyse l'ensemble du texte avant de répondre.

TEXTE DE L'OFFRE :

------------------
{$text}
------------------

RÈGLES :

1. Retourne uniquement un tableau JSON valide.

2. Chaque élément doit contenir exactement :
- description
- valeur_offerte

3. Ne jamais inventer une information.

4. Conserve les nombres, quantités et unités exactement comme
elles apparaissent dans l'offre.

5. Exemple :

"25 ordinateurs Dell Precision 5680"

peut donner :

{
    "description": "Nombre de postes",
    "valeur_offerte": "25"
}

6. Exemple :

"RTX A1000 6GB"

doit conserver :

"6GB"

7. Les numéros de pages et de sections ne sont pas des caractéristiques.

8. Ignore les informations purement administratives, juridiques
ou commerciales.

9. Si aucune caractéristique technique vérifiable n'est présente,
retourne [].

10. Pas d'explication.

11. Pas de Markdown.

12. Pas de "..." ou "[...]".

FORMAT :

[
    {
        "description": "Mémoire vive",
        "valeur_offerte": "32 GB DDR5 4800 MHz"
    }
]
PROMPT;

        $response = $this->callGemini($prompt);

        if (!$response['success']) {
            return $response;
        }

        try {
            $offers = $this->decodeJsonResponse(
                $response['text']
            );
        } catch (RuntimeException $e) {
            return [
                'success' => false,
                'error' => 'Gemini a retourné un JSON invalide pour les caractéristiques de l\'offre fournisseur.',
                'raw' => $response['text'],
            ];
        }

        $cleanOffers = [];

        foreach ($offers as $offer) {

            if (
                !isset($offer['description']) ||
                !isset($offer['valeur_offerte'])
            ) {
                continue;
            }

            $description = trim(
                (string) $offer['description']
            );

            $value = trim(
                (string) $offer['valeur_offerte']
            );

            if ($description === '' || $value === '') {
                continue;
            }

            $cleanOffers[] = [
                'description' => $description,
                'valeur_offerte' => $value,
            ];
        }

        $cleanOffers = $this->removeDuplicateOffers(
            $cleanOffers
        );

        return [
            'success' => true,
            'data' => $cleanOffers,
        ];
    }

    /**
     * ============================================================
     * 3. COMPARAISON EXIGENCES / OFFRE
     * ============================================================
     */
    public function compareRequirementsWithOffer(
        array $requirements,
        array $offerCharacteristics
    ): array {
        /*
         * Ajouter un index à chaque caractéristique de l'offre.
         *
         * Cela permet à Gemini de dire :
         *
         * requirement_index = 10
         * offer_index = 5
         *
         * plutôt que de répéter toute la caractéristique.
         */
        $offerIndexed = [];

        foreach (
            array_values($offerCharacteristics)
            as $index => $offer
        ) {
            $offerIndexed[] = [
                'offer_index' => $index,
                'description' => $offer['description'] ?? '',
                'valeur_offerte' => $offer['valeur_offerte'] ?? '',
            ];
        }

        /*
         * Ajouter un index aux exigences.
         */
        $requirementsIndexed = [];

        foreach (
            array_values($requirements)
            as $index => $requirement
        ) {
            $requirementsIndexed[] = [
                'requirement_index' => $index,
                'description' => $requirement['description'] ?? '',
                'valeur_demandee' => $requirement['valeur_demandee'] ?? '',
                'obligatoire' => (bool) (
                    $requirement['obligatoire'] ?? true
                ),
            ];
        }

        $requirementsJson = $this->compactJson(
            $requirementsIndexed
        );

        $offerJson = $this->compactJson(
            $offerIndexed
        );

        $prompt = <<<PROMPT
Tu es un expert en évaluation technique des offres fournisseurs IT.

Tu dois comparer CHAQUE exigence du cahier des charges avec
les caractéristiques techniques proposées dans l'offre fournisseur.

IMPORTANT :

- Il y a exactement {$this->countRequirements($requirementsIndexed)} exigences.
- Tu dois retourner UNE ligne pour CHAQUE exigence.
- Ne supprime aucune exigence.
- Ne crée aucune exigence supplémentaire.
- Utilise exactement le "requirement_index" fourni.
- Cherche dans l'offre la caractéristique qui correspond le mieux
  à chaque exigence.
- Retourne "offer_index" correspondant à la caractéristique utilisée.
- Si aucune caractéristique correspondante n'existe :
  "offer_index": null
- "conforme": true uniquement si l'offre respecte réellement
  l'exigence.
- "conforme": false si l'information est absente, insuffisante
  ou si la valeur ne respecte pas l'exigence.
- Ne fais aucune supposition.
- Une information absente doit être considérée comme non conforme.
- Compare les nombres, quantités, unités et caractéristiques
  lorsqu'ils sont explicitement disponibles.
- Ne transforme jamais une information absente en information conforme.

FORMAT OBLIGATOIRE :

[
    {
        "requirement_index": 0,
        "offer_index": 5,
        "conforme": true
    },
    {
        "requirement_index": 1,
        "offer_index": null,
        "conforme": false
    }
]

NE RETOURNE RIEN D'AUTRE QUE LE JSON.

EXIGENCES DU CAHIER DES CHARGES :

{$requirementsJson}

OFFRE DU FOURNISSEUR :

{$offerJson}
PROMPT;

        $response = $this->callGemini($prompt);

        if (!$response['success']) {
            return $response;
        }

        try {
            $comparisons = $this->decodeJsonResponse(
                $response['text']
            );
        } catch (RuntimeException $e) {
            return [
                'success' => false,
                'error' => 'Gemini a retourné un JSON invalide pour la comparaison.',
                'raw' => $response['text'],
            ];
        }

        /*
         * Indexer les comparaisons par requirement_index.
         */
        $comparisonByRequirement = [];

        foreach ($comparisons as $comparison) {

            if (
                !isset($comparison['requirement_index'])
            ) {
                continue;
            }

            $index = (int) $comparison['requirement_index'];

            $comparisonByRequirement[$index] = $comparison;
        }

        /*
         * Construire systématiquement un résultat
         * pour CHAQUE exigence.
         */
        $finalResults = [];

        foreach ($requirementsIndexed as $requirement) {

            $requirementIndex =
                $requirement['requirement_index'];

            $comparison =
                $comparisonByRequirement[$requirementIndex]
                ?? null;

            $offerIndex = null;
            $conforme = false;
            $valeurOfferte = null;

            if ($comparison) {

                $offerIndex =
                    $comparison['offer_index'] ?? null;

                $conforme = (bool) ($comparison['conforme'] ?? false);

                if ($offerIndex === null) {
                    $conforme = false;
                }

                if (
                    $offerIndex !== null &&
                    isset($offerIndexed[$offerIndex])
                ) {
                    $valeurOfferte =
                        $offerIndexed[$offerIndex]['valeur_offerte']
                        ?? null;
                }

                if ($valeurOfferte === null || trim((string) $valeurOfferte) === '') {
                    $conforme = false;
                }
            }

            $finalResults[] = [
                'requirement_index' => $requirementIndex,
                'valeur_offerte' => $valeurOfferte,
                'conforme' => $conforme,
            ];
        }

        return [
            'success' => true,
            'data' => $finalResults,
        ];
    }

    /**
     * ============================================================
     * 4. CALCUL DE LA NOTE
     * ============================================================
     */
    public function calculateFinalScore(array $results): array
    {
        $noteMaximale = count($results);

        $noteFinale = 0;

        foreach ($results as $result) {
            if (!empty($result['conforme'])) {
                $noteFinale++;
            }
        }

        $pourcentage = $noteMaximale > 0
            ? ($noteFinale / $noteMaximale) * 100
            : 0;

        $statut = $pourcentage >= 70
            ? 'CONFORME'
            : 'NON CONFORME';

        return [
            'note_finale' => $noteFinale,
            'note_maximale' => $noteMaximale,
            'pourcentage' => round($pourcentage, 2),
            'statut' => $statut,
        ];
    }

    /**
     * ============================================================
     * UTILITAIRES
     * ============================================================
     */

    /**
     * Nettoyer le texte extrait du PDF.
     *
     * Cette partie vient de la logique qui fonctionnait
     * précédemment.
     */
    private function cleanDocumentText(string $text): string
    {
        $text = str_replace(
            ["\r\n", "\r"],
            "\n",
            $text
        );

        $text = preg_replace(
            '/[ \t]+/',
            ' ',
            $text
        );

        $text = preg_replace(
            "/\n{3,}/",
            "\n\n",
            $text
        );

        return trim($text);
    }

    /**
     * Décoder proprement la réponse JSON de Gemini.
     */
    private function decodeJsonResponse(string $text): array
    {
        $text = trim($text);

        /*
         * Supprimer les éventuels blocs Markdown.
         */
        $text = preg_replace(
            '/^```(?:json)?\s*/i',
            '',
            $text
        );

        $text = preg_replace(
            '/\s*```$/',
            '',
            $text
        );

        $text = trim($text);

        /*
         * Récupérer uniquement la partie [ ... ].
         */
        $first = strpos($text, '[');
        $last = strrpos($text, ']');

        if (
            $first !== false &&
            $last !== false &&
            $last > $first
        ) {
            $text = substr(
                $text,
                $first,
                $last - $first + 1
            );
        }

        try {

            $data = json_decode(
                $text,
                true,
                512,
                JSON_THROW_ON_ERROR
            );

            if (!is_array($data)) {
                throw new RuntimeException(
                    'Le JSON retourné n’est pas un tableau.'
                );
            }

            return $data;

        } catch (\Throwable $e) {

            throw new RuntimeException(
                'JSON Gemini invalide : ' . $e->getMessage()
            );
        }
    }

    private function removeDuplicateRequirements(
        array $requirements
    ): array {
        $unique = [];

        foreach ($requirements as $requirement) {

            $key = mb_strtolower(
                trim($requirement['description'])
                . '|'
                . trim($requirement['valeur_demandee'])
            );

            $unique[$key] ??= $requirement;
        }

        return array_values($unique);
    }

    private function removeDuplicateOffers(
        array $offers
    ): array {
        $unique = [];

        foreach ($offers as $offer) {

            $key = mb_strtolower(
                trim($offer['description'])
                . '|'
                . trim($offer['valeur_offerte'])
            );

            $unique[$key] ??= $offer;
        }

        return array_values($unique);
    }

    private function compactJson(array $data): string
    {
        return json_encode(
            $data,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        );
    }

    private function countRequirements(
        array $requirements
    ): int {
        return count($requirements);
    }
}