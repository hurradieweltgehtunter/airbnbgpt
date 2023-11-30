<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Resources\HousingIndexResource;
use App\Http\Resources\WritingStyleIndexResource;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Dashboard', [
            'is_admin' => Auth::user()->is_admin,
            'housings' => HousingIndexResource::collection(
                auth()
                ->user()
                ->housings()
                ->orderBy('id', 'desc')
                ->get()
            ),
            'writingStyles' => WritingStyleIndexResource::collection(
                auth()
                ->user()
                ->writingStyles()
                ->orderBy('title', 'asc')
                ->get()
            )
        ]);
    }
}
