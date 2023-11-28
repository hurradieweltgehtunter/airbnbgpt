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

class WritingStyleAnalyzerAgent extends Agent
{
    public function run() {
        $response = [];
        $writingStyle = $this->agentable;

        $textExamples = $writingStyle->examples()->get();

        $message = new Message();
        $message->role = 'user';
        $message->content = $textExamples[0]->content;
        $message->senderId = $writingStyle->user->id;

        $this->addMessage($message);
        $this->functionCall = ['name' => 'analysis_handler'];
        $response = parent::run();

        if(isset($response->functionCall)) {
            $arguments = json_decode($response->functionCall->arguments);
            $functionName = $response->functionCall->name;

            [$title, $description] = $this->$functionName($arguments);
        }


        $writingStyle->description = $description;
        $writingStyle->title = $title;
        $writingStyle->save();

        // Delete the Analyzer Agent
        Agent::where('id', $this->id)->delete();

    }

    /**
     * Handles the AI generated text analysis.
     *
     * @param array $arguments: [title, description]
     */
    private function analysis_handler($arguments) {
        $title = $arguments->title;
        $description = $arguments->description;

        return [$title, $description];
    }

    public function finished() {
        // return WritingStyle as json response
        return response()->json($writingStyle->toArray());
    }
}
