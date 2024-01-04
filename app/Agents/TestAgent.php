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

class TestAgent extends Agent implements AgentInterface
{

    public function run(array $data = null) {

        [$response, $agentUsage] = parent::run();

        echo '<pre>';
            print_r($response);
            echo '</pre>';
        if(isset($response->toolCalls)) {
            $arguments = json_decode($response->toolCalls[0]->function->arguments);

            $functionName = $response->toolCalls[0]->function->name;

            $result = $this->$functionName($arguments);

        } else {
            echo '<pre>';
            print_r($response);
            echo '</pre>';

            echo parent::fixUmlauts($response->content);
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

        echo '<pre>';
        print_r($arguments);
        echo '</pre>';


        return '';
    }

    public function finished() : bool
    {
        return true;
    }
}
