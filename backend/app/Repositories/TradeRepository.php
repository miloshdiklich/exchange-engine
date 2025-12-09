<?php


namespace App\Repositories;


use App\Contracts\TradeRepositoryInterface;
use App\Models\Trade;

class TradeRepository implements TradeRepositoryInterface
{
    public function create(array $data): Trade
    {
        return Trade::query()->create($data);
    }
}
