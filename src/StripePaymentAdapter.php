<?php

namespace Dystore\Stripe;

use Dystore\Api\Domain\Payments\Contracts\PaymentIntent as PaymentIntentContract;
use Dystore\Api\Domain\Payments\Data\PaymentIntent;
use Dystore\Api\Domain\Payments\PaymentAdapters\PaymentAdapter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Lunar\Models\Cart;
use Lunar\Models\Contracts\Cart as CartContract;
use Lunar\Stripe\Facades\Stripe;
use Lunar\Stripe\Managers\StripeManager;
use Spatie\StripeWebhooks\ProcessStripeWebhookJob;
use Spatie\StripeWebhooks\StripeSignatureValidator;
use Spatie\WebhookClient\WebhookConfig;
use Spatie\WebhookClient\WebhookProcessor;

class StripePaymentAdapter extends PaymentAdapter
{
    protected StripeManager $stripeManager;

    protected WebhookConfig $webhookConfig;

    public function __construct()
    {
        $this->webhookConfig = new WebhookConfig([
            'name' => 'stripe',
            'signing_secret' => Config::get('stripe-webhooks.signing_secret'),
            'signature_header_name' => 'Stripe-Signature',
            'signature_validator' => StripeSignatureValidator::class,
            'webhook_profile' => Config::get('stripe-webhooks.profile'),
            'webhook_model' => Config::get('stripe-webhooks.model'),
            'process_webhook_job' => ProcessStripeWebhookJob::class,
        ]);

        $this->stripeManager = Stripe::getFacadeRoot();
    }

    /**
     * Get payment driver on which this adapter binds.
     *
     * Drivers for lunar are set in lunar.payments.types.
     * When stripe is set as a driver, this adapter will be used.
     */
    public function getDriver(): string
    {
        return Config::get('dystore.stripe.driver', 'stripe');
    }

    /**
     * Get payment type.
     *
     * This key serves is an identification for this adapter.
     * That means that stripe driver is handled by this adapter if configured.
     */
    public function getType(): string
    {
        return Config::get('dystore.stripe.type', 'stripe');
    }

    /**
     * Create payment intent.
     */
    public function createIntent(CartContract $cart, array $meta = [], ?int $amount = null): PaymentIntentContract
    {
        $cart = $this->updateCartMeta($cart, $meta);

        /** @var \Stripe\PaymentIntent $paymentIntent */
        $stripePaymentIntent = $this->stripeManager->createIntent($cart->calculate());

        $paymentIntent = new PaymentIntent(
            intent: $stripePaymentIntent,
            meta: $meta,
        );

        $this->createIntentTransaction($cart, $paymentIntent, $meta);

        return $paymentIntent;
    }

    /**
     * Handle incoming Stripe webhook.
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        return (new WebhookProcessor($request, $this->webhookConfig))->process();
    }

    /**
     * Update cart meta.
     *
     * @param  array<string,mixed>  $meta
     */
    protected function updateCartMeta(CartContract $cart, array $meta = []): Cart
    {
        if (empty($meta)) {
            return $cart;
        }

        /** @var Cart $cart */
        $cart->update('meta', [
            ...$cart->meta,
            ...$meta,
        ]);

        return $cart;
    }
}
