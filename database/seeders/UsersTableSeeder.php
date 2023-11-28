<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'id' => 1,
            'name' => 'AirBnB-GPT',
            'email' => 'default@example.com',
            'password' => Hash::make(Str::random(10)),
            'avatar' => '/images/bot-avatar.jpg',
            'email_verified_at' => now(),
        ]);

        DB::table('users')->insert([
            'id' => 2,
            'name' => 'Test',
            'email' => 'test@test.de',
            'password' => Hash::make('testtest'),
            'email_verified_at' => now(),
            'is_admin' => true,
        ]);

        DB::table('users')->insert([
            'id' => 3,
            'name' => 'Mathias Rudolph',
            'email' => 'mathias.rudolph@gmail.com',
            'password' => Hash::make('mathias'),
            'email_verified_at' => now(),
            'is_admin' => false,
        ]);

        DB::table('users')->insert([
            'id' => 4,
            'name' => 'Martin der Peter',
            'email' => 'hallomartinderpeter@gmail.com',
            'password' => Hash::make('martin'),
            'email_verified_at' => now(),
            'is_admin' => false,
        ]);
    }
}
