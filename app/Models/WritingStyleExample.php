<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WritingStyleExample extends Model
{
    use HasFactory;

    protected $fillable = ['writing_style_id', 'content'];

    public function writingStyle()
    {
        return $this->belongsTo(WritingStyle::class);
    }
}
