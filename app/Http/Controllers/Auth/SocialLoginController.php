<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use App\Providers\RouteServiceProvider;

class SocialLoginController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        $user = Socialite::driver('google')->user();

        $user = User::firstOrCreate(
            ['email' => $user->email],
            [
            'name' => $user->name,
            'email' => $user->email,
            'email_verified_at' => now(),
            'avatar' => $user->avatar,
        ]);

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }
}
