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
use App\Models\AgentUsage;

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
    public $tools;
    public $tool_choice;
    public $use_tools;
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
        $this->tools = $availableAgent->tools;
        $this->use_tools = $availableAgent->use_tools;
        $this->tool_choice = $availableAgent->tool_choice ? ["type" => "function", "function" => ["name" => $availableAgent->tool_choice]] : 'none';
        $this->fake_responses = $availableAgent->fake_responses;
        $this->fake_enabled = $availableAgent->fake_enabled;

        // Init the conversation
        $this->conversation = new Conversation();

        // Get the initial message from available_agents
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
    public static function createFromName($agentName, $entity = null, $parameters = [])
    {
        if ($entity) {
            $existingAgent = $entity->agents()->where('name', $agentName)->first();
            if($existingAgent) {
                $existingAgent = AgentFactory::load($existingAgent->id);
                $existingAgent->initRuntime();
                return $existingAgent;
            }

            $agent = AgentFactory::createNew($agentName, $entity);
            $agent->parameters = $parameters;

            $agent = $entity->agents()->save($agent);
        } else {
            $agent = AgentFactory::createNew($agentName, $entity);
            $agent->parameters = $parameters;
        }

        // Check if init() exists on $agent
        if (method_exists($agent, 'init'))
        {
            $agent->init();
        }

        if ($entity) {
            $agent->refresh();
        }

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

        if($this->use_tools == true) {
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

            $agentUsage = AgentUsage::create($this, $AIResponse);
            $agentUsage->save();

            Log::debug('Agent::run: received AI message for agent ' . get_class($this));
            Log::debug((array) $AIResponse);

            return [$AIResponse->choices[0]->message, $agentUsage];
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

            if($message->role === 'tool') {
                $GPTMessage['tool_call_id'] = $message->name; // ToDo: Set the message id thhis message is the answer to: https://platform.openai.com/docs/api-reference/chat/create#chat-create-messages
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

        return $fakeResponse;
    }

    /**
     * Method to fix Umlauts in AI responses. GPT4-1106 model has some issues. As long they are not solved, we try to fix the umlauts here.
     */
    public function fixUmlauts($message) {
        if(is_array($message)) {
            foreach($message as $key => $value) {
                $message[$key] = $this->fixUmlauts($value);
            }

            return $message;
        }

        $message = str_replace('"a', 'ä', $message);
        $message = str_replace('"o', 'ö', $message);
        $message = str_replace('"u', 'ü', $message);
        $message = json_decode('"' . $message . '"');
        return $message;
    }
}
