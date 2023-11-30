<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\HousingController;
use App\Http\Controllers\WritingStyleController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\AvailableAgentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HousingRoomController;
use App\Http\Controllers\HousingImageController;
use App\Http\Controllers\WritingStyleExampleController;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\Agent;

use App\Http\Resources\HousingIndexResource;
use App\Http\Resources\WritingStyleIndexResource;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    // Forward to login
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Forward to dashboard
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });
});

Route::prefix('dashboard')->name('dashboard')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [DashboardController::class, 'index']);
});

Route::get('/scrape-airbnb', function () {
    $process = new Process(['node', base_path('resources/js/Scraper/scraper.js')]);
    $process->run();

    if (!$process->isSuccessful()) {
        throw new ProcessFailedException($process);
    }

    echo '<textarea>';
    print_r($process->getOutput());
    echo '</textarea>';

    // get housing wiht id 1
    $housing = \App\Models\Housing::find(1);

    $agent = Agent::createFromName('AirbnbScraperAgent', $housing);
    $agent->run('asd');

});


// HOUSING CRUD Operations
Route::resource('housings', HousingController::class)
    ->middleware(['auth', 'verified', 'owns-resource:App\Models\Housing,belongs_to']);

// HOUSING Custom Routes
Route::prefix('housings')->name('housings.')->middleware(['auth', 'verified', 'owns-resource:App\Models\Housing,belongs_to'])->group(function () {
    Route::get('/{housing}/images', [HousingController::class, 'images'])->name('images'); // Formular zum Bearbeiten eines Raumes
    Route::get('/{housing}/questionnaire', [HousingController::class, 'showQuestionnaire'])->name('showQuestionnaire'); // Formular zum Bearbeiten eines Raumes
    Route::get('/{housing}/style', [HousingController::class, 'writingstyleSelect'])->name('writingstyleSelect'); // Formular zum Bearbeiten eines Raumes
    Route::get('/{housing}/prepare/{writingStyle}', [HousingController::class, 'prepare'])->name('prepare'); // Alles vorbereiten zum Texte generieren
    Route::get('/{housing}/run', [HousingController::class, 'run'])->name('run'); // Texte generieren

    // Create rooms from images
    Route::patch('/{housing}/rooms', [HousingRoomController::class, 'create'])->name('createRooms');
});

// HousingImages CRUD Operations
Route::resource('images', HousingImageController::class)
    ->middleware(['auth', 'verified', 'owns-subresource:App\Models\HousingImage,App\Models\Housing,belongs_to']);

// WRITINGSTYLE CRUD Operations
Route::resource('styles', WritingStyleController::class)
    ->except(['edit'])
    ->middleware(['auth', 'verified', 'owns-resource:App\Models\WritingStyle']);

// WRTITINGSTYLE Custom Routes
Route::prefix('styles')->name('styles.')->middleware(['auth', 'verified', 'owns-resource:App\Models\WritingStyle'])->group(function () {
    Route::post('/{writingStyle}/examples', [WritingStyleExampleController::class, 'store'])->name('storeExample');
    Route::post('/examples/analyze', [WritingStyleExampleController::class, 'analyze'])->name('analyzeExample');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ADMIN

// Available Agents
Route::resource('availableagents', AvailableAgentController::class)
    ->middleware(['auth', 'verified', 'isAdmin']);

require __DIR__.'/auth.php';
