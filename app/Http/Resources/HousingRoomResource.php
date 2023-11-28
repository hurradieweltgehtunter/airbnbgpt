<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HousingRoomResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'housing_id' => $this->housing_id,
            'name' => $this->name,
            'description' => $this->description,
            'images' => HousingImageResource::collection($this->whenLoaded('images'))
        ];
    }
}
