<?php

namespace Dystcz\LunarApiStripeAdapter\Jobs\Webhooks;

use Dystcz\LunarApi\Domain\Orders\Events\OrderPaymentCanceled;
use Illuminate\Support\Facades\Config;

class HandlePaymentIntentCancelled extends WebhookHandler
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

        OrderPaymentCanceled::dispatch($order, $paymentAdapter, $paymentIntent);
    }
}
