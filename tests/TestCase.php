<?php

namespace Dystcz\LunarApiStripeAdapter\Tests;

use Cartalyst\Converter\Laravel\ConverterServiceProvider;
use Dystcz\LunarApi\JsonApiServiceProvider;
use Dystcz\LunarApi\LunarApiServiceProvider;
use Dystcz\LunarApiStripeAdapter\LunarApiStripeAdapterServiceProvider;
use Dystcz\LunarApiStripeAdapter\Tests\Stubs\Carts\Modifiers\TestShippingModifier;
use Dystcz\LunarApiStripeAdapter\Tests\Stubs\JsonApi\V1\Server;
use Dystcz\LunarApiStripeAdapter\Tests\Stubs\Lunar\TestTaxDriver;
use Dystcz\LunarApiStripeAdapter\Tests\Stubs\Lunar\TestUrlGenerator;
use Dystcz\LunarApiStripeAdapter\Tests\Stubs\Users\User;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Kalnoy\Nestedset\NestedSetServiceProvider;
use LaravelJsonApi\Spec\ServiceProvider;
use Livewire\LivewireServiceProvider;
use Lunar\Base\ShippingModifiers;
use Lunar\Facades\Taxes;
use Lunar\LunarServiceProvider;
use Lunar\Models\Channel;
use Lunar\Models\Country;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;
use Lunar\Models\TaxClass;
use Lunar\Stripe\StripePaymentsServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\LaravelBlink\BlinkServiceProvider;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Taxes::extend('test', function ($app) {
            return $app->make(TestTaxDriver::class);
        });

        Currency::factory()->create([
            'code' => 'EUR',
            'decimal_places' => 2,
        ]);

        Country::factory()->create([
            'name' => 'United Kingdom',
            'iso3' => 'GBR',
            'iso2' => 'GB',
            'phonecode' => '+44',
            'capital' => 'London',
            'currency' => 'GBP',
            'native' => 'English',
        ]);

        Channel::factory()->create([
            'default' => true,
        ]);

        CustomerGroup::factory()->create([
            'default' => true,
        ]);

        TaxClass::factory()->create();

        App::get(ShippingModifiers::class)->add(TestShippingModifier::class);

        activity()->disableLogging();
    }

    protected function getPackageProviders($app)
    {
        return [
            // Laravel JsonApi
            \LaravelJsonApi\Encoder\Neomerx\ServiceProvider::class,
            \LaravelJsonApi\Laravel\ServiceProvider::class,
            ServiceProvider::class,

            // Lunar core
            LunarServiceProvider::class,
            MediaLibraryServiceProvider::class,
            ActivitylogServiceProvider::class,
            ConverterServiceProvider::class,
            NestedSetServiceProvider::class,
            BlinkServiceProvider::class,

            // Lunar Stripe
            StripePaymentsServiceProvider::class,

            // Livewire
            LivewireServiceProvider::class,

            // Lunar API
            LunarApiServiceProvider::class,
            JsonApiServiceProvider::class,

            // Lunar API Stripe Adapter
            LunarApiStripeAdapterServiceProvider::class,
        ];
    }

    /**
     * @param  Application  $app
     */
    public function getEnvironmentSetUp($app)
    {
        $app->useEnvironmentPath(__DIR__.'/..');
        $app->bootstrapWith([LoadEnvironmentVariables::class]);

        Config::set('auth.providers.users', [
            'driver' => 'eloquent',
            'model' => User::class,
        ]);

        /**
         * Lunar configuration
         */
        Config::set('lunar-api.additional_servers', [
            Server::class,
        ]);
        Config::set('lunar.urls.generator', TestUrlGenerator::class);
        Config::set('lunar.taxes.driver', 'test');

        // Default payment driver
        Config::set('lunar.payments.default', 'card');

        // Stripe payment adapter
        // Config::set('lunar-api-stripe-adapter', [
        //     'driver' => 'stripe',
        //     'type' => 'card',
        // ]);

        /**
         * App configuration
         */
        Config::set('database.default', 'sqlite');
        Config::set('database.migrations', 'migrations');

        Config::set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        Config::set('services.stripe', [
            'public_key' => env('STRIPE_PUBLIC_KEY'),
            'key' => env('STRIPE_SECRET_KEY'),
            'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        ]);
    }

    /**
     * Define database migrations.
     *
     * @return void
     */
    protected function defineDatabaseMigrations()
    {
        $this->loadLaravelMigrations();
    }
}
