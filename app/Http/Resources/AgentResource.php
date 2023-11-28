<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\WritingStyleResource;

class AgentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $return = [
            'entity' => class_basename($this->agentable_type),
            'id' => $this->id,
            'name' => $this->name,
        ];

        if(isset($this->writingStyle)) {
            $return['writingStyle'] = new WritingStyleResource($this->writingStyle);
        }

        return $return;
    }
}
