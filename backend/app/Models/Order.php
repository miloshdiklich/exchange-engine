<?php

namespace App\Models;

use App\Domain\Trading\Enums\OrderSide;
use App\Domain\Trading\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'matched_order_id'
    ];
    
    protected $casts = [
        'side' => OrderSide::class,
        'status' => OrderStatus::class,
        'price' => 'decimal:8',
        'amount' => 'decimal:8',
        'locked_usd' => 'decimal:8',
        'locked_asset' => 'decimal:8',
    ];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function matchedOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'matched_order_id');
    }
}
