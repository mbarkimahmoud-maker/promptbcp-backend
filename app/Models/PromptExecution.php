<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromptExecution extends Model
{
    protected $fillable = [
        'prompt_id', 'variables_values', 'final_content', 'generated_file', 'status'
    ];

    protected $casts = [
        'variables_values' => 'array',
    ];

    public function prompt()
    {
        return $this->belongsTo(Prompt::class);
    }
}
