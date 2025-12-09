<?php

namespace App\Contracts;

use App\Models\Asset;

interface AssetRepositoryInterface
{
    public function lockForUserAndSymbol(int $userId, string $symbol): ?Asset;
    
    public function lockOrCreate(int $userId, string $symbol): Asset;
    
    public function save(Asset $asset): void;
}
