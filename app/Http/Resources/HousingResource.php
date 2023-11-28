<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use Carbon\Carbon;
use App\Models\User;
use App\Models\HousingImage;

use App\Models\Housing;
class HousingResource extends JsonResource
{
    public static $wrap = null;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

    public function toArray($request)
    {

        $housing = parent::toArray($request);

        $housing['is_finished'] = $this->is_finished;

        return $housing;
    }
}


