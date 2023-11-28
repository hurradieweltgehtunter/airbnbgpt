<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HousingContent extends Model
{
    use HasFactory;

    protected $fillable = ['housing_id', 'name', 'content'];

    /**
     * Get the housing that the text belongs to.
     */
    public function housing()
    {
        return $this->belongsTo(Housing::class);
    }
}
