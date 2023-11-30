<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HousingIndexResource extends JsonResource
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
            'address_street' => $this->address_street,
            'address_street_number' => $this->address_street_number,
            'address_zip' => $this->address_zip,
            'address_city' => $this->address_city,
            'address_country' => $this->address_country,
            'address_administrative_area_level_1' => $this->address_administrative_area_level_1,
            'address_sublocality' => $this->address_sublocality,
            'address_sublocality_level_1' => $this->address_sublocality_level_1,
            'created_at' => $this->created_at
        ];
    }
}
