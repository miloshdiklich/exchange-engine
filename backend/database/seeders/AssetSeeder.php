<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Database\Seeder;

class AssetSeeder extends Seeder
{
    public function run(): void
    {
        $seller = User::where('email', 'seller@example.com')->first();
        
        Asset::updateOrCreate(
            ['user_id' => $seller->id, 'symbol' => 'BTC'],
            ['amount' => 1.00000000, 'locked_amount' => 0]
        );
        
        Asset::updateOrCreate(
            ['user_id' => $seller->id, 'symbol' => 'ETH'],
            ['amount' => 10.00000000, 'locked_amount' => 0]
        );
    }
}
