<?php

namespace Dystcz\LunarApiStripeAdapter\Jobs\Webhooks;

use Dystcz\LunarApi\Domain\Payments\Data\PaymentIntent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\WebhookClient\Models\WebhookCall;
use Stripe\Event;
use Throwable;

class HandlePaymentIntentRequiresAction implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public WebhookCall $webhookCall;

    public function __construct(WebhookCall $webhookCall)
    {
        $this->webhookCall = $webhookCall;
    }

    /**
     * Handle payment intent requires action.
     */
    public function handle(): void
    {
        try {
            $event = Event::constructFrom($this->webhookCall->payload);
            $paymentIntent = new PaymentIntent(intent: $event->data->object);
        } catch (Throwable $e) {
            $this->fail($e);
        }
    }
}
