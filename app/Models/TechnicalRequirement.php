<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TechnicalRequirement extends Model
{
    protected $fillable = [
        'technical_evaluation_id',
        'description',
        'valeur_demandee',
        'obligatoire',
    ];

    protected $casts = [
        'obligatoire' => 'boolean',
    ];

    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(TechnicalEvaluation::class, 'technical_evaluation_id');
    }

    public function result(): HasOne
    {
        return $this->hasOne(TechnicalEvaluationResult::class);
    }
}