<?php

namespace App\Http\Controllers\Api;

use App\Domain\Trading\DTO\PlaceOrderDto;
use App\Domain\Trading\Enums\OrderStatus;
use App\Http\Requests\Trading\PlaceOrderRequest;
use App\Services\Trading\OrderService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService
    ) {}
    
    public function index(Request $request): JsonResponse
    {
        // TODO: Implement order listing
    }
    
    public function store(PlaceOrderRequest $request)
    {
        $dto = PlaceOrderDto::fromArray($request->user(), $request->validated());
        
        $result = $this->orderService->placeOrder($dto);
        
        return response()->json($result, 201);
    }
    
    public function cancel()
    {
        // TODO: Implement order cancellation
    }
}
