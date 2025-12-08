<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Trade extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'buy_order_id',
        'sell_order_id',
        'buyer_id',
        'seller_id',
        'symbol',
        'price',
        'amount',
        'volume_usd',
        'fee_usd_buyer',
        'fee_usd_seller',
    ];
    
    protected $casts = [
        'price' => 'decimal:8',
        'amount' => 'decimal:8',
        'volume_usd' => 'decimal:8',
        'fee_usd_buyer' => 'decimal:8',
        'fee_usd_seller' => 'decimal:8',
    ];
    
    public function buyOrder()
    {
        return $this->belongsTo(Order::class, 'buy_order_id');
    }
    
    public function sellOrder()
    {
        return $this->belongsTo(Order::class, 'sell_order_id');
    }
    
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }
    
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
}
