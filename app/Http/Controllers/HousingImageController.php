<?php

namespace App\Http\Controllers;

use App\Models\Housing;
use App\Models\HousingImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class HousingImageController extends Controller
{
    /**
     * Stores a new image to the given housing
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $housing = Housing::findOrFail($data['housingId']);

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
        try {
            $request->validate([
                'file' => 'required|mimes:jpeg,png,jpg|max:4096', // Maximale Größe ist 2MB
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Es ist ein Fehler aufgetreten:' . $e->getMessage()
            ], 500);
        }

        $path = $request->file('file')->store('public'); // speichert die Datei im "uploads"-Verzeichnis im Storage
        $url = Storage::url($path);

        $image = new HousingImage();
        $image->housing_id = $housing->id;
        $image->path = url($url);

        $image->save();

        return response()->json($image->toArray());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, HousingImage $image)
    {
        foreach($request->all() as $key => $value) {
            $image->$key = $value;
        }

        try {
            $image->save();
        } catch(\Exception $e) {
            return response()->json([
                'message' => 'Es ist ein Fehler aufgetreten:' . $e->getMessage()
            ], 500);
        }

        // Send a 204 response
        return response()->noContent();

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Housing $housing, HousingImage $image)
    {
        // Load the room the image bleongs to
        $room = $image->room;

        // Delete the file in $image->path
        $path = str_replace(url('/'), '', $image->path);
        Storage::delete($path);

        $image->delete();

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
