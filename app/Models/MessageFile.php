<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageFile extends Model
{
    protected $fillable = [
        'message_id', 'name', 'size', 'type', 'audio', 'duration', 'url', 'preview_url', 'progress'
    ];

    // Eine Beziehung, die die Nachricht eines Dateianhangs zurückgibt
    public function message()
    {
        return $this->belongsTo(Message::class);
    }
}
