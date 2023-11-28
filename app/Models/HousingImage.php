<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HousingImage extends Model
{
    use HasFactory;

    protected $fillable = ['room_id', 'path'];

    /**
     * Get the housing that the image belongs to.
     */
    public function room()
    {
        return $this->belongsTo(HousingRoom::class);
    }

    public function housing()
    {
        return $this->belongsTo(Housing::class);
    }

}
