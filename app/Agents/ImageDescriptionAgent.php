<?php

namespace App\Agents;

use App\Models\Agent;
use App\Models\WritingStyle;

use App\Http\Resources\HousingRoomResource;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Custom\Conversation;
use App\Services\AgentService;
use Illuminate\Support\Facades\Auth;
use OpenAI\Laravel\Facades\OpenAI;
use App\Models\Message;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * This agent is responsible for analyzing the images of a housing and generating a description for each image
 */

class ImageDescriptionAgent extends Agent
{
    public $writingStyle;

    public function getWritingStyleIdAttribute()
    {
        return $this->parameters['writing_style_id'] ?? null;
    }

    public function writingStyle()
    {
        // Verwendung des Accessors
        $writingStyleId = $this->writing_style_id;

        // Manuelle Definition der Beziehung
        if ($writingStyleId) {
            return WritingStyle::where('id', $writingStyleId)->first();
        }

        return null;
    }

    public function run($data = null) {
        $response = [];

        return new StreamedResponse(function () {
            // Load all images from the housing
            $housing = $this->agentable;
            $rooms = $housing->rooms;
            $originalInitialMessage = $this->conversation->getFirstMessage()->content;

            // Go through all available rooms
            foreach($rooms as $room)
            {
                $images = $room->images;

                if(count($images) == 0) {
                    continue;
                }

                // For each image we want to create a image description text
                foreach($images as $image) {
                    // Empty the conversation
                    $this->conversation->clearMessages();

                    // replace the label placeholder in the message
                    $messageContent = str_replace('{{label}}', $room->name, $originalInitialMessage);

                    // replace the writingStyle placeholder, insert the writing style
                    $messageContent = str_replace('{{writingStyle}}', $this->writingStyle()->description, $messageContent);

                    // Create the message content
                    $content = [
                        [
                            "type" => "text",
                            "text" => $messageContent
                        ],
                        [
                            "type" => "image_url",
                            "image_url" => $image->path
                        ],
                    ];

                    // erstelle die message
                    $message = new Message();
                    $message->role = 'user';
                    $message->content = $content;
                    $message->senderId = $housing->user->id;
                    $message->housing_id = $housing->id;

                    $this->addMessage($message);

                    Log::debug('ImageAnalyzer sends message to AI. Conversation:');
                    foreach($this->conversation->getAllMessages() as $message) {
                        Log::debug($message->role . ': ' . print_r($message->content, true));
                    }

                    [$returnMessage, $agentUsage] = parent::run();

                    // get the room to $housing with label $label
                    $image->description = parent::fixUmlauts($returnMessage->content);
                    $image->save();

                    $agentUsage->setEntity($image)
                        ->save();
                }

                // Room is done, lets flush the data to the frontend
                $room->load('images');

                echo "data: " . json_encode(['room' => $room]) . "\n\n";
                ob_flush();
                flush();
            }

            // Now that we have analyzed the images, delete the agent
            // Agent::where('id', $this->id)->delete();

            // return ['type' => 'redirect', 'url' => route('housings.showQuestionnaire', $this->agentable->id, false)];
            $this->has_finished = true;
            $this->save();
            return true;
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no', // Wichtig für Nginx, um Buffering zu deaktivieren
        ]);
    }

    public function finished() {
        Log::debug('ImageAnalyzerAgent::finished()');
        return true;
    }
}
