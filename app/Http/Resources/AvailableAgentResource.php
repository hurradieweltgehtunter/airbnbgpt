<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\WritingStyleResource;

class AvailableAgentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $return = [
            'id' => $this->id,
            'name' => $this->name,
            'fake_enabled' => $this->fake_enabled,
            'initial_message' => $this->initial_message,
            'system_prompt' => $this->system_prompt,
            'model' => $this->model,
        ];

        return $return;
    }
}
