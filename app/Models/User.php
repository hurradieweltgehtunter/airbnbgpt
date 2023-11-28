<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'is_admin'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean',
    ];

    public function index()
    {
        return Inertia::render('Users/Index', [
          'users' => User::all(),
        ]);
    }

    public function store(Request $request)
    {
        User::create($request->validate([
          'first_name' => ['required', 'max:50'],
          'last_name' => ['required', 'max:50'],
          'email' => ['required', 'max:50', 'email'],
        ]));

        return to_route('users.index');
    }

    // Ein Benutzer kann viele Räume haben
    public function housings()
    {
        return $this->hasMany(Housing::class, 'belongs_to');
    }

    // Ein Benutzer kann viele WritingStyles haben
    public function writingStyles()
    {
        return $this->hasMany(WritingStyle::class);
    }

    // Ein Benutzer kann viele Nachrichten gesendet haben
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'senderId');
    }
}
