<?php

namespace App\Contracts;

use App\Models\Order;
use Illuminate\Support\Collection;

interface OrderRepositoryInterface
{
    public function create(array $data): Order;
    
    public function findByIdForUser(int $id, int $userId): ?Order;
    
    public function findOpenCounterOrder(Order $incomingOrder): ?Order;
    
    public function lock(Order $order): Order;
    
    public function getOpenOrders(?string $symbol = null): Collection;
    
    public function getUserOrders(int $userId, ?string $symbol = null): Collection;
    
    public function save(Order $order): void;
}
