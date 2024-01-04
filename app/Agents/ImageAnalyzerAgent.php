<?php

namespace App\Agents;

use App\Interfaces\AgentInterface;
use App\Custom\Conversation;
use App\Http\Resources\HousingRoomResource;
use App\Models\Agent;
use App\Models\Message;
use App\Services\AgentService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

/**
 * This agent analyzes images and ...?!
 */
class ImageAnalyzerAgent extends Agent implements AgentInterface
{
    public function run(array $data = null) {
        $response = [];

        // Load all images from the housing
        $housing = $this->agentable;
        $rooms = $housing->rooms;
        $originalInitialMessage = $this->conversation->getFirstMessage()->content;

        // For each room we want now an AI generated message
        foreach($rooms as $room)
        {
            $images = $room->images;

            if(count($images) == 0) {
                continue;
            }

            // replace the label placeholder in the message
            $messageContent = str_replace('{{label}}', $room->name, $originalInitialMessage);

            // Empty the conversation
            $this->conversation->clearMessages();

            // Create the message content
            $content = [
                [
                    "type" => "text",
                    "text" => $messageContent
                ],
            ];

            // Add all images
            foreach($images as $image) {
                $content[] = [
                    "type" => "image_url",
                    "image_url" => $image->path
                ];
            }

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

            Log::debug('ImageAnalyzer received message from AI: ' . $returnMessage->content);

            $room->description = parent::fixUmlauts($returnMessage->content);
            $room->save();

            $agentUsage->setEntity($room)
                ->save();

            // Reset the messages
            $this->loadMessagesIntoConversation();
        }

        $this->has_finished = true;
        $this->save();

        return $this->finished();
    }

    public function finished() : array
    {
        Log::debug('ImageAnalyzerAgent::finished()');
        return ['type' => 'redirect', 'url' => route('housings.showQuestionnaire', $this->agentable->id, false)];
    }
}
