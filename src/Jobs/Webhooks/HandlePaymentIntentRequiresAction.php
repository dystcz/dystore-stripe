<?php

namespace Dystcz\LunarApiStripeAdapter\Jobs\Webhooks;

use Illuminate\Support\Facades\Config;

class HandlePaymentIntentRequiresAction extends WebhookHandler
{
    /**
     * Handle payment intent processing.
     */
    public function handle(): void
    {
        // $event = $this->constructStripeEvent();
        // $paymentIntent = $this->getPaymentIntentFromEvent($event);
        // $order = $this->findOrderByIntent($paymentIntent);
        //
        // $paymentAdapter = $this->register->get(Config::get('lunar-api.stripe.driver', 'stripe'));
    }
}
