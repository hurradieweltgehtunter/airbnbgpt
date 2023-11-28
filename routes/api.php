<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\MessageController;
use App\Http\Controllers\HousingController;
use App\Http\Controllers\HousingRoomController;
use App\Http\Controllers\HousingImageController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\AvailableAgentController;
use App\Http\Controllers\WritingStyleController;
use App\Http\Controllers\WritingStyleExampleController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->controller(MessageController::class)->group(function () {
    Route::get('/messages/next', 'next');
    // Route::get('/messages/{id}', 'show');
    Route::post('/messages', 'store2');
});

// HOUSINGS ROUTES

Route::prefix('housings')->name('housing.')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [HousingController::class, 'index'])->name('index'); // Liste aller Räume
    Route::post('/', [HousingController::class, 'store'])->name('store'); // Neuen Raum speichern
    // Route::get('/{housing}', [HousingController::class, 'show'])->name('show'); // Einzelnen Raum anzeigen
    Route::put('/{id}', [HousingController::class, 'update'])->name('update'); // Raumänderungen speichern
    Route::delete('/{housing}', [HousingController::class, 'destroy'])->name('destroy'); // Einen Raum löschen

    Route::post('/{id}/address', [HousingController::class, 'storeAddress'])->name('storeAddress'); // Adresse speichern
    Route::get('/{id}/describeimage', [HousingController::class, 'describeImage'])->name('describeImage'); // Bild beschreiben

    Route::get('/{housing}/analyzeimages', [HousingController::class, 'analyzeImages'])->name('analyzeImages');

    // IMAGES
    Route::get('/{id}/images', [HousingImageController::class, 'index'])->name('index'); // Show all images to room
    Route::delete('/{housing}/images/{housingImage}', [HousingImageController::class, 'destroy'])->name('destroy');
    Route::post('/{housing}/images', [HousingImageController::class, 'store'])->name('store'); // Store new images to housing
    Route::put('/{housing}/images/{housingImage}', [HousingImageController::class, 'update'])->name('update'); // Update image

    // AGENTS
    Route::post('/{housing}/createagent', [AgentController::class, 'store'])->name('store');
    Route::get('/{housing}/agents/{agent}/run', [AgentController::class, 'run'])->name('run');

    // ROOMS
    Route::patch('/{housing}/rooms', [HousingRoomController::class, 'create'])->name('create');
});

Route::prefix('housingimages')->name('housingimages.')->middleware(['auth', 'verified'])->group(function () {
    // Route::get('/{id}/images', [HousingImageController::class, 'index'])->name('index'); // Show all images to room
    // Route::post('/{id}/images', [HousingImageController::class, 'store'])->name('store'); // Store new image to room
    // Route::put('/{housing}/images/{housingImage}', [HousingImageController::class, 'update'])->name('update'); // Store new image to room

});

Route::prefix('writingstyles')->name('writingstyles.')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [WritingStyleController::class, 'index'])->name('index'); // Show all writing styles to the user
    Route::post('/', [WritingStyleController::class, 'store'])->name('store'); // Store new WritingStyle
    Route::put('/{writingStyle}', [WritingStyleController::class, 'update'])->name('update'); // Update writingStyle
    Route::delete('/{writingStyle}', [WritingStyleController::class, 'destroy'])->name('destroy');

    Route::post('/{writingStyle}/examples', [WritingStyleExampleController::class, 'store'])->name('store'); // Store new WritingStyleExample

    // AGENTS
    Route::get('/{writingStyle}/createagent', [WritingStyleController::class, 'createAgent'])->name('createAgent');

});

Route::prefix('agents')->name('agents.')->middleware(['auth', 'verified'])->group(function () {
    Route::post('/{agent}/run', [AgentController::class, 'runAgent'])->name('runAgent');
    Route::get('/{agent}/run', [AgentController::class, 'runAgent'])->name('runAgent');
    Route::post('/{agentId}/messages', [MessageController::class, 'store'])->name('store');
});


Route::prefix('availableagents')->name('availableagents.')->middleware(['auth', 'verified'])->group(function () {
    Route::put('/{agentId}', [AvailableAgentController::class, 'update'])->name('update');
});
