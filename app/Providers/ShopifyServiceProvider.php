<?php

namespace App\Providers;

use App\Repositories\Contracts\ShopifyProductRepositoryInterface;
use App\Repositories\Eloquents\Product\ShopifyProductRepository;
use Illuminate\Support\ServiceProvider;

class ShopifyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->app->bind(ShopifyProductRepositoryInterface::class, ShopifyProductRepository::class);
    }
}
