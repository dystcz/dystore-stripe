<?php

namespace Dystcz\LunarApiStripeAdapter\Managers;

use Lunar\Stripe\Managers\StripeManager as LunarStripeManager;
use Stripe\PaymentIntent;

class StripeManager extends LunarStripeManager
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Build the intent
     *
     * @param  int  $value
     * @param  string  $currencyCode
     * @param  \Lunar\Models\CartAddress  $shipping
     */
    protected function buildIntent($value, $currencyCode, $shipping): PaymentIntent
    {
        parent::buildIntent($value, $currencyCode, $shipping);
    }
}
