<?php

namespace App\Providers;

use App\Contracts\AssetRepositoryInterface;
use App\Contracts\OrderRepositoryInterface;
use App\Contracts\TradeRepositoryInterface;
use App\Contracts\UserRepositoryInterface;
use App\Repositories\AssetRepository;
use App\Repositories\OrderRepository;
use App\Repositories\TradeRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AssetRepositoryInterface::class, AssetRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(TradeRepositoryInterface::class, TradeRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
