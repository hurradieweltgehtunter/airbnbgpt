<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AvailableAgent;
use App\Models\Agent;
use App\Models\Housing;
use App\Models\GPTModel;
use App\Factories\AgentFactory;

use App\Http\Resources\AvailableAgentResource;
use App\Http\Resources\AvailableAgentIndexResource;
use App\Http\Resources\GPTModelResource;

use Inertia\Inertia;

class AvailableAgentController extends Controller
{
    public function index()
    {
        $availableAgents = AvailableAgentIndexResource::collection(AvailableAgent::get());
        return Inertia::render('Agents', ['section' => 'index', 'availableagents' => $availableAgents]);
    }

    /**
     * Show a single model
     */
    public function show(Request $request, $agentId)
    {
        $agent = AvailableAgent::findOrFail($agentId);
        $agent->load('gptmodel');
        return Inertia::render('Agents', [
            'section' => 'show',
            'agent' => new AvailableAgentResource($agent),
            'available_models' => GPTModelResource::collection(GPTModel::all())
        ]);
    }

    /**
     * Update the model
     */
    public function update(Request $request, AvailableAgent $availableagent)
    {
        $availableagent->update($request->all());
        return response()->noContent();
    }
}
