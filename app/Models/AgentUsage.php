<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AgentUsage extends Model
{
    private $fake_enabled = false;

    protected $fillable = [
        'user_id', 'entity_type', 'entity_id',
        'prompt_tokens', 'completion_tokens', 'model', 'processing_time', 'agent'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function entity()
    {
        return $this->morphTo();
    }

    public function getTotalTokensAttribute()
    {
        return $this->prompt_tokens + $this->completion_tokens;
    }

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();
        static::saving(function ($agentUsage) {
            // If request is a fake request, don't save the usage because there is none
            if($agentUsage->fake_enabled)
                return false;
        });
    }

    public static function create($agent, $response, $entity = null)
    {
        $data = [
            'agent' => get_class($agent),
            'model' => $response->meta()->openai->model,
            'user_id' => Auth::id(),
            'prompt_tokens' => $response->usage->promptTokens,
            'completion_tokens' => $response->usage->completionTokens,
            'processing_time' => $response->meta()->openai->processingMs,
        ];

        if($entity)
        {
            $data['entity_type'] = get_class($entity);
            $data['entity_id'] = $entity->id;
        }

        $agentUsage = new self($data);

        $agentUsage->fake_enabled = $agent->fake_enabled;

        return $agentUsage;
    }

    /**
     * Set the entity for the AgentUsage.
     *
     * @param  Model  $model
     * @return $this
     */
    public function setEntity(Model $model)
    {
        $this->entity_type = get_class($model);
        $this->entity_id = $model->getKey();

        return $this;
    }
}
