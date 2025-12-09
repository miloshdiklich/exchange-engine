<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'buyer@example.com'],
            [
                'name'     => 'Buyer User',
                'password' => Hash::make('password'),
                'balance'  => 100000.00000000,
            ]
        );
        
        User::updateOrCreate(
            ['email' => 'seller@example.com'],
            [
                'name'     => 'Seller User',
                'password' => Hash::make('password'),
                'balance'  => 1000.00000000,
            ]
        );
    }
}
