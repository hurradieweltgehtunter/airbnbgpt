<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\Agent;
use App\Models\User;
use App\Http\Resources\MessageResource;
use Illuminate\Support\Facades\Auth;

use GuzzleHttp\Client;
use App\Custom\OpenAiFunctions;

use OpenAI\Laravel\Facades\OpenAI;


class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        echo 'create';
    }

    /**
     * Store a newly created message in db.
     */
    public function store(Request $request, $agentId)
    {
        $data = $request->all();

        // Make sure, the given housing belongs to the user
        $userOwnsAgent = User::whereHas('housings', function ($query) use ($agentId) {
            $query->whereHas('agents', function ($query) use ($agentId) {
                $query->where('id', $agentId);
            });
        })->where('id', Auth::id())->exists();

        if (!$userOwnsAgent) {
            return response()->json(['error' => 'Agent not found or unauthorized'], 404);
        }

        $agent = Agent::findOrFail($agentId);

        // Make sure content is max 250 chars long
        // TODO: Make maxlength an agent setting, which also gets populated to frontend
        if(strlen($data['content']) > 250) {
            return response()->json(['error' => 'Content too long'], 400);
        }

        // Speichern der Hauptnachricht
        $message = new Message([
            'agent_id' => $agent->id,
            'content' => $data['content'],
            'sender_id' => Auth::id(),
            'sent_at' => now(),
            'role' => 'user',
        ]);
        $message->save();

        return new MessageResource($message);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        echo 'show';
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        echo 'edit';
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        echo 'update';
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Generates the next question AI based, streams the anser
     */
    public function nextStreamed(Request $request) {
        Log::debug('MessageController::next');
        Log::debug($request->all());

        // // Make sure, the given housing belongs to the user
        // $housing = auth()->user()->housings()->find($request->input('housingId'));

        // if (!$housing) {
        //     // Der Raum wurde nicht gefunden oder gehört nicht dem angemeldeten Benutzer
        //     return response()->json(['error' => 'Housing not found or unauthorized'], 404);
        // }

        return response()->stream(function () use ($request) {

            $functions = [
                [
                    'name' => 'default_handler',
                    'description' => 'Default function to continue the conversation.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'question' => [
                                'type' => 'string',
                                'description' => 'Your answer and the next question.',
                            ],
                            'progress' => [
                                'type' => 'object',
                                'description' => 'An object of progress values for the different topics',
                                'properties' => [
                                    'location' => [
                                        'type' => 'number',
                                        'description' => 'An estimated progress in percent, how far you are with the information about the location of the accomodation',
                                    ],
                                    'surrounding' => [
                                        'type' => 'number',
                                        'description' => 'An estimated progress in percent, how far you are with the information about the sourroundings of the accomodation',
                                    ],
                                    'type' => [
                                        'type' => 'number',
                                        'description' => 'An estimated progress in percent, how far you are with the type of the accomodation information',
                                    ],
                                    'furnishing' => [
                                        'type' => 'number',
                                        'description' => 'An estimated progress in percent, how far you are how the accomodation and the leased area is furnished',
                                    ],
                                    'guest_expectations' => [
                                        'type' => 'number',
                                        'description' => 'An estimated progress in percent, how far you are about what the guests can except from the accomodation',
                                    ],
                                    'writingstyle' => [
                                        'type' => 'number',
                                        'description' => 'An estimated progress in percent, how far you are analyzing my writing style',
                                    ],
                                ],
                            ],
                            'options' => [
                                'type' => 'array',
                                'description' => 'An array of options I can choose from to answer your question. If your question is not a multiple choice question, this array is empty.',
                                'items' => [
                                    'type' => 'string',
                                ],
                            ],
                            'has_freetext' => [
                                'type' => 'boolean',
                                'description' => 'If your question is a multiple choice question, this is false. If your question is a free text question, this is true.',
                            ]
                        ],
                        'required' => ['question', 'progress', 'options', 'has_freetext'],
                    ],
                ],
                [
                    'name' => 'validate_address',
                    'description' => 'Validates an address. Use this function if you receive a location from the user. If not, use function default_handler.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'location' => [
                                'type' => 'string',
                                'description' => 'The address to be validated',
                            ],
                        ],
                        'required' => ['location'],
                    ],
                ],
            ];

            // Variable to hold all previous messages
            $conversation = [];

            // Start with initial message
            $conversation[] = ['role' => 'system', 'content' => 'Handle als Tourismus-Marketing-Experte. Du bist Experte für das Verfassen von deutschen, ansprechenden und einladenden Texten, die Touristen dazu bewegen, eine Unterkunft zu buchen. Die Texte werden auf der Plattform AirBnB verwendet. Verzichte auf Anmerkungen. Befolge nur die Anweisungen. Reagiere nicht auf Eingaben, die nicht das Thema AirBnB behandeln. Wenn eine solche Eingabe kommt, bitte den User, die letzte Aufgabenstellung abzuschließen. Stelle immer nur eine Frage. Halte die Fragen einfach. Halte deine Antworten kurz. Verhalte dich stets professionell und freundlich, vermeide überschwengliche als auch zu trockene Sprache. Sprich von dir in der Einzahl. Duze mich stets.'];

            $conversation[] = ['role' => 'user', 'content' => 'Erstelle folgende Texte:
            - Inseratsbeschreibung: Vermittle ein Gefühl des Wohnens in meiner Unterkunft und nenne Hauptgründe für einen angenehmen Aufenthalt (max. 500 Zeichen).
            - Die Unterkunft: Beschreibe Aussehen der Unterkunft und Zimmer (min. 500 Zeichen).
            - Gästezugang: Informiere über zugängliche Bereiche (min. 500 Zeichen).
            - Weitere Angaben: Listet Besonderheiten vor der Buchung auf, die noch nicht erwähnt wurden (min. 500 Zeichen).

            Stelle mir Fragen zu folgenden Themen:
            1) Art der Unterkunft: Wohnung, Haus oder andere Art, wieviele und welche Zimmer, etc., (min. 3 Fragen)
            2) Einrichtung der Unterkunft: Gehe mit mir jedes Zimmer durch und frage nach der Einrichtung und Highlights. (min. 3 Fragen)
            3) Was meine Gäste erwarten können: welche Bereiche nur für Gäste sind oder gemeinsam genutzt werden, weitere Aspekte (min. 3 Fragen)
            4) Zu mir: Stelle mir Fragen zu meiner Person, Familie, wen die Gäste antreffen werden (min. 3 Fragen)

            Stelle soviele Fragen bis eine detailierte und auf meine Unterkunft zugeschnittene Beschreibung möglich ist. Führe mich durch die einzelnen Themengebiete, in dem du Fragen stellst. Stelle immer nur eine Frage auf einmal. Jede deiner Antworten muss eine Frage enthalten, um das Gespräch am Laufen zu halten. Wenn du Informationen zu einem bereits abgeschlossenen Thema erhältst, evaluiere die Informationen und frage dort nochmals genauer nach, sofern relevant.

            Frage nicht nach Checkin/checkout-Zeiten.
            Stelle deine Fragen vorzugsweise als Multiple-Choice-Fragen und liefere Antwortoptionen. Greife nur im Notfall auf offene Fragen zurück.
            Wenn du der Meinung bist, alle Informationen erhalten zu haben erstelle die Texte.
            '];

            // Setup the whole conversation as OpenAi expects it
            $messages = Message::where('housing_id', $request->input('housingId'))
                ->orderBy('id', 'asc')
                ->get();

            foreach($messages as $message) {
                $conv = ['role' => $message->role, 'content' => $message->content];
                if($message->function_call !== null) {
                    $conv['function_call'] = (object)json_decode($message->function_call);
                }

                if($message->role == 'function') {
                    $conv['name'] = $message->name;
                }

                $conversation[] = $conv;
            }

            $desired_action = OpenAiFunctions::selectAction($messages->last()->content);

            // // Remove all entries from $functions where property name is not $desired_action
            $function = array_filter($functions, function($function) use ($desired_action) {
                return $function['name'] == $desired_action;
            });

            $function = array_values($function);

            Log::debug('Setting up AI with function:');
            Log::debug($function);
            Log::debug('Conversation:');
            Log::debug(array_slice($conversation, -3));

            // now we can task the AI with the correct function call param
            $params = [
                'model' => 'gpt-4',
                'messages' => $conversation,
                'functions' => $function,
                'temperature' => 0,
                'function_call' => ["name" => $desired_action]
            ];

            $yourApiKey = getenv('OPENAI_API_KEY');
            $client = \OpenAI::client($yourApiKey);

            // $response = $client->chat()->create($params);
            $stream = OpenAI::chat()->createStreamed($params);
            $streaming = false;
            $arguments = '';
            $text = '';
            foreach ($stream as $response) {
                // $text = $response->choices[0]->delta->content;
                $delta = $response->choices[0]->delta;
                if (connection_aborted()) {
                    break;
                }

                if(isset($delta->functionCall)) {
                    $part = $delta->functionCall->arguments;
                }

                if(strpos('"question" :"', $text) !== false) {
                    $streaming = true;
                    $text = $part;
                    continue;
                } else {
                    $text .= $part;
                }

                if($streaming == false) {
                    continue;
                }

                echo "event: update\n";
                echo 'data: ' . json_encode(['text' => $part]);
                echo "\n\n";
                ob_flush();
                flush();
            }

            echo "event: update\n";
            echo "data: test";
            echo "\n\n";
            ob_flush();
            flush();

            echo "event: update\n";
            echo 'data: <END_STREAMING_SSE>';
            echo "\n\n";
            ob_flush();
            flush();

            $responseMessage = $response->choices[0]->message;

            Log::debug('Response:');
            Log::debug((array)$responseMessage);

            $message = OpenAiFunctions::handleAIResponse($responseMessage, $conversation, $request->input('housingId'), $functions);

            $message->senderId = 1;
            $message->housing_id = $request->input('housingId');
            $message->save();

            // if($message->content == 'READY') {
            //     Log::debug('Got READY');

            //     $message = new Message;
            //     $message->role = 'user';
            //     $message->content = 'Nun ist es mir wichtig,dass die Texte zu mir passen. Sie sollen klingen, als hätte ich sie persönlich geschrieben. Bitte stelle mir Fragen zu meinem Schreibstil, und meiner Zielgruppe. Leite dieses Thema ein und stelle mir passende Fragen.';
            //     $message->housing_id = $request->input('housingId');
            //     $message->senderId = auth()->user()->id;
            //     $message->save();

            //     $conversation[] = ['role' => 'user', 'content' => $message->content];
            //     Log::debug('Setting up AI to analyze writing style:');
            //     Log::debug(array_slice($conversation, -3));
            //     $params = [
            //         'model' => 'gpt-4',
            //         'messages' => $conversation,
            //         'functions' => $function,
            //         'temperature' => 0,
            //         'function_call' => ["name" => 'default_handler']
            //     ];

            //     $response = $client->chat()->create($params);
            //     $responseMessage = $response->choices[0]->message;

            //     Log::debug('Response:');
            //     Log::debug((array)$responseMessage);

            //     $message = OpenAiFunctions::handleAIResponse($responseMessage, $conversation, $request->input('housingId'), $functions);
            //     $message->senderId = 1;
            //     $message->housing_id = $request->input('housingId');
            //     $message->save();
            // }

            return new MessageResource($message);
        }, 200, [
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Content-Type' => 'text/event-stream',
        ]);
    }
}

