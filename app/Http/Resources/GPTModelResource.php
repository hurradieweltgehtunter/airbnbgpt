<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\WritingStyleResource;

class GPTModelResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'cost_input_1k' => $this->cost_input_1k,
            'cost_output_1k' => $this->cost_output_1k,
            'enabled' => $this->enabled,
        ];
    }
}
