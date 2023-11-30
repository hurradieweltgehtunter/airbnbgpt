<?php

namespace App\Http\Controllers;

use App\Models\WritingStyleExample;
use Illuminate\Http\Request;
use App\Models\WritingStyle;
use App\Models\Agent;

class WritingStyleExampleController extends Controller
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
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, WritingStyle $writingStyle)
    {
        $validated = $request->validate([
            'content' => 'required|string'
        ]);

        $writingStyleExample = WritingStyleExample::create([
            'writing_style_id' => $writingStyle->id,
            'content' => $validated['content']
        ]);

        // return the writingStyleExample
        return response()->json($writingStyleExample);

    }

    /**
     * Display the specified resource.
     */
    public function show(WritingStyleExample $writingStyleExample)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function analyze (Request $request)
    {
        // Validate input. content must be set, not empty, a string and at minimum 500 cahracter long
        $validated = $request->validate([
            'content' => 'required|string|min:500'
        ]);

        // Create a new wrtigin style analyzer agent
        $agent = Agent::CreateFromName('WritingStyleAnalyzerAgent');
        $writingStyle = $agent->run($validated['content']);

        return response()->json($writingStyle);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WritingStyleExample $writingStyleExample)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, WritingStyleExample $writingStyleExample)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WritingStyleExample $writingStyleExample)
    {
        //
    }
}
