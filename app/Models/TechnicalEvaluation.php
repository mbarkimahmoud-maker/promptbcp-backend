<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TechnicalEvaluation extends Model
{
    protected $fillable = [
        'nom_fournisseur',
        'cahier_des_charges_path',
        'offre_fournisseur_path',
        'note_finale',
        'note_maximale',
        'pourcentage',
        'statut',
    ];

    protected $casts = [
        'note_finale' => 'decimal:2',
        'note_maximale' => 'decimal:2',
        'pourcentage' => 'decimal:2',
    ];

    public function requirements(): HasMany
    {
        return $this->hasMany(TechnicalRequirement::class);
    }
}