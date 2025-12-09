<?php


namespace App\Services\Trading;


use App\Contracts\AssetRepositoryInterface;
use App\Contracts\OrderRepositoryInterface;
use App\Contracts\UserRepositoryInterface;
use App\Domain\Trading\DTO\PlaceOrderDto;
use App\Domain\Trading\Enums\OrderSide;
use App\Domain\Trading\Enums\OrderStatus;
use App\Domain\Trading\Services\MatchingService;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly AssetRepositoryInterface $assets,
        private readonly OrderRepositoryInterface $orders,
        private readonly MatchingService $matcher,
    ) {}
    
    public function placeOrder(PlaceOrderDto $dto): array
    {
        return DB::transaction(function () use ($dto) {
            $user = $this->users->lockById($dto->user->id);
            
            if ($dto->side === OrderSide::BUY) {
                $order = $this->placeBuyOrder($user, $dto);
            } else {
                $order = $this->placeSellOrder($user, $dto);
            }
            
            $trade = $this->matcher->attemptMatch($order);
            
            return [
                'order' => $order,
                'trade' => $trade,
            ];
        });
    }
    
    /**
     * @param User $user
     * @param PlaceOrderDto $data
     * @return Order
     */
    private function placeBuyOrder(User $user, PlaceOrderDto $data): Order
    {
        $requiredUsd = bcmul($data->price, $data->amount, 8);
        
        if (bccomp($user->balance, $requiredUsd, 8) < 0) {
            throw new \RuntimeException('Insufficient USD balance.');
        }
        
        $user->balance = bcsub($user->balance, $requiredUsd, 8);
        $this->user->save($user);
        
        return $this->orders->create([
            'user_id' => $user->id,
            'symbol' => $data->symbol,
            'side' => OrderSide::BUY,
            'price' => $data->price,
            'amount' => $data->amount,
            'status' => OrderStatus::OPEN,
            'locked_usd' => $requiredUsd,
            'locked_asset' => '0',
        ]);
    }
    
    /**
     * @param $user
     * @param PlaceOrderDto $data
     * @return Order
     */
    private function placeSellOrder($user, PlaceOrderDto $data): Order
    {
        $asset = $this->assets->lockForUserAndSymbol($user->id, $data->symbol);
        
        if ( !$asset || bccomp($asset->amount, $data->amount, 8) < 0) {
            throw new \RuntimeException('Insufficient asset balance.');
        }
        
        $asset->amount = bcsub($asset->amount, $data->amount, 8);
        $asset->locked_amount = bcadd($asset->locked_amount, $data->amount, 8);
        $this->assets->save($asset);
        
        return $this->orders->create([
            'user_id' => $user->id,
            'symbol' => $data->symbol,
            'side' => OrderSide::SELL,
            'price' => $data->price,
            'amount' => $data->amount,
            'status' => OrderStatus::OPEN,
            'locked_usd' => '0',
            'locked_asset' => $data->amount,
        ]);
    }
}
