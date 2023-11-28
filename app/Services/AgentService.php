<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\AvailableAgent;

class AgentService
{
    protected $availableAgent;

    public function __construct()
    {

    }

    /**
     * Creates a new agent of the given name
     */
    public function getAgentByName($agentName)
    {
        return AvailableAgent::where('name', $agentName)->firstOrFail();
    }
}
