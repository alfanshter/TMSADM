<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin Utama',
            'email' => 'admin@admin.com',
<<<<<<< HEAD
            'password' => Hash::make('password'), // password default
=======
            'password' => Hash::make('admin123'), // password default
>>>>>>> temp-main
            'role' => 'admin',
            'phone' => '081234567890',
            'status' => true,
            'avatar' => null,
        ]);
    }
}
