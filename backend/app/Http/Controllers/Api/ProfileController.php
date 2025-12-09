<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request)
    {
        $user = $request->user()->load('assets');
        
        return response()->json([
           'balance' => $user->balance,
           'assets' => $user->assets->map(function ($asset) {
               return [
                   'symbol' => $asset->symbol,
                   'amount' => $asset->amount,
                   'locked_amount' => $asset->locked_amount,
               ];
           }),
        ]);
    }
}
