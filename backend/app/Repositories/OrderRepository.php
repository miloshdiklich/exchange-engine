<?php


namespace App\Repositories;


use App\Models\Order;
use Illuminate\Support\Collection;
use App\Domain\Trading\Enums\OrderSide;
use App\Domain\Trading\Enums\OrderStatus;
use App\Contracts\OrderRepositoryInterface;

class OrderRepository implements OrderRepositoryInterface
{
    public function create(array $data): Order
    {
        return Order::query()->create($data);
    }

    public function findById(int $id): ?Order
    {
      return Order::query()->find($id);
    }
    
    public function findByIdForUser(int $id, int $userId): ?Order
    {
        return Order::query()
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();
    }
    
    public function findOpenCounterOrder(Order $incomingOrder): ?Order
    {
        $q = Order::query()
            ->where('symbol', $incomingOrder->symbol)
            ->where('status', OrderStatus::OPEN)
            ->where('amount', $incomingOrder->amount);
        
        if ($incomingOrder->side === OrderSide::BUY) {
            $q->where('side', OrderSide::SELL)
                ->where('price', '<=', $incomingOrder->price);
        } else {
            $q->where('side', OrderSide::BUY)
                ->where('price', '>=', $incomingOrder->price);
        }
        return $q->orderBy('created_at')->first();
    }
    
    public function lock(Order $order): Order
    {
        return Order::query()
            ->whereKey($order->getKey())
            ->lockForUpdate()
            ->firstOrFail();
    }
    
    public function getOpenOrders(?string $symbol = null): Collection
    {
        return Order::query()
            ->when($symbol, fn ($query) => $query->where('symbol', $symbol))
            ->where('status', OrderStatus::OPEN)
            ->orderBy('created_at')
            ->get();
    }
    
    public function getUserOrders(int $userId, ?string $symbol = null): Collection
    {
        return Order::query()
            ->where('user_id', $userId)
            ->when($symbol, fn ($query, $symbol) => $query->where('symbol', $symbol))
            ->orderByDesc('created_at')
            ->get();
    }
    
    public function save(Order $order): void
    {
        $order->save();
    }
}
