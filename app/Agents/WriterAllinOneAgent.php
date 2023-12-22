<?php

namespace App\Agents;

use App\Models\Agent;
use App\Models\Message;
use App\Models\Housing;
use App\Models\WritingStyle;

use App\Http\Resources\HousingRoomResource;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Custom\Conversation;
use App\Services\AgentService;
use Illuminate\Support\Facades\Auth;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Chat\CreateResponse;
use Illuminate\Support\Facades\Log;
use App\Models\WritingStyleExample;
use App\Models\HousingContent;
use Illuminate\Support\Facades\Storage;

class WriterAllinOneAgent extends Agent
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
            return WritingStyle::where('id', $writingStyleId);
        }

        return null;
    }

    public function initRuntime() {
        parent::initRuntime();
        $this->writingStyle = $this->writingStyle()->first();

    }

    /**
     * Init Method is only called when the agent is newly created. Not when it is loaded from the database
     */
    public function init() {

    }

    /**
     * Run Method is called every time the agent is run
     *
     * @param $data: The data from the request:
     * useWritingStyle: int ID of writingStyle to use
     */
    public function run($data = null) {
        if($data === null) {
            throw new \Exception('No writing Style provided');
        }

        $this->prepareMessages();

        $this->use_tools = false;

        [$response, $agentUsage] = parent::run();

        // save $response to text file in storage
        // Storage::put('AllinOneWriterResponse.txt', print_r($response, true));

        if(isset($response->toolCalls) && count($response->toolCalls) > 0) {
            $arguments = json_decode($response->toolCalls[0]->function->arguments);

            $functionName = $response->toolCalls[0]->function->name;

            $result = $this->$functionName($arguments);
        } else {
            $housing = $this->agentable;
            $housingContent = $housing->contents()->updateOrCreate(
                ['name' => 'texts'],
                [
                'housing_id' => $this->agentable->id,
                'name' => 'texts',
                'content' => $response->content
            ]);
        }

        // Now that we have the texts, delete the agent
        // Agent::where('id', $this->id)->delete();

        $this->has_finished = true;
        $this->save();

        $agentUsage->setEntity($this->agentable)
            ->save();
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
                'content' => parent::fixUmlauts($content)
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
        // Get the inital message
        $initialMessage = $this->conversation->getFirstMessage();

        // Empty the conversation
        $this->conversation->clearMessages();

    // 1) Create the prompt for the images
        $baseMessage = new Message();
        $baseMessage->role = 'user';
        $baseMessage->content = 'Analysiere die angehängten hochwertigen Bilder meiner Unterkunft, um ein detailliertes Verständnis ihres Aussehens und der Einrichtung zu erlangen. Nutze diese visuellen Informationen, um die Texte für mein AirBnB-Inserat zu erstellen. Berücksichtige dabei die Stilrichtung, das Farbschema, die Raumgestaltung und besondere Merkmale der Unterkunft, wie sie auf den Bildern zu sehen sind. Diese visuellen Details sollen in die Erstellung der Beschreibungstexte für die Unterkunft, die Gästezugänge und weitere Angaben einfließen, um eine genaue und ansprechende Darstellung meiner Unterkunft zu gewährleisten.';
        $baseMessage->sender_id = $this->agentable->user->id;
        $baseMessage->housing_id = $this->agentable->id;
        $this->addMessage($baseMessage);

    // 2) Prepare messages with images
        // Get all rooms from the housing
        $housing = $this->agentable;
        $rooms = $housing->rooms;

        // Create one message for each room
        foreach($rooms as $room) {
            $message = new Message();
            $message->role = 'user';
            $content = [];
            $message->sender_id = $housing->user->id;
            $message->housing_id = $housing->id;

            $roomImages = $room->images;

            if(count($roomImages) == 0) {
                continue;
            }

            if(count($roomImages)  === 1) {
                $text = 'Dies ist ein Bild von: ' . $room->name;
            } else if(count($roomImages) > 1) {
                $text = 'Dies sind Bilder von: ' . $room->name;
            }

            // Add the room name as placeholder
            $content[] = [
                "type" => "text",
                "text" => $text
            ];

            // Add all images
            foreach($roomImages as $image) {
                $content[] = [
                    "type" => "image_url",
                    "image_url" => $image->path
                ];
            }

            $message->content = $content;

            $this->addMessage($message);
        }

    // 3) Add the dialogue
        // Get the agent with the name HousingQuestionnaire to the current housing
        $questionnaireAgent = $housing->agents()->where('name', 'HousingQuestionnaireAgent')->first();

        // TODO: Clarify if it should be possible to write without having a questionnaire

        if($questionnaireAgent) {
            // Add the prompt:
            $message = new Message();
            $message->role = 'user';
            $message->content = 'Analysiere den folgenden Dialog, in dem ich detaillierte Fragen zu meiner Unterkunft beantworte. Nutze die darin enthaltenen Informationen, um ein tieferes Verständnis von Besonderheiten, Einrichtung, Lage und den einzigartigen Merkmalen meiner Unterkunft zu gewinnen. Diese Details sind wichtig für die Erstellung authentischer und umfassender Texte für mein AirBnB-Inserat. Achte darauf, wie ich auf verschiedene Aspekte eingehe, um diese Informationen effektiv in die Beschreibungen zu integrieren. Achte auch darauf, wer meine Zielgruppe ist und ob ich meine Gäste duzen oder sizen möchte und ob ich aus der ich oder wir form schreiben möchte:' . "\n" . 'DIALOG ANFANG:' . "\n";
            $message->sender_id = $housing->user->id;
            $message->housing_id = $housing->id;
            $this->addMessage($message);



            // TODO: If no questionnaire is present?

            // Now, get all messages to that agent
            $messages = $questionnaireAgent->messages()->get();

            // Add all messages to the conversation
            foreach($messages as $message) {
                $this->addMessage($message);
            }

            $message = new Message();
            $message->role = 'user';
            $message->content = "\n DIALOG ENDE \n";
            $message->sender_id = $housing->user->id;
            $message->housing_id = $housing->id;
            $this->addMessage($message);
        }

    // 4) Add the main prompt
        $this->addMessage($this->replacePlaceholders($initialMessage));

    // // 5) Add the example texts
    //     // Add the prompt
    //     $message = new Message();
    //     $message->role = 'user';
    //     $message->content = 'Schreibe die Texte im selben Stil wie nachfolgender Beispieltext. Analysiere den folgenden Beispieltext gründlich hinsichtlich Schreibstil, die Art der Darstellung, den Ton, die Strukturierung der Informationen, eventuell auffallende Redewendungen usw. Verstehe und repliziere diesen Stil, wenn du die neuen Texte für mein Inserat erstellst. Die Texte sollen in ihrer Ausdrucksweise, ihrem Fluss und ihrer Persönlichkeit dem Beispieltext entsprechen. Übernehme auf keinen Fall Informationen aus dem Beispieltext!';
    //     $message->sender_id = $housing->user->id;
    //     $message->housing_id = $housing->id;
    //     $this->addMessage($message);

    //     // Get the WritingStyleExmaple with id = 3
    //     $writingStyleExample = $this->writingStyle->examples()->first();

    //     // Add the example text
    //     $message = new Message();
    //     $message->role = 'user';
    //     $message->content = "BEISPIELTEXT ANFANG: \n" . $writingStyleExample->content . '\n BEISPIELTEXT ENDE';
    //     $message->sender_id = $housing->user->id;
    //     $message->housing_id = $housing->id;
    //     $this->addMessage($message);
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
                    $content = $this->writingStyle->description;
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

    public function finished() {
        // return json response
        return response()->json(['type' => 'redirect', 'url' => route('housings.show', ['housing' => $this->agentable], false)]);
    }
}
