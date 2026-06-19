<?php

namespace App\Services;

use PhpOffice\PhpWord\IOFactory;
use App\Models\Prompt;
use App\Models\PromptVariable;

class PromptService
{
    /**
     * Extraire le texte brut d'un fichier Word
     */
    public function extractTextFromWord(string $filePath): string
    {
        $phpWord = IOFactory::load($filePath);
        $text = '';

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if (method_exists($element, 'getElements')) {
                    foreach ($element->getElements() as $subElement) {
                        if (method_exists($subElement, 'getText')) {
                            $text .= $subElement->getText() . ' ';
                        }
                    }
                }
                $text .= "\n";
            }
        }

        return trim($text);
    }

    /**
     * Extraire les variables {{ variable }} du contenu
     */
    public function extractVariables(string $content): array
    {
        preg_match_all('/\{\{(.*?)\}\}/', $content, $matches);

        $variables = array_map(function ($var) {
            // Supprimer espaces normaux + insécables + tabulations
            $var = preg_replace('/[\s\x{00A0}\x{200B}]+/u', ' ', $var);
            return trim($var);
        }, $matches[1] ?? []);

        return array_values(array_unique(array_filter($variables)));
    }

    /**
     * Sauvegarder le prompt et ses variables en DB
     */
    public function savePrompt(array $data, string $filePath = null): Prompt
    {
        $prompt = Prompt::create([
            'category_id'   => $data['category_id'] ?? null,
            'title'         => $data['title'],
            'content'       => $data['content'],
            'original_file' => $filePath,
            'source'        => $filePath ? 'upload' : 'manual',
        ]);

        // Extraire et sauvegarder les variables
        $variables = $this->extractVariables($data['content']);

    
        foreach ($variables as $variable) {
            PromptVariable::create([
                'prompt_id'     => $prompt->id,
                'name'          => $variable,  // garde le nom original avec espaces
                'label'         => $variable,  // même chose pour le label
                'default_value' => null,
            ]);
        }

        return $prompt;
    }

    /**
     * Remplacer les variables dans le contenu
     */
    public function replaceVariables(string $content, array $values): string
    {
        foreach ($values as $key => $value) {
            $cleanKey = preg_replace('/[\s\x{00A0}\x{200B}]+/u', ' ', trim($key));
            
            // Regex qui accepte n'importe quel type d'espace entre {{ et }}
            $content = preg_replace(
                '/\{\{[\s\x{00A0}]*' . preg_quote($cleanKey, '/') . '[\s\x{00A0}]*\}\}/u',
                $value,
                $content
            );
        }
        return $content;
    }

    /**
 * Générer un fichier Word avec le contenu final
 */
public function generateWordFile(string $content, string $title): string
{
    $phpWord = new \PhpOffice\PhpWord\PhpWord();
    $section = $phpWord->addSection();

    // Style du titre
    $phpWord->addTitleStyle(1, [
        'bold'  => true,
        'size'  => 16,
        'color' => '000000',
    ]);

    // Ajouter le titre
    $section->addTitle($title, 1);
    $section->addTextBreak(1);

    // Ajouter le contenu ligne par ligne
    $lines = explode("\n", $content);
    foreach ($lines as $line) {
        if (trim($line) === '') {
            $section->addTextBreak(1);
        } else {
            $section->addText(htmlspecialchars($line), [
                'size' => 12,
                'name' => 'Arial',
            ]);
        }
    }

    // Sauvegarder le fichier
    $fileName  = 'executions/' . uniqid('prompt_') . '.docx';
    $fullPath  = storage_path('app/' . $fileName);

    // Créer le dossier si inexistant
    if (!file_exists(storage_path('app/executions'))) {
        mkdir(storage_path('app/executions'), 0755, true);
    }

    $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
    $writer->save($fullPath);

    return $fileName;
}
}