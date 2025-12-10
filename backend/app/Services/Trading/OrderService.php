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
use App\Models\User;
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
        $result = DB::transaction(function () use ($dto) {
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
        
        if ($result['trade']) {
            $trade = $result['trade'];
            $trade->loadMissing(['buyOrder', 'sellOrder']);
            
            $buyOrder = $result['trade']->buyOrder ?? null;
            $sellOrder = $result['trade']->sellOrder ?? null;
            
            if ($buyOrder && $sellOrder) {
                // Fire event outside transaction
                event(new \App\Events\OrderMatched(
                    trade: $trade,
                    buyOrder: $buyOrder,
                    sellOrder: $sellOrder,
                ));
            }
        }
        return $result;
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
        $this->users->save($user);
        
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
    
    /**
     * @param User $user
     * @param int $orderId
     * @return Order
     */
    public function cancelOrder(User $user, int $orderId): Order
    {
        return DB::transaction(function () use ($user, $orderId) {
            // find order belonging to user
            $order = $this->orders->findByIdForUser($orderId, $user->id);
            
            if (! $order) {
                throw new \RuntimeException('Order not found.');
            }
            
            // lock the order row
            $order = $this->orders->lock($order);
            
            if ($order->status !== OrderStatus::OPEN) {
                throw new \RuntimeException('Only OPEN orders can be cancelled.');
            }
            
            if ($order->side === OrderSide::BUY) {
                $this->cancelBuyOrder($order);
            } else {
                $this->cancelSellOrder($order);
            }
            
            $order->status = OrderStatus::CANCELLED;
            $order->locked_usd = '0';
            $order->locked_asset = '0';
            $this->orders->save($order);
            
            return $order;
        });
    }
    
    /**
     * @param Order $order
     * @return void
     */
    private function cancelBuyOrder(Order $order): void
    {
        $buyer = $this->users->lockById($order->user_id);
        $lockedUsd = $order->locked_usd;
        
        // refund locked USD to buyer
        if (bccomp($lockedUsd, '0', 8) > 0) {
            $buyer->balance = bcadd($buyer->balance, $lockedUsd, 8);
            $this->users->save($buyer);
        }
    }
    
    /**
     * @param Order $order
     * @return void
     */
    private function cancelSellOrder(Order $order): void
    {
        $seller = $this->users->lockById($order->user_id);
        $lockedAmount = $order->locked_asset;
        
        if (bccomp($lockedAmount, '0', 8) <= 0) {
            return;
        }
        
        $asset = $this->assets->lockForUserAndSymbol($seller->id, $order->symbol);
        
        if (!$asset) {
            throw new \RuntimeException('Seller asset not found during order cancellation.');
        }
        
        $asset->amount = bcadd($asset->amount, $lockedAmount, 8);
        $asset->locked_amount = bcsub($asset->locked_amount, $lockedAmount, 8);
        $this->assets->save($asset);
    }
}
