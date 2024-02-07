<?php

namespace Dystcz\LunarApiStripeAdapter\Jobs\Webhooks;

use Dystcz\LunarApiStripeAdapter\Actions\AuthorizeStripePayment;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

class HandlePaymentIntentSucceeded extends WebhookHandler
{
    /**
     * Handle failed payment intent.
     */
    public function handle(): void
    {
        $event = $this->constructStripeEvent();
        $paymentIntent = $this->getPaymentIntentFromEvent($event);
        $order = $this->findOrderByIntent($paymentIntent);

        $paymentAdapter = $this->register->get(Config::get('lunar-api.stripe.driver', 'stripe'));

        App::make(AuthorizeStripePayment::class)($order, $order->cart, $paymentIntent);
    }
}
