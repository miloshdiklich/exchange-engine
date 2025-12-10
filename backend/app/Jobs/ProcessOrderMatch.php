<?php

namespace App\Jobs;

use App\Contracts\OrderRepositoryInterface;
use App\Domain\Trading\Services\MatchingService;
use App\Events\OrderMatched;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessOrderMatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly int $orderId,
    ) {}

    public function handle(
        OrderRepositoryInterface $orders,
        MatchingService $matcher,
    ): void {
        $order = $orders->findById($this->orderId);

        if (! $order) {
            return;
        }

        // MatchingService already does locking + transaction internally
        $trade = $matcher->attemptMatch($order);

        if (! $trade) {
            return;
        }

        // Load related orders for broadcasting
        $trade->loadMissing(['buyOrder', 'sellOrder']);

        $buyOrder  = $trade->buyOrder ?? null;
        $sellOrder = $trade->sellOrder ?? null;

        if ($buyOrder && $sellOrder) {
            event(new OrderMatched(
                trade: $trade,
                buyOrder: $buyOrder,
                sellOrder: $sellOrder,
            ));
        }
    }
}
