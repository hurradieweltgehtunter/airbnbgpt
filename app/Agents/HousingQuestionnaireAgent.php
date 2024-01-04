<?php

namespace App\Agents;

use App\Interfaces\AgentInterface;
use App\Custom\Conversation;
use App\Http\Resources\AgentResource;
use App\Http\Resources\HousingRoomResource;
use App\Http\Resources\MessageResource;
use App\Models\Agent;
use App\Models\Message;
use App\Services\AgentService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class HousingQuestionnaireAgent extends Agent implements AgentInterface
{
    public function messages()
    {
        return $this->hasMany(Message::class, 'agent_id');
    }

    /**
     * Init the agent. This method gets only called on agent creation (Agent::createFromName)
     */
    public function init() {
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

    public function run(array $data = null) {

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

        [$response, $agentUsage] = parent::run();

        // Handle the AI Response
        if(isset($response->toolCalls) && count($response->toolCalls) > 0) {
            $arguments = json_decode($response->toolCalls[0]->function->arguments);
            $functionName = $response->toolCalls[0]->function->name;

            $result = $this->$functionName($response);
        } else {
            $result = $response->content;
        }

        $agentUsage->setEntity($this->agentable)
            ->save();

        return response()->json($result);
    }

    private function default_handler($response)
    {
        Log::debug('HousingQuestionnaire::default_handler()');
        Log::debug((array) $response);

        try {
            $arguments = json_decode($response->toolCalls[0]->function->arguments);

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
    public function finished() : array
    {
        Log::debug('HousingQuestionnaire::finished()');
        Log::debug('Forwarding to housings.writingstyleSelect with housingId ' . $this->agentable->id);
        return ['type' => 'redirect', 'url' => route('housings.writingstyleSelect', $this->agentable->id, false)];
    }
}
