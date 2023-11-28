<?php

namespace App\Http\Controllers;

use App\Models\Housing;
use App\Models\HousingRoom;
use App\Models\HousingImage;
use Illuminate\Http\Request;

class HousingRoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Create a room for each image and attach the image to it
     */
    public function create(Request $request, Housing $housing)
    {
        $uniqueLabels = HousingImage::where('housing_id', $housing->id)->distinct()->pluck('label');

        // 1) Check, if there is already a room with the same name. If so, remove it from the uniqueLabels
        $uniqueLabels = $uniqueLabels->filter(function ($label) use ($housing) {
            return !$housing->rooms()->where('name', $label)->exists();
        });

        // Schritt 2: Erstellen von Räumen für jedes einzigartige Label
        $rooms = $uniqueLabels->mapWithKeys(function ($label) use ($housing) {
            $room = $housing->rooms()->create(['name' => $label]);
            return [$label => $room->id];
        });

        // Schritt 3: Jedes Bild, das zum Housing gehört und das entsprechende label hat, mit room_id aktualisieren
        HousingImage::where('housing_id', $housing->id)->get()->each(function ($image) use ($rooms) {
            if (isset($rooms[$image->label])) {
                $image->room_id = $rooms[$image->label];
                $image->save();
            }
        });

        // return all rooms to $housing. Include rooms' images
        $housing->refresh();
        $housing->load('rooms.images');

        return response()->json($housing->rooms, 201);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Housing $housing)
    {
        $room = new HousingRoom();
        $room->name = $request->input('name');
        $room->housing_id = $housing->id;

        $room->save();

        return response()->json($room, 201);

    }

    /**
     * Display the specified resource.
     */
    public function show(HousingRoom $housingRoom)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(HousingRoom $housingRoom)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, HousingRoom $housingRoom)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HousingRoom $housingRoom)
    {
        //
    }
}
