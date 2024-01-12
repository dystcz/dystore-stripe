<?php

namespace Dystcz\LunarApiStripeAdapter;

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
use Lunar\Models\Order;
use Lunar\Stripe\Facades\StripeFacade;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use UnexpectedValueException;

class StripePaymentAdapter extends PaymentAdapter
{
    /**
     * Get payment driver.
     */
    public function getDriver(): string
    {
        return Config::get('lunar-api.stripe.driver', 'stripe');
    }

    /**
     * Get payment type.
     */
    public function getType(): string
    {
        return Config::get('lunar-api.stripe.type', 'stripe');
    }

    /**
     * Set cart.
     */
    protected function setCart(Cart $cart): void
    {
        $this->cart = $cart;
    }

    /**
     * Create payment intent.
     */
    public function createIntent(Cart $cart, array $meta = []): PaymentIntent
    {
        $this->setCart($cart);

        /** @var Stripe\PaymentIntent $stripePaymentIntent */
        $stripePaymentIntent = StripeFacade::createIntent($cart->calculate());

        $meta = [
            'payment_intent' => $stripePaymentIntent->id,
        ];

        $cart->update(['meta' => $meta]);

        $paymentIntent = new PaymentIntent(
            id: $stripePaymentIntent->id,
            amount: $stripePaymentIntent->amount,
            status: 'intent',
            client_secret: $stripePaymentIntent->client_secret,
        );

        $this->createTransaction($paymentIntent);

        return $paymentIntent;
    }

    /**
     * Handle incoming Stripe webhook.
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        $event = App::environment('testing')
            // Construct directly from request in testing environment
            ? Event::constructFrom($request->all())
            // Construct from Stripe webhook while checking signature
            : $this->constructEvent($request);

        // Return early if event counld not be constructed
        if ($event instanceof JsonResponse) {
            return $event;
        }

        $stripePaymentIntent = $event->data->object;

        $statusMap = Config::get('lunar-api.stripe.payment_intent_status_map', []);

        $paymentIntentStatus = match (true) {
            in_array($event->type, array_keys($statusMap)) => $statusMap[$event->type],
            default => 'unknown',
        };

        $paymentIntent = new PaymentIntent(
            id: $stripePaymentIntent->id,
            amount: $stripePaymentIntent->amount,
            status: $paymentIntentStatus,
            client_secret: $stripePaymentIntent->client_secret,
        );

        $cart = Cart::query()
            ->with(['draftOrder', 'completedOrder'])
            ->where('meta->payment_intent', $paymentIntent->id)
            ->first();

        /** @var Order $order */
        $order = $cart->draftOrder ?: $cart->completedOrder;

        switch ($paymentIntentStatus) {
            case 'succeeded':
                App::make(AuthorizeStripePayment::class)($order, $cart, $paymentIntent);
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
     * Construct Stripe event.
     */
    protected function constructEvent(Request $request): JsonResponse|Event
    {
        try {
            return Webhook::constructEvent(
                $request->getContent(),
                $request->header('Stripe-Signature'),
                Config::get('services.stripe.webhooks.payment_intent')
            );
        } catch (UnexpectedValueException|SignatureVerificationException $e) {
            report($e);

            return new JsonResponse([
                'webhook_successful' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
