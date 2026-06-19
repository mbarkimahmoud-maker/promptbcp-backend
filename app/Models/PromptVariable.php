<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromptVariable extends Model
{
    protected $fillable = [
        'prompt_id', 'name', 'label', 'default_value'
    ];

    public function prompt()
    {
        return $this->belongsTo(Prompt::class);
    }
}
