<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use Carbon\Carbon;
use App\Models\User;

class MessageResource extends JsonResource
{
    public static $wrap = null;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

    public function toArray($request) : array
    {
        $sentDate = new Carbon($this->sent_at ?? now());
        $formattedDate = $sentDate->format('j F'); // z.B. '13 November'
        $formattedTime = $sentDate->format('H:i'); // z.B. '10:20'

        // Get a username by id
        $sender = User::find($this->sender_id);

        return [
            'id'              => $this->id,
            'content'         => $this->content,
            'sender_id'       => strval($this->sender_id),
            'username'        => $sender->name,
            'avatar'          => $sender->avatar,
            'date'            => $formattedDate,
            'isFinal'         => $this->isFinal ?? false,
            'meta'            => $this->meta ?? [],
        ];
    }
}


