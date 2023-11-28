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
        'use_functions',
        'functions',
        'fake_responses',
        'fake_enabled'
    ];

    protected $casts = [
        'initial_message' => 'array',
        'functions' => 'array',
        'use_functions' => 'boolean',
        'fake_responses' => 'array',
        'fake_enabled' => 'boolean'
    ];
}
