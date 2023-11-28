<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $casts = [
        'meta' => 'array',
    ];

    protected $fillable = [
        'agent_id',
        'content',
        'sender_id',
        'role',
        'function_name',
        'function_call',
        'meta',
    ];

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function getFunctionCallAttribute($value)
    {
        return json_decode($value, true);
    }

    public function setFunctionCallAttribute($value)
    {
        $this->attributes['function_call'] = json_encode($value);
    }
}
