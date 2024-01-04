<?php

namespace App\Agents;

use App\Interfaces\AgentInterface;
use App\Custom\Conversation;
use App\Http\Resources\HousingRoomResource;
use App\Models\Agent;
use App\Models\HousingContent;
use App\Models\Message;
use App\Services\AgentService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use OpenAI\Laravel\Facades\OpenAI;

class WriterAgent extends Agent implements AgentInterface
{
    /**
     * Init Method is only called when the agent is newly created. Not when it is loaded from the database
     */
    public function init() {}

    public function run(array $data = null) {
        $this->prepareMessages();

        // echo each message as role: content
        foreach($this->conversation->getAllMessages() as $message) {
            echo $message->role . ': ' . $message->content . '<br>';
        }
        echo 'WriterAgent: ' . $this->id . "\n";
            exit();

        $response = parent::run();

        if(isset($response->toolCalls)) {
            $arguments = json_decode($response->toolCalls[0]->function->arguments);

            $functionName = $response->toolCalls[0]->function->name;

            $result = $this->$functionName($arguments);
        } else {
            throw new \Exception("No function call received in Writer Agent");
        }

        // Now that we have the texts, delete the agent
        Agent::where('id', $this->id)->delete();

        return to_route('housings.show', $this->agentable->id);

    }

    /**
     * Method to handle the functionCall response from AI
     */
    private function handle_texts($arguments) {
        $contents = array();
        $housing = $this->agentable;

        foreach($arguments as $key => $content) {
            $housingContent = $housing->contents()->updateOrCreate(
                ['name' => $key],
                [
                'housing_id' => $this->agentable->id,
                'name' => $key,
                'content' => $content
            ]);

            $contents[] = $housingContent;
        }

        return $contents;
    }

    /**
     * Method to prepare messages for AI
     * It will load all messages from the questionnaire agent and add them to the conversation
     * Converation structure:
     * [Messages from questionnaire]
     * [Inital Messages from Writer Agent]
     */
    private function prepareMessages()
    {
        // Replace all placeholders in the initial messages
        $updatedInitialMessages = array_map(function($message) {
            return $this->replacePlaceholders($message);
        }, $this->conversation->getAllMessages());

        // Empty the conversation
        $this->clearMessages();

        // Get the conversation from HousingQuestionnaire Agent
        $questionnaireAgent = $this->agentable->agents()->where('name', 'HousingQuestionnaire')->first();

        // Add all messages from questionnaire to conversation
        foreach($questionnaireAgent->conversation->getAllMessages() as $message) {
            $this->addMessage($message);
        }

        // Add all rooms descriptions to the conversation
        $message = new Message();
        $message->role = 'user';
        $message->senderId = $this->agentable->user->id;
        $message->housing_id = $this->agentable->id;
        $message->content = 'Hier eine Beschreibung der Zimmer meiner Unterkunft:' . "\n";

        foreach($this->agentable->rooms as $room) {
            $message->content .= $room->name . ': ' . $room->description . "\n\n";
        }

        $this->addMessage($message);

        // Add the initial messages to the conversation
        foreach($updatedInitialMessages as $message) {
            $this->addMessage($message);
        }
    }

    /**
     * Method to replace placeholders in messages
     */
    private function replacePlaceholders($message) {
        // Lookout for placeholders, which look like {{<placeholdername>}}
        $placeholders = $this->getPlaceholders($message->content);

        foreach($placeholders as $placeholder) {
            $content = '';
            switch($placeholder) {
                case 'writingStyle':
                    $writingStyle = Auth::user()->writingStyles()->where('id', 4)->first();
                    $content = $writingStyle->description;
                    break;
            }

            $message->content = str_replace('{{' . $placeholder . '}}', $content, $message->content);
        }

        return $message;
    }

    /**
     * Method to detect placeholders in a text
     */
    private function getPlaceholders($text) {
        $placeholders = array();
        preg_match_all('/{{(.*?)}}/', $text, $placeholders);
        return $placeholders[1];
    }

    public function finished() : bool
    {
        return true;
    }
}
