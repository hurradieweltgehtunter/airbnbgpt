<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvailableAgent extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'system_prompt',
        'initial_message',
        'gpt_model_id',
        'use_tools',
        'tools',
        'fake_responses',
        'fake_enabled'
    ];

    protected $casts = [
        'initial_message' => 'array',
        'tools' => 'array',
        'use_tools' => 'boolean',
        'fake_responses' => 'array',
        'fake_enabled' => 'boolean',
        'model' => 'integer'
    ];

    public function gptmodel()
    {
        return $this->belongsTo(GPTModel::class, 'gpt_model_id');
    }
}
