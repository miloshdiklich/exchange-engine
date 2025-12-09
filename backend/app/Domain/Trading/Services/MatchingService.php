<?php


namespace App\Domain\Trading\Services;


use App\Contracts\AssetRepositoryInterface;
use App\Contracts\OrderRepositoryInterface;
use App\Contracts\TradeRepositoryInterface;
use App\Contracts\UserRepositoryInterface;
use App\Domain\Trading\Enums\OrderSide;
use App\Domain\Trading\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Trade;
use Illuminate\Support\Facades\DB;

class MatchingService
{
    public function __construct(
        private readonly OrderRepositoryInterface $orders,
        private readonly UserRepositoryInterface $users,
        private readonly AssetRepositoryInterface $assets,
        private readonly TradeRepositoryInterface $trades,
    ) {}
    
    /**
     * @param Order $incomingOrder
     * @return Trade|null
     */
    public function attemptMatch(Order $incomingOrder): ?Trade
    {
        $incomingOrder = $this->orders->lock($incomingOrder);
        
        if ($incomingOrder->status !== OrderStatus::OPEN) {
            return null;
        }
        
        $counterOrder = $this->orders->findOpenCounterOrder($incomingOrder);
        
        if (!$counterOrder) {
            return null;
        }
        
        $counterOrder = $this->orders->lock($counterOrder);
        
        if ($counterOrder->status !== OrderStatus::OPEN) {
            return null;
        }
        
        if (bccomp($incomingOrder->amount, $counterOrder->amount, 8) !== 0) {
            return null;
        }
        
        return $this->executeTrade($incomingOrder, $counterOrder);
    }
    
    /**
     * @param Order $orderA
     * @param Order $orderB
     * @return Trade
     */
    private function executeTrade(Order $orderA, Order $orderB): Trade
    {
        if ($orderA->side === OrderSide::BUY) {
            $buyOrder = $orderA;
            $sellOrder = $orderB;
        } else {
            $buyOrder = $orderB;
            $sellOrder = $orderA;
        }
        
        $price = (string) $sellOrder->price;
        $amount = (string) $buyOrder->amount;
        
        $volume = bcmul($price, $amount, 8);
        $fee = bcmul($volume, '0.015', 8);
        
        return DB::transaction(function () use ($buyOrder, $sellOrder, $amount, $volume, $fee, $price) {
            $buyer = $this->users->lockById($buyOrder->user_id);
            $seller = $this->users->lockById($sellOrder->user_id);
            
            $buyerAsset = $this->assets->lockOrCreate($buyer->id, $buyOrder->symbol);
            $sellerAsset = $this->assets->lockForUserAndSymbol($seller->id, $sellOrder->symbol);
            
            if (!$sellerAsset) {
                throw new \RuntimeException('Seller does not have the asset to sell.');
            }
            
            $reserved = (string) $buyOrder->locked_usd;
            $netCost = bcadd($volume, $fee, 8);
            $leftover = bcsub($reserved, $netCost, 8);
            
            if (bccomp($leftover, '0', 8) < 0) {
                throw new \RuntimeException('Buyer has insufficient locked USD for the trade.');
            }
            
            $buyerAsset->amount = bcadd($buyerAsset->amount, $amount, 8);
            $this->assets->save($buyerAsset);
            
            $buyer->balance = bcadd($buyer->balance, $netCost, 8);
            $this->users->save($buyer);
            
            $sellerAsset->locked_amount = bcsub($sellerAsset->locked_amount, $amount, 8);
            $this->assets->save($sellerAsset);
            
            $seller->balance = bcadd($seller->balance, $volume, 8);
            $this->users->save($seller);
            
            $buyOrder->status = OrderStatus::FILLED;
            $buyOrder->locked_usd = '0';
            $buyOrder->matched_order_id = $sellOrder->id;
            $this->orders->save($buyOrder);
            
            $sellOrder->status = OrderStatus::FILLED;
            $sellOrder->locked_asset = '0';
            $sellOrder->matched_order_id = $buyOrder->id;
            $this->orders->save($sellOrder);
            
            $trade = $this->trades->create([
                'buy_order_id' => $buyOrder->id,
                'sell_order_id' => $sellOrder->id,
                'buyer_id' => $buyer->id,
                'seller_id' => $seller->id,
                'symbol' => $buyOrder->symbol,
                'price' => $price,
                'amount' => $amount,
                'volume_usd' => $volume,
                'fee_usd_buyer' => $fee,
                'fee_usd_seller' => '0',
            ]);
            
            //TODO: Dispatch trade executed event
            
            return $trade;
        });
    }
}
