<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HousingRoom extends Model
{
    use HasFactory;

    protected $fillable = ['housing_id', 'name', 'description'];

    /**
     * Get the housing that the image belongs to.
     */
    public function housing()
    {
        return $this->belongsTo(Housing::class);
    }

    /**
     * Get all iamges of the room.
     */

    public function images()
    {
        return $this->hasMany(HousingImage::class, 'room_id');
    }
}
