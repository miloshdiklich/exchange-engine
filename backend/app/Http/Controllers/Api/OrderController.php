<?php

namespace App\Http\Controllers\Api;

use App\Contracts\OrderRepositoryInterface;
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
        private readonly OrderService $orderService,
        private readonly OrderRepositoryInterface $orders,
    ) {}
    
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $symbol = $request->query('symbol');
        
        $openOrders = $this->orders->getOpenOrders($symbol);
        $myOrders = $this->orders->getUserOrders($user->id, $symbol);
        
        return response()->json([
            'open_orders' => $openOrders,
            'my_orders' => $myOrders,
        ]);
    }
    
    public function store(PlaceOrderRequest $request)
    {
        $dto = PlaceOrderDto::fromArray($request->user(), $request->validated());
        
        $result = $this->orderService->placeOrder($dto);
        
        return response()->json($result, 201);
    }
    
    public function cancel(Request $request, int $orderId)
    {
        $order = $this->orderService->cancelOrder($request->user(), $orderId);
        
        return response()->json([
            'order' => $order,
        ]);
    }
}
