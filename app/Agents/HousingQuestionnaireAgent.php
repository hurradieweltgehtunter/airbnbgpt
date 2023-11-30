<?php

namespace App\Agents;

use App\Models\Agent;

use App\Http\Resources\HousingRoomResource;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Custom\Conversation;
use App\Services\AgentService;
use Illuminate\Support\Facades\Auth;
use OpenAI\Laravel\Facades\OpenAI;
use App\Models\Message;
use App\Http\Resources\MessageResource;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\AgentResource;

class HousingQuestionnaireAgent extends Agent
{
    public function messages()
    {
        return $this->hasMany(Message::class, 'agent_id');
    }

    /**
     * Init the agent. This method gets only called on agent creation (Agent::createFromName)
     */
    public function init() {

        // Add the address to the conversation
        $content = 'Die Adresse meiner Unterkunft lautet: ';

        $content .= $this->agentable->address_street . ' ' . $this->agentable->address_street_number . ',';
        $content .= ' ' . $this->agentable->address_zip . ' ' . $this->agentable->address_city;

        if($this->agentable->address_sublocality_level_1)
            $content .= ' ' . $this->agentable->address_sublocality_level_1 . ',';

        if($this->agentable->address_administrative_area_level_1)
            $content .= ' ' . $this->agentable->address_administrative_area_level_1 . ',';

        $content .= ' ' . $this->agentable->address_country . '. ';

        $message = new Message();
        $message->agent_id = $this->id;
        $message->role = 'user';
        $message->sender_id = Auth::id();
        $message->content = $content;

        $message->save();

        // Add the known rooms to the conversation
        $content = 'In meiner Unterkunft gibt es folgende Bereiche: ' . "\n";

        foreach($this->agentable->rooms as $room) {
            $content .= '- ' . $room->name . ":\n";
            $content .= $room->description . "\n\n";
        }

        $content .= 'Frage mich aber nochmal, ob dies alle Bereiche sind oder ob es noch weitere gibt.';

        $message = new Message();
        $message->agent_id = $this->id;
        $message->role = 'user';
        $message->sender_id = Auth::id();
        $message->content = $content;

        $message->save();
    }

    public function run($data = null) {

        // If the message count is exceeded (e.g. 10 messages), close the agent
        // TODO: Make this tokenbased
        // TODO: Make this an agent setting
        if($this->conversation->userMessageCountExceeded()) {
            Log::debug('HousingQuestionnaire::run() - Message count exceeded. Userid: ' . Auth::id());
            return $this->closeAgent();
        }

        // Get the last message
        $lastMessage = $this->conversation->getLastMessage();

        // If the last message is already from AI, return the last message
        if($lastMessage->sender_id === 1) {
            // If not, return the last message
            return ['type' => 'message', 'message' => new MessageResource($lastMessage)];
        }

        $response = parent::run();

        // Handle the AI Response
        if(isset($response->toolCalls)) {
            $arguments = json_decode($response->toolCalls[0]->function->arguments);
            $functionName = $response->toolCalls[0]->function->name;

            $result = $this->$functionName($response);
        } else {
            $result = $response->content;
        }

        return response()->json($result);
    }

    private function default_handler($response)
    {
        Log::debug('HousingQuestionnaire::default_handler()');
        Log::debug((array) $response);

        try {
            $arguments = json_decode($response->functionCall->arguments);

            if($arguments->q === 'READY') {
                return $this->closeAgent();
            }

            $message = new Message();
            $message->agent_id = $this->id;
            $message->role = $response->role;
            $message->content = parent::fixUmlauts($arguments->q);
            $message->sender_id = 1;
            $message->meta = [
                'progress' => $arguments->p,
                'options' => parent::fixUmlauts($arguments->o),
                'multipleOptions' => $arguments->mo ?? false,
                'hasFreetext' => $arguments->f ?? false,
            ];

            $message->save();
            $this->addMessage($message);

            return ['type' => 'message', 'message' => new MessageResource($message)];

        } catch (\Exception $e) {
            echo 'Error in default handler: ' . $e->getMessage();
            echo '<pre>';
            print_r($response);
            echo '</pre>';
            throw $e;
        }
    }


    /**
     * Method, to close this specific agent. After called once, this agent cannot be run again.
     */
    public function closeAgent()
    {
        $this->has_finished = true;
        $this->save();

        return $this->finished();
    }

    /**
     * Method which handles the finished state of the agent.
     * Gets called, if the agent is closed (has_finished = true)
     */
    public function finished()
    {
        Log::debug('HousingQuestionnaire::finished()');
        Log::debug('Forwarding to housings.writingstyleSelect with housingId ' . $this->agentable->id);
        return ['type' => 'redirect', 'url' => route('housings.writingstyleSelect', $this->agentable->id, false)];
    }
}
