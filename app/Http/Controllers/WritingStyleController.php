<?php

namespace App\Http\Controllers;

use App\Models\WritingStyle;
use Illuminate\Http\Request;
use App\Models\Agent;
use Inertia\Inertia;
use App\Http\Resources\AgentResource;
use Illuminate\Support\Facades\DB;

class WritingStyleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get all writing styles to the current user
        $writingStyles = WritingStyle::where('user_id', auth()->user()->id)->get();

        // return a JSON response
        return response()->json($writingStyles);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('WritingStyle', ['section' => 'create']);
    }

    public function createAgent(Request $request, WritingStyle $writingStyle)
    {
        $agent = Agent::CreateFromName('WritingStyleAnalyzer', $writingStyle);

        // return AgentResource
        return new AgentResource($agent);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $writingStyle = WritingStyle::createForUser(auth()->user()->id, $request->all());

        $writingStyle->fill($request->all());

        return response()->json($writingStyle);
    }

    /**
     * Display the specified resource.
     */
    public function show(WritingStyle $writingStyle)
    {
        return Inertia::render('WritingStyle', ['section' => 'show', 'writingStyle' => $writingStyle]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WritingStyle $writingStyle)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, WritingStyle $writingStyle)
    {
        $data = $request->all();

        $writingStyle->title = $data['title'];
        $writingStyle->description = $data['description'];

        $writingStyle->save();
        $writingStyle->refresh();

        return response()->json($writingStyle);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WritingStyle $writingStyle)
    {
        $writingStyle->delete();

        return response()->json(null, 204);

    }

    public function runAgent(WritingStyle $writingStyle, Agent $agent) {
        $response = $agent->run();

        return response()->json($response);
    }
}
