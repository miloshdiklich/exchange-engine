<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domain\Trading\Enums\OrderSide;
use App\Domain\Trading\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'symbol',
        'side',
        'price',
        'amount',
        'status',
        'locked_usd',
        'locked_asset',
        'matched_order_id',
    ];
    
    protected $casts = [
        'side' => OrderSide::class,
        'status' => OrderStatus::class,
        'price' => 'decimal:8',
        'amount' => 'decimal:8',
        'locked_usd' => 'decimal:8',
        'locked_asset' => 'decimal:8',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function matchedOrder()
    {
        return $this->belongsTo(Order::class, 'matched_order_id');
    }
}
