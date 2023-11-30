<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Custom\Conversation;
use App\Services\AgentService;
use Illuminate\Support\Facades\Auth;
use OpenAI\Laravel\Facades\OpenAI;
use App\Factories\AgentFactory;
use App\Models\Message;
use Illuminate\Support\Facades\Log;
use OpenAI\Responses\Chat\CreateResponse;
use OpenAI\Testing\ClientFake;

class Agent extends Model
{
    use HasFactory;

    protected $table = 'agents';

    protected $fillable = [
        'name',
        'description',
        'parameters',
        'agentable_type',
        'agentable_id'
    ];

    public $conversation;

    // Properties, which will be taken from available Agent on runtime (initRuntime())
    public $system_prompt;
    public $model;
    public $functions;
    public $functionCall;
    public $use_functions;
    public $fake_responses;
    public $fake_enabled;

    protected $casts = [
        'functions' => 'array',
        'parameters' => 'array',
        'has_finished' => 'boolean',
    ];

    public function messages()
    {
        return $this->hasMany(Message::class, 'agent_id');
    }

    public function agentable()
    {
        return $this->morphTo();
    }

    // public function __construct($attributes = []) {
    //     parent::__construct($attributes);
    //     $this->initRuntime();
    // }

    // public function newFromBuilder($attributes = [], $connection = null) {
    //     if (!empty($attributes->name)) {
    //         $className = '\\App\\Agents\\' . $attributes->name . 'Agent';
    //         if (class_exists($className)) {
    //             $instance = new $className((array) [], $connection);
    //             $instance->setRawAttributes((array) $attributes, true);
    //             $instance->loadMessagesIntoConversation();
    //             $instance->initRuntime();
    //             return $instance;
    //         } else {
    //             throw new \Exception('Agent class not found: ' . $className);
    //         }
    //     }

    //     throw new \Exception('Agent class not found: ' . $attributes->name);

    //     return parent::newFromBuilder($attributes, $connection);
    // }

    protected static function booted() {
        static::deleting(function ($agent) {
            $agent->messages()->each(function($message) {
                $message->delete();
             });
        });
    }

    protected static function boot()
    {
        parent::boot();

        static::retrieved(function ($agent) {
            // Run initRuntime() only if the agent is a child class of Agent
            if ($agent instanceof Agent && get_class($agent) !== 'App\Models\Agent') {
                $agent->initRuntime();
            } else {
                // this is called when the base Agent::class is initiated
            }
        });
    }


    /**
     * Initializes the agent on runtime! This method gets called on every request
     * Contrary to init() which only gets called once on agent creation
     */
    public function initRuntime() {
        // Load some config params from available_agents
        $agentService = new AgentService();
        $availableAgent = $agentService->getAgentByName($this->name);
        $this->system_prompt = $availableAgent->system_prompt;
        $this->model = $availableAgent->model;
        $this->functions = $availableAgent->functions;
        $this->use_functions = $availableAgent->use_functions;
        $this->fake_responses = $availableAgent->fake_responses;
        $this->fake_enabled = $availableAgent->fake_enabled;

        // Init the conversation
        $this->conversation = new Conversation();

        // Get the initial message from available_agents
        $agentService = new AgentService();
        $availableAgent = $agentService->getAgentByName($this->name);

        if (!$availableAgent) {
            throw new Exception("Agent not found.");
        }

        if (isset($availableAgent->initial_message)) {
            $message = $this->addMessage($availableAgent->initial_message);
        } else {
            throw new \Exception('Agent::initRuntime: No initial message found to agent ' . $this->name);
        }

        // Load all messages from DB into the conversation
        $this->loadMessagesIntoConversation();
    }

    /**
     * Method to load all messages from DB into the conversation
     */
    public function loadMessagesIntoConversation() {
        foreach ($this->messages as $message) {
            $this->addMessage($message);
        }
    }

    /**
     * Method to create a new agent from the given name by looking it up in avaliable_agents
     * @param $agentName Name of the agent to create
     * @param $entity Entity to which the agent belongs (Housing, WritingStyle, ...)
     */
    public static function createFromName($agentName, $entity, $parameters = [])
    {
        $existingAgent = $entity->agents()->where('name', $agentName)->first();
        if($existingAgent) {
            $existingAgent = AgentFactory::load($existingAgent->id);
            $existingAgent->initRuntime();
            return $existingAgent;
        }

        $agent = AgentFactory::createNew($agentName, $entity);
        $agent->parameters = $parameters;

        $agent = $entity->agents()->save($agent);

        // Check if init() exists on $agent
        if (method_exists($agent, 'init'))
        {
            $agent->init();
        }

        $agent->refresh();
        $agent->initRuntime();

        return $agent;
    }

    /**
     * Method to add a new message to the converation
     */
    public function addMessage($message) {
        if(is_array($message)) {
            // Message can have the following attributes: name, role, content, function_call. Create a new entry in messages setting these attributes
            $newMessage = new Message();
            $newMessage->role = $message['role'];
            $newMessage->content = $message['content'] ?? null;

            if(isset($message['name']) && $message['name'] !== '')
                $newMessage->name = $message['name'];

            if(isset($message['function_call']) && $message['function_call'] !== '')
                $newMessage->function_call = $message['function_call'];

            if(isset($message['functionCall']) && $message['functionCall'] !== '')
                $newMessage->function_call = $message['functionCall'];

        } else {
            $newMessage = $message;
        }

        $newMessage->agent_id = $this->id;

        // Set the sender_id
        if($newMessage->role === 'user')
            $newMessage->sender_id = Auth::user()->id;
        else
            $newMessage->sender_id = 1;

        $this->conversation->addMessage($newMessage);

        return $newMessage;
    }

    /**
     * Executes a query to ChatGPT and returns the response
     */
    public function run($data = null) {
        $params = [
            'model' => $this->model,
            'messages' => $this->prepareMessages(),
            'max_tokens' => 1000
        ];

        if($this->use_functions == true) {
            // $params['functions'] = $this->functions;
            // $params['function_call'] = $this->functionCall;

            $params['tools'] = $this->tools;
            $params['tool_choice'] = $this->tool_choice;
        }

        try {
            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/AIRequest.log'),
              ])->info(print_r($params, true));

            if($this->fake_enabled == true) {

                $fakeResponse = $this->getFakeResponse();

                OpenAI::fake([
                    CreateResponse::fake($fakeResponse),
                ]);
            }

            $AIResponse = OpenAI::chat()->create($params);

            Log::debug('Agent::run: received AI message');
            Log::debug((array) $AIResponse);

            return $AIResponse->choices[0]->message;
        } catch (\Exception $e) {
            Log::debug('Agent::run: ' . $e->getMessage());
            Log::debug((array) $params);
            throw $e;
        }
    }

    /**
     * Method to prepare the messages for the AI. Converts all messages to an array and appends the system prompt
     */
    private function prepareMessages() {
        $messages = [];

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

        // If a system prompt is set, append it to the messages
        if($this->system_prompt != '') {
            $messages[] = [
                'role'      => 'system',
                'content'   => $this->system_prompt
            ];
        }

        return $messages;
    }

    /**
     * Returns a fake but valid response to reduce AI API costs
     */
    private function getFakeResponse() {
        $fakeResponse = $this->fake_responses[$this->model];

        if(isset($fakeResponse['choices'][0]['message']['function_call'])) {
            $fakeResponse['choices'][0]['message']['function_call']['arguments'] = json_encode($fakeResponse['choices'][0]['message']['function_call']['arguments']);
        }


        return $fakeResponse;
    }

    public function finished() {
        return true;
    }

    public function fixUmlauts($message) {
        $message = str_replace('"a', 'ä', $message);
        $message = str_replace('"o', 'ö', $message);
        $message = str_replace('"u', 'ü', $message);
        $message = json_decode('"' . $message . '"');
        return $message;
    }
}
