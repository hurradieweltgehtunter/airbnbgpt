<?php

namespace App\Custom;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use OpenAI\Laravel\Facades\OpenAI;

use App\Custom\Conversation;
use App\Models\Message;
use App\Models\Housing;
use App\Custom\OpenAiFunctions;

class ChatGPT {
    private $client;
    public $conversation;
    private $model = 'gpt-4';
    private $functionCall = ['name' => 'default_handler'];
    private $housingId = null;
    public $useFunctions = true; // Whether to send functions calls to the AI or not
    private $systemPrompt = '';

    // The functions that are actually sent to the AI
    private $functions = [];

    public function __construct() {

    }


    public function setFunctionCall($fName = null) {
        Log::debug('OpenAiFunctions::setFunctionCall() -> ' . $fName);

        if($fName == null) {
            $this->functions = $this->availableFunctions;
            $this->functionCall = ['name' => 'default_handler'];
            Log::debug('set function call to default_handler');
        }

        // Make sure, $fName is withing the available functions
        $availableFunctionNames = array_map(function($function) {
            return $function['name'];
        }, $this->availableFunctions);

        if(!in_array($fName, $availableFunctionNames)) {
            Log::debug('Function ' . $fName . ' is not available');
            return false;
        } else {
            Log::debug('Function ' . $fName . ' is available');
        }

        Log::debug('Filtering out unnecessary functions, leaving functions:');
        $this->functionCall = ['name' => $fName];
        $this->functions = array_values(array_filter($this->availableFunctions, function($function) use ($fName) {
            return $function['name'] == $fName;
        }));

        foreach($this->functions as $f) {
            Log::debug('Function: ' . $f['name']);
        }

    }

    private function loadConversation() {
        // Load all messages for the authenticated user ordered by id desc
        $messages = Message::where('housing_id', $this->housingId)->orderBy('id', 'asc')->get();
        $this->conversation = new Conversation($messages);
    }

    /**
     * Method to set the system prompt
     */
    public function setSystemPrompt($prompt) {
        $this->systemPrompt = $prompt;
    }

    public function setModel($model) {
        $this->model = $model;
    }

    public function selectAction() {
        Log::debug('chatGPT::selectAction()');

        $selectFunction = [
            [
                'name' => 'select_action',
                'description' => 'Selects the next action.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'action' => [
                            'type' => 'string',
                            'enum' => ['default_handler'],
                            'description' => 'Action name to accomplish a task',
                        ],
                    ],
                    'required' => ['action'],
                ],
            ],
        ];

        $params = [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a very helpful assistant. Your job is to choose the best posible action to solve the user question or task.
                        These are the available actions:
                        - default_handler: Use this, if no other action is suitable, params is the input',
                ],
                [
                    'role' => 'user',
                    // add the last message of the conversation
                    'content' => $this->conversation->getLastMessage()->content
                ],
            ],
            'functions' => $selectFunction,
            'function_call' => ["name" => "select_action"]
        ];

        // If theres only one available function, we don't need to query the AI about it
        if(count($selectFunction[0]['parameters']['properties']['action']['enum']) <= 1) {
            return $selectFunction[0]['parameters']['properties']['action']['enum'][0];
        }

        $response = OpenAI::chat()->create($params);

        Log::debug('Got result:');
        Log::debug((array) $response->choices[0]->message);

        // Handle the response
        $responseMessage = $response->choices[0]->message;
        $arguments = json_decode($responseMessage->functionCall->arguments);
        Log::debug('desired action: ' . $arguments->action);

        return $arguments->action;
    }

    /**
     * Method to prepare the messages for the AI. Converts all messages to an array and appends the system prompt
     */
    private function prepareMessages() {
        foreach($this->conversation->getMessages() as $message) {
            $GPTmessage = [
                'role'      => $message->role,
                'content'   => $message->content
            ];

            if($message->role === 'function') {
                $GPTMessage['name'] = $message->name;
            }

            $messages[] = $GPTmessage;
        }

        if($this->systemPrompt != '') {
            $messages[] = [
                'role'      => 'system',
                'content'   => $this->systemPrompt
            ];
        }

        return $messages;
    }

    /**
     * Method to contact OpenAI and ask for a response
     *
     * @param $message Message: The message to send to the AI
     */
    public function ask($message = null): Message {
        try {
            Log::debug('Calling AI...');

            if($message != null) {
                $this->conversation->addMessage($message);
            }

            $messages = $this->prepareMessages();
            $params = [
                'model' => $this->model,
                'messages' => $messages,
                'max_tokens' => 3000
            ];

            if($this->useFunctions == true) {
                $params['functions'] = $this->functions;
                $params['function_call'] = $this->functionCall;
            }

            Log::debug('Conversation (' . $this->conversation->getConversationLength() . ' messages):');
            foreach($messages as $m) {
                if(is_array($m['content']) || is_object($m['content'])) {
                    $content = json_encode($m['content']);
                } else {
                    $content = $m['content'];
                }
                Log::debug($m['role'] . ' => ' . $content);
            }

            $AIResponse = OpenAI::chat()->create($params);

            // Now that we have a successful response, we store the user Message
            try {
                $message = $this->conversation->getLastMessage();
                if(is_array($message->content) || is_object($message->content)) {
                    $content = json_encode($message->content);
                } else {
                    $content = $message->content;
                }
                Log::debug('Storing last message: ' . $content . ' | ' . $message->functionCall);
                $message->save();
            } catch(\Exception $e) {
                Log::debug('Error saving message: ' . $e->getMessage());
                Log::debug((array) $message->toArray());
                throw $e;
            }

            return $this->handleAiResponse($AIResponse->choices[0]->message);

        } catch (\Exception $e) {
            Log::debug('ChatGPT->ask: Error calling AI: ' . $e->getMessage());
            Log::debug($e->getTraceAsString());
            throw $e;
        }
    }

    /**
    * General method to handle the response from the AI
    */
    public function handleAIResponse($response) {
        Log::debug('ChatGPT::handleAIResponse()');
        Log::debug((array) $response);

        if(isset($response->functionCall)) {

            $functionName = $response->functionCall->name;
            $functionArgs = json_decode($response->functionCall->arguments);

            Log::debug('Got function call from AI: ' . $functionName);

            // if default_handler, it's only about formatting the answer
            if($functionName == 'default_handler') {
               return OpenAiFunctions::default_handler($response);
            }

            // if its another function call, we need to do more

            // 1) Save the message from the AI, which contains the function call
            $functionCallMessage = new Message();
            $functionCallMessage->role = $response->role;
            $functionCallMessage->content = '';
            $functionCallMessage->functionCall = $response->functionCall;
            $functionCallMessage->housing_id = $this->housingId;
            $functionCallMessage->senderId = 1;

            Log::debug('Storing message we received from AI');
            Log::debug((array) $functionCallMessage->toArray());

            $functionCallMessage->save();

            // ... and add it to the converstation
            $this->conversation->addMessage($functionCallMessage);

            // 3) Now we actually execute the function
            Log::debug('Now executing function: ' . $functionName);
            $functionResult = OpenAiFunctions::{$functionName}($functionArgs);
            Log::debug('Result of function ' . $functionName . ' : ');
            Log::debug((array) $functionResult);

            // Now we decide, if the function resturn value should be sent to the AI again or not
            if($functionResult['continue'] === false) {
                Log::debug('Function ' . $functionName . ' returned continue = false, so we stop here');
                return $functionResult['payload'];
            }

            // 4) Create a new message containing the function result which we can send again to the AI
            $message = new Message;
            $message->role = 'function';
            $message->name = $functionName;
            $message->content = json_encode($functionResult);
            $message->housing_id = $this->housingId;
            $message->senderId = 1;

            Log::debug('Created message for AI:');
            Log::debug((array) $message->toArray());

            $message->save();

            $this->conversation->addMessage($message);

            // 5) Now we can send the message to the AI again
            $this->setFunctionCall('default_handler');
            return $this->ask();

        } else {

            // Return the message without calling a function
            $message = new Message;
            $message->role = 'assistant';
            $message->content = $response->content;
            $message->senderId = 1;
            $message->housing_id = $this->housingId;
        }

        return $message;
    }
}
