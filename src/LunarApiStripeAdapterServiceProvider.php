<?php

namespace Dystcz\LunarApiStripeAdapter;

use Illuminate\Support\ServiceProvider;

class LunarApiStripeAdapterServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        StripePaymentAdapter::register();
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/lunar-api-stripe-adapter.php', 'lunar-api-stripe-adapter');
    }
}
