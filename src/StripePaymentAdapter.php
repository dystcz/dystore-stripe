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
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Throwable;
use UnexpectedValueException;

class StripePaymentAdapter extends PaymentAdapter
{
    /**
     * Get payment driver.
     */
    public function getDriver(): string
    {
        return Config::get('lunar-api-stripe-adapter.driver', 'stripe');
    }

    /**
     * Get payment type.
     */
    public function getType(): string
    {
        return Config::get('lunar-api-stripe-adapter.type', 'stripe');
    }

    /**
     * Create payment intent.
     */
    public function createIntent(Cart $cart): PaymentIntent
    {
        $this->cart = $cart;

        /** @var Stripe\PaymentIntent $intent */
        $intent = StripeFacade::createIntent($cart->calculate());

        $this->createTransaction($intent->id, $intent->amount);

        return new PaymentIntent(
            id: $intent->id,
            client_secret: $intent->client_secret,
        );
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

        $paymentIntent = $event->data->object;

        try {
            $order = App::make(FindOrderByIntent::class)($paymentIntent->id);
        } catch (Throwable $e) {
            return new JsonResponse([
                'message' => "Order not found for payment intent {$paymentIntent->id}",
            ], 404);
        }

        switch ($event->type) {
            case 'payment_intent.succeeded':
                App::make(AuthorizeStripePayment::class)($order, $paymentIntent);
                break;
            case 'payment_intent.canceled':
                OrderPaymentCanceled::dispatch($order, $paymentIntent);
                break;
            case 'payment_intent.payment_failed':
                OrderPaymentFailed::dispatch($order, $paymentIntent);
                break;
            default:
                Log::info('Received unknown event type '.$event->type);
        }

        return new JsonResponse([
            'message' => 'success',
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
                Config::get('services.stripe.webhook_secret')
            );
        } catch (UnexpectedValueException $e) {
            report($e);

            return new JsonResponse([
                'error' => 'Invalid payload',
            ], 400);
        } catch (SignatureVerificationException $e) {
            report($e);

            return new JsonResponse([
                'error' => 'Invalid signature',
            ], 400);
        }
    }
}
