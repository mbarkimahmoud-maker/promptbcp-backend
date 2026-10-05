<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TechnicalEvaluationResult extends Model
{
    protected $fillable = [
        'technical_requirement_id',
        'valeur_offerte',
        'conforme',
        'points_obtenus',
    ];

    protected $casts = [
        'conforme' => 'boolean',
        'points_obtenus' => 'decimal:2',
    ];

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(TechnicalRequirement::class, 'technical_requirement_id');
    }
}