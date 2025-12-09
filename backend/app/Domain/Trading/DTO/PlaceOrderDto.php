<?php


namespace App\Domain\Trading\DTO;


use App\Models\User;

class PlaceOrderDto
{
    public function __construct(
        public readonly User $user,
        public readonly string $symbol,
        public readonly string $side,
        public readonly string $price,
        public readonly string $amount,
    ) {}
    
    public static function fromArray(User $user, array $data): self
    {
        return new self(
            user: $user,
            symbol: $data['symbol'],
            side: (string) $data['side'],
            price: (string) $data['price'],
            amount: (string) $data['amount'],
        );
    }
}
