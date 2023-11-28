<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\HousingController;
use App\Http\Controllers\WritingStyleController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\AvailableAgentController;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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

    // return Inertia::render('Welcome', [
    //     'canLogin' => Route::has('login'),
    //     'canRegister' => Route::has('register'),
    //     'laravelVersion' => Application::VERSION,
    //     'phpVersion' => PHP_VERSION,
    // ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard', [
        'is_admin' => Auth::user()->is_admin
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/app', function () {
    return Inertia::render('App');
})->name('app');

Route::middleware(['auth', 'verified'])->group(function () {
    // Forward to dashboard
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });
});

// Route-Gruppe für Housing-Ressourcen
Route::prefix('housings')->name('housings.')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [HousingController::class, 'index'])->name('index'); // Liste aller Räume
    Route::get('/create', [HousingController::class, 'create'])->name('create'); // Formular zum Erstellen eines neuen Raumes
    Route::post('/', [HousingController::class, 'store'])->name('store'); // Neuen Raum speichern

    Route::get('/{housing}', [HousingController::class, 'show'])->name('show'); // Einzelnen Raum anzeigen
    Route::get('/{housing}/edit', [HousingController::class, 'edit'])->name('edit'); // Leitet auf den entsprechenden Schritt in der Bearbeitung weiter
    Route::get('/{housing}/images', [HousingController::class, 'images'])->name('images'); // Formular zum Bearbeiten eines Raumes
    Route::get('/{housing}/questionnaire', [HousingController::class, 'showQuestionnaire'])->name('showQuestionnaire'); // Formular zum Bearbeiten eines Raumes
    Route::get('/{housing}/editRooms', [HousingController::class, 'editRooms'])->name('editRooms'); // Formular zum Bearbeiten eines Raumes
    Route::get('/{housing}/style', [HousingController::class, 'writingstyleSelect'])->name('writingstyleSelect'); // Formular zum Bearbeiten eines Raumes
    Route::get('/{housing}/prepare/{writingStyle}', [HousingController::class, 'prepare'])->name('prepare'); // Alles vorbereiten zum Texte generieren
    Route::get('/{housingId}/run', [HousingController::class, 'run'])->name('run'); // Texte generieren
    // Route::get('/{housing}/write/{writingStyle}', [HousingController::class, 'write'])->name('write'); // Erstellt die Texte

    Route::put('/{housing}', [HousingController::class, 'update'])->name('update'); // Raumänderungen speichern
    Route::delete('/{housing}', [HousingController::class, 'destroy'])->name('destroy'); // Einen Raum löschen
});

Route::prefix('styles')->name('styles.')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [WritingStyleController::class, 'index'])->name('index'); // Liste aller Räume
    Route::get('/create', [WritingStyleController::class, 'create'])->name('create'); // Formular zum Erstellen eines neuen Raumes
    Route::post('/create', [WritingStyleController::class, 'store'])->name('store'); // Neuen Raum speichern

    Route::get('/{writingStyle}', [WritingStyleController::class, 'show'])->name('show'); // Einzelnen Raum anzeigen

    Route::put('/{id}', [WritingStyleController::class, 'update'])->name('update'); // Raumänderungen speichern
    Route::delete('/{id}', [WritingStyleController::class, 'destroy'])->name('destroy'); // Einen Raum löschen
});

Route::post('/users', [UsersController::class, 'create'])->name('users.create');
Route::get('/users', [UsersController::class, 'create'])->name('users.create');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ADMIN

// Agents
Route::prefix('availableagents')->name('availableagents.')->middleware(['auth', 'verified', 'isAdmin'])->group(function () {
    Route::get('/', [AvailableAgentController::class, 'index'])->name('index'); // Liste aller Räume
    Route::get('/{agentId}', [AvailableAgentController::class, 'show'])->name('show'); // Listet einen Raum
});

require __DIR__.'/auth.php';
