<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageReaction extends Model
{
    protected $fillable = ['message_id', 'reaction', 'user_id'];

    // Eine Beziehung, die die Nachricht einer Reaktion zurückgibt
    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    // Eine Beziehung, die den Benutzer einer Reaktion zurückgibt
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
