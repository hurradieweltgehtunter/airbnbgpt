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

use App\Models\HousingContent;

class TestAgent extends Agent
{

    public function run($data = null) {

        $this->functionCall = ['name' => 'handle_texts'];

        $response = parent::run();

        echo '<pre>';
            print_r($response);
            echo '</pre>';
        if(isset($response->functionCall)) {
            $functionName = $response->functionCall->name;

            $result = $this->$functionName($response);
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
    private function handle_texts($response) {
        $arguments = json_decode($response->functionCall->arguments);

        echo '<pre>';
        print_r($arguments);
        echo '</pre>';


        return $contents;
    }

    public function finished() {
        return true;
    }
}
