<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Agent;
use App\Factories\AgentFactory;
use Illuminate\Support\Facades\Auth;

class WritingStyle extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function examples()
    {
        return $this->hasMany(WritingStyleExample::class);
    }

    public function agents()
    {
        return $this->morphMany(Agent::class, 'agentable');
    }

    public function agentUsages()
    {
        return $this->morphMany(AgentUsage::class, 'entity');
    }

    public static function createForUser($userId, $attributes = [])
    {
        $writingStyle = new WritingStyle(); // Erstellt eine neue Instanz des Housing-Models
        foreach($attributes as $key => $value) {
            $writingStyle->$key = $value;
        }

        $writingStyle->user_id = $userId;

        $writingStyle->save();
        $writingStyle->refresh();

        return $writingStyle;
    }

    public function addAgent($agent) {
        // Füge dem WritingStyle-Objekt den Agenten hinzu
        $this->agents()->save($agent);

        return $agent;
    }
}
