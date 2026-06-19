<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prompt extends Model
{
    protected $fillable = [
        'category_id', 'title', 'content', 'original_file', 'source'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variables()
    {
        return $this->hasMany(PromptVariable::class);
    }

    public function executions()
    {
        return $this->hasMany(PromptExecution::class);
    }
}
