<?php
/**
 * GPTModels are the corresponding, available open ai models.
 * https://platform.openai.com/docs/models
 *
 * Check out the GptModelsTableSeeder for available models.
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GptModel extends Model
{
    protected $fillable = [
        'name',
        'enabled',
        'cost_input_1k',
        'cost_output_1k'
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    /**
     * Get the agents using this model.
     */
    public function agents()
    {
        return $this->hasMany(Agent::class);
    }

    /**
     * Get the usages for the model.
     * Using the name as the foreign key is indented, since we want to log the real model name in the agent_usages table.
     */
    public function usages() {
        return $this->hasMany(AgentUsage::class, 'model', 'name');
    }
}
