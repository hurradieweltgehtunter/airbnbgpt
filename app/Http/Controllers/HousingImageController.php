<?php

namespace App\Http\Controllers;

use App\Models\Housing;
use App\Models\HousingImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HousingImageController extends Controller
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
     * Stores a new image to the given housing
     */
    public function store(Request $request, Housing $housing)
    {
        // Get the number of already existing images
        $imageCount = count($housing->images);

        // if the image count is already 10, return an error
        if($imageCount >= 10) {
            return response()->json([
                'message' => 'Es können maximal 10 Bilder pro Unterkunft hochgeladen werden.'
            ], 422);
        }

        $mime = $request->file('file')->getMimeType();

        // Validieren Sie den Antrag
        $request->validate([
            'file' => 'required|mimes:jpeg,png,jpg,svg,pdf|max:2048', // Maximale Größe ist 2MB
            // 'housingId' => 'required|integer|exists:rousings,id',
        ]);

        $path = $request->file('file')->store('public'); // speichert die Datei im "uploads"-Verzeichnis im Storage
        $url = Storage::url($path);

        $image = new HousingImage();
        $image->housing_id = $housing->id;
        $image->path = url($url);

        $image->save();

        return response()->json($image->toArray());
    }

    /**
     * Display the specified resource.
     */
    public function show(HousingImage $housingImage)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(HousingImage $housingImage)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Housing $housing, HousingImage $housingImage)
    {
        foreach($request->all() as $key => $value) {
            $housingImage->$key = $value;
        }

        try {
            $housingImage->save();
        } catch(\Exception $e) {
            return response()->json([
                'message' => 'Es ist ein Fehler aufgetreten.'
            ], 500);
        }

        // Send a 204 response
        return response()->noContent();

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Housing $housing, HousingImage $housingImage)
    {
        // Load the room the image bleongs to
        $room = $housingImage->room;

        // Delete the file in $housingImage->path
        $path = str_replace(url('/'), '', $housingImage->path);
        Storage::delete($path);

        $housingImage->delete();

        if($room == null) {
            return response()->noContent();
        }

        // If no image in room is left, delete the room
        // TODO: Move this into a observer image deleting event
        $room->refresh();

        if(count($room->images) == 0) {
            $room->delete();
        }

        return response()->noContent();
    }
}
