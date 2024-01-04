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
use OpenAI\Laravel\Facades\OpenAI;

class WritingStyleAnalyzerAgent extends Agent implements AgentInterface
{
    /**
     * Init Method is only called when the agent is newly created. Not when it is loaded from the database.
     */
    public function init()
    {

    }

    /**
     * The attributes that are mass assignable.
     *
     * $data string The text to analyze
     */
    public function run(array $data = null) : WritingStyle {
        $this->initRuntime();
        $response = [];
        // $writingStyle = $this->agentable;

        // $textExamples = $writingStyle->examples()->get();

        $initialMessage = $this->conversation->getFirstMessage();
        $this->conversation->clearMessages();

        // Replace placeholders in initial message
        $this->replacePlaceholder($initialMessage, 'exampleText', $data);
        $this->conversation->addMessage($initialMessage);

        [$response, $agentUsage] = parent::run();

        if(isset($response->toolCalls)) {
            $arguments = json_decode($response->toolCalls[0]->function->arguments);

            $functionName = $response->toolCalls[0]->function->name;

            [$title, $description] = $this->$functionName($arguments);
        }

        $writingStyle = new WritingStyle();
        $writingStyle->description = $description;
        $writingStyle->title = $title;
        $writingStyle->user_id = Auth::id();
        $writingStyle->save();

        // Delete the Analyzer Agent
        Agent::where('id', $this->id)->delete();

        $agentUsage->setEntity($writingStyle);
        $agentUsage->save();

        return $writingStyle;
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

    public function finished() : \Illuminate\Http\JsonResponse
    {
        // return WritingStyle as json response
        return response()->json($writingStyle->toArray());
    }

    private function replacePlaceholder(&$message, $placeholder, $replacement) {
        $message->content = str_replace('{{' . $placeholder . '}}', $replacement, $message->content);
    }
}
