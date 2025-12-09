<?php

namespace App\Contracts;

use App\Models\Trade;

interface TradeRepositoryInterface
{
    public function create(array $data): Trade;
}
