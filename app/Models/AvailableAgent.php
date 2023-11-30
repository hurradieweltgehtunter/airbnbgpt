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
        'model',
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
        'fake_enabled' => 'boolean'
    ];
}
