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

Route::prefix('housings')->name('housing.')->middleware(['auth', 'verified'])->group(function () {
    Route::post('/{housing}/images', [HousingImageController::class, 'store'])->name('storeImage'); // Store new image to housing

    // AGENTS
    Route::post('/{housing}/createagent', [AgentController::class, 'store'])->name('storeAgent');
    Route::get('/{housing}/agents/{agent}/run', [AgentController::class, 'run'])->name('runAgent');
});

Route::prefix('images')->name('housingimages.')->middleware(['auth', 'verified', 'owns-subresource:App\Models\HousingImage,App\Models\Housing,belongs_to'])->group(function () {
    Route::put('/{housingImage}', [HousingImageController::class, 'update'])->name('update'); // Update image
    Route::delete('/{housingImage}', [HousingImageController::class, 'destroy'])->name('destroy');
});

Route::prefix('agents')->name('agents.')->middleware(['auth', 'verified'])->group(function () {
    Route::post('/{agent}/run', [AgentController::class, 'runAgent'])->name('runAgentPOST');
    Route::get('/{agent}/run', [AgentController::class, 'runAgent'])->name('runAgent');
    Route::post('/{agentId}/messages', [MessageController::class, 'store'])->name('store');
});


// Route::prefix('availableagents')->name('availableagents.')->middleware(['auth', 'verified'])->group(function () {
//     Route::put('/{agentId}', [AvailableAgentController::class, 'update'])->name('update');
// });
