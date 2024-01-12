<?php

namespace Dystcz\LunarApiStripeAdapter;

use Dystcz\LunarApi\Domain\Orders\Actions\FindOrderByIntent;
use Dystcz\LunarApi\Domain\Orders\Events\OrderPaymentCanceled;
use Dystcz\LunarApi\Domain\Orders\Events\OrderPaymentFailed;
use Dystcz\LunarApi\Domain\Payments\PaymentAdapters\PaymentAdapter;
use Dystcz\LunarApi\Domain\Payments\PaymentAdapters\PaymentIntent;
use Dystcz\LunarApiStripeAdapter\Actions\AuthorizeStripePayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Lunar\Models\Cart;
use Lunar\Stripe\Facades\StripeFacade;
use Lunar\Stripe\Managers\StripeManager;
use Spatie\StripeWebhooks\ProcessStripeWebhookJob;
use Spatie\StripeWebhooks\StripeSignatureValidator;
use Spatie\WebhookClient\WebhookConfig;
use Spatie\WebhookClient\WebhookProcessor;
use Stripe\Webhook;
use Throwable;

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
            'webhook_profile' => config('stripe-webhooks.profile'),
            'webhook_model' => config('stripe-webhooks.model'),
            'process_webhook_job' => ProcessStripeWebhookJob::class,
        ]);

        $this->stripeManager = StripeFacade::getFacadeRoot();
    }

    /**
     * Get payment driver on which this adapter binds.
     *
     * Drivers for lunar are set in lunar.payments.types.
     * When stripe is set as a driver, this adapter will be used.
     */
    public function getDriver(): string
    {
        return 'stripe';
    }

    /**
     * Get payment type.
     *
     * This key serves is an identification for this adapter.
     * That means that stripe driver is handled by this adapter if configured.
     */
    public function getType(): string
    {
        return 'stripe';
    }

    /**
     * Get Stripe manager.
     */
    private function getStripeManager(): StripeManager
    {
        return StripeFacade::getFacadeRoot();
    }

    /**
     * Create payment intent.
     */
    public function createIntent(Cart $cart, array $meta = []): PaymentIntent
    {
        $cart = $this->updateCartMeta($cart, $meta);

        /** @var \Stripe\PaymentIntent $paymentIntent */
        $paymentIntent = $this->stripeManager->createIntent($cart->calculate());

        $this->createTransaction($paymentIntent);

        return $paymentIntent;
    }

    /**
     * Handle incoming Stripe webhook.
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        return (new WebhookProcessor($request, $this->webhookConfig))->process();

        try {
            $order = App::make(FindOrderByIntent::class)($paymentIntent);
        } catch (Throwable $e) {
            return new JsonResponse([
                'webhook_successful' => false,
                'message' => "Order not found for payment intent {$paymentIntent->id}",
            ], 404);
        }

        switch ($paymentIntentStatus) {
            case 'succeeded':
                App::make(AuthorizeStripePayment::class)($order, $paymentIntent);
                break;
            case 'canceled':
                OrderPaymentCanceled::dispatch($order, $this, $paymentIntent);
                break;
            case 'failed':
                OrderPaymentFailed::dispatch($order, $this, $paymentIntent);
                break;
            default:
                Log::info('Received unknown event type '.$event->type);
        }

        return new JsonResponse([
            'webhook_successful' => true,
            'message' => 'Webook handled successfully',
        ]);
    }

    /**
     * Update cart meta.
     *
     * @param  array<string,mixed>  $meta
     */
    protected function updateCartMeta(Cart $cart, array $meta = []): Cart
    {
        if (empty($meta)) {
            return $cart;
        }

        $cart->update('meta', [
            ...$this->cart->meta,
            ...$meta,
        ]);

        return $cart;
    }
}
