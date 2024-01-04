<?php

namespace App\Interfaces;

use Illuminate\Http\JsonResponse;

interface AgentInterface
{
    /**
     * Initializes the agent. This method gets only called on agent
     * creation (Agent::createFromName)
     */
    public function init();

    /**
     * Runs the agent. Returns mixed data, depending on the agent

     * @param mixed $data
     */
    public function run(array $data = null);

    /**
     * Method which handles the finished state of the agent.
     * Gets called, if the agent is closed (has_finished = true)
     *
     */
    public function finished();
}
