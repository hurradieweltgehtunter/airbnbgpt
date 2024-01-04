<?php

namespace App\Http\Controllers;

use App\Factories\AgentFactory;
use App\Http\Resources\AgentResource;
use App\Models\Agent;
use App\Models\Housing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AgentController extends Controller
{
    public function index()
    {
        $agents = Agent::with('messages')->get();
        return Inertia::render('Agents', ['section' => 'index', 'agents' => $agents]);
    }

    /**
     * Creates a new agent
     */
    public function store(Request $request, Housing $housing)
    {
        // validate request, it must contain a name
        $request->validate([
            'name' => 'required',
        ]);

        // Check, if an agent with the given name already exists to this housing
        $existingAgent = Agent::where('name', $request->input('name'))->where('agentable_id', $housing->id)->first();

        // If thje agent already exists, return it
        if($existingAgent) {
            return new AgentResource($existingAgent);
        }

        $agent = Agent::CreateFromName($request->input('name'), $housing);

        return new AgentResource($agent);
    }

    public function show(Request $request, $agentId)
    {
        $agent = Agent::with('messages')->findOrFail($id);
        return response()->json($agent);
    }

    public function update(Request $request, $id)
    {
        $agent = Agent::findOrFail($id);
        $agent->update($request->all());
        return response()->json($agent);
    }

    public function destroy($id)
    {
        Agent::destroy($id);
        return response()->json(null, 204);
    }

    /**
     * Method to run a specific agent
     *
     * @param Request $request
     * @param Agent $agent
     * @return mixed
     */
    public function runAgent(Request $request, $agent)
    {
        $data = request()->all();

        if($agent->has_finished === true) {
            return $agent->finished();
        }

        try {
            return $agent->run($data);
        } catch(\GuzzleHttp\Exception\ConnectException $e)
        {
            return response()->json(['error' => 'Timeout in OpenAI API request: ' . $e->getMessage()], 408);
        } catch(\Exception $e)
        {
            return response()->json(['error' => 'Error in OpenAI API request: ' . $e->getMessage()], 500);
        }
    }
}
