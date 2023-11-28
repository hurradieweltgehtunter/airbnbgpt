<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AvailableAgent;
use App\Models\Agent;
use App\Models\Housing;
use App\Factories\AgentFactory;

use App\Http\Resources\AvailableAgentResource;

use Inertia\Inertia;

class AvailableAgentController extends Controller
{
    public function index()
    {
        $agents = AvailableAgent::get();
        return Inertia::render('Agents', ['section' => 'index', 'agents' => $agents]);
    }

    public function show(Request $request, $agentId)
    {
        $agent = AvailableAgent::findOrFail($agentId);

        return Inertia::render('Agents', ['section' => 'show', 'agent' => new AvailableAgentResource($agent)]);
    }

    public function update(Request $request, $id)
    {
        $agent = AvailableAgent::findOrFail($id);
        $agent->update($request->all());
        return response()->json($agent);
    }
}
