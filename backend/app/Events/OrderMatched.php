<?php

namespace App\Events;

use App\Models\Order;
use App\Models\Trade;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderMatched implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly Trade $trade,
        public readonly Order $buyOrder,
        public readonly Order $sellOrder,
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->buyOrder->user_id),
            new PrivateChannel('user.' . $this->sellOrder->user_id),
        ];
    }
    
    public function broadcastAs(): string
    {
        return 'OrderMatched';
    }
    
    public function broadcastWith(): array
    {
        return [
            'trade' => [
                'id' => $this->trade->id,
                'symbol' => $this->trade->symbol,
                'price' => $this->trade->price,
                'amount' => $this->trade->amount,
                'volume_usd' => $this->trade->volume_usd,
                'fee_usd_buyer' => $this->trade->fee_usd_buyer,
                'fee_usd_seller' => $this->trade->fee_usd_seller,
                'created_at' => $this->trade->created_at->toDateTimeString(),
            ],
            'buy_order' => [
                'id' => $this->buyOrder->id,
                'status' => $this->buyOrder->status,
                'symbol' => $this->buyOrder->symbol,
                'price' => $this->buyOrder->price,
                'amount' => $this->buyOrder->amount,
                'side' => $this->buyOrder->side,
            ],
            'sell_order' => [
                'id' => $this->sellOrder->id,
                'status' => $this->sellOrder->status,
                'symbol' => $this->sellOrder->symbol,
                'price' => $this->sellOrder->price,
                'amount' => $this->sellOrder->amount,
                'side' => $this->sellOrder->side,
            ],
        ];
    }
}
