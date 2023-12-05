<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    /**
     * Activate maintenance mode
     */
    public function down() {
        Artisan::call('down', [
            '--secret' => env('ADMIN_SECRET')
        ]);
    }

    /**
     * Deactivate maintenance mode
     */
    public function up() {
        Artisan::call('up');
    }

    /**
     * Run migrations
     */
    public function migrate() {
        Artisan::call('migrate');
        $migrateOutput = Artisan::output();
        Log::info("Migration Output: " . $migrateOutput);

        Artisan::call('db:seed', ['--class' => 'GptModelsTableSeeder']);
        $seedOutput = Artisan::output();
        Log::info("Seeder Output: " . $seedOutput);
    }
}
