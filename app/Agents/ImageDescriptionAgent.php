<?php

namespace App\Agents;

use App\Interfaces\AgentInterface;
use App\Custom\Conversation;
use App\Http\Resources\HousingRoomResource;
use App\Models\Agent;
use App\Models\Message;
use App\Models\WritingStyle;
use App\Services\AgentService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;
use Symfony\Component\HttpFoundation\StreamedResponse;
/**
 * This agent is responsible for analyzing the images of a housing and generating a description for each image
 */

class ImageDescriptionAgent extends Agent implements AgentInterface
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

    public function run(array $data = null) {
        $response = [];

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

        }

        // Now that we have analyzed the images, delete the agent
        // Agent::where('id', $this->id)->delete();

        // return ['type' => 'redirect', 'url' => route('housings.showQuestionnaire', $this->agentable->id, false)];
        $this->has_finished = true;
        $this->save();
        return true;
    }

    public function finished() : bool
    {
        Log::debug('ImageAnalyzerAgent::finished()');
        return true;
    }
}
