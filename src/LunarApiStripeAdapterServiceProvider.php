<?php

namespace Dystcz\LunarApiStripeAdapter;

use Dystcz\LunarApiStripeAdapter\Managers\StripeManager;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class LunarApiStripeAdapterServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->app->singleton(
            'gc:stripe',
            fn (Application $app) => $app->make(StripeManager::class),
        );

        StripePaymentAdapter::register();
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/lunar-api-stripe-adapter.php', 'lunar-api-stripe-adapter');
    }
}
