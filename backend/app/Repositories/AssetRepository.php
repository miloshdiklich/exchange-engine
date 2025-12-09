<?php


namespace App\Repositories;


use App\Contracts\AssetRepositoryInterface;
use App\Models\Asset;

class AssetRepository implements AssetRepositoryInterface
{
    public function lockForUserAndSymbol(int $userId, string $symbol): ?Asset
    {
        return Asset::query()
            ->where('user_id', $userId)
            ->where('symbol', $symbol)
            ->lockForUpdate()
            ->first();
    }
    
    public function lockOrCreate(int $userId, string $symbol): Asset
    {
        $asset = $this->lockForUserAndSymbol($userId, $symbol);
        
        if (!$asset) {
            $asset = Asset::query()->create([
                'user_id' => $userId,
                'symbol' => $symbol,
                'amount' => '0',
            ]);
        }
        return $asset;
    }
    
    public function save(Asset $asset): void
    {
        $asset->save();
    }
}
