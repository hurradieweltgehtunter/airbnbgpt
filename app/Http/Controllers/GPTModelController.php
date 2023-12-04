<?php

namespace App\Http\Controllers;

use App\Models\GPTModel;
use Illuminate\Http\Request;

class GPTModelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Show all models
        $models = GPTModel::with('usages')->get();
        return response()->json($models);
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(GPTModel $gptmodel)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GPTModel $gptmodel)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GPTModel $gptmodel)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GPTModel $gptmodel)
    {
        //
    }
}
