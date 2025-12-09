<?php


namespace App\Domain\Trading\DTO;


use App\Domain\Trading\Enums\OrderSide;
use App\Models\User;

class PlaceOrderDto
{
    public function __construct(
        public readonly User $user,
        public readonly string $symbol,
        public readonly OrderSide $side,
        public readonly string $price,
        public readonly string $amount,
    ) {}
    
    public static function fromArray(User $user, array $data): self
    {
        $sideEnum = $data['side'] === 'buy' ? OrderSide::BUY : OrderSide::SELL;
        return new self(
            user: $user,
            symbol: $data['symbol'],
            side: $sideEnum,
            price: (string) $data['price'],
            amount: (string) $data['amount'],
        );
    }
}
