<?php

namespace Dystcz\LunarApiStripeAdapter\Jobs\Webhooks;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\WebhookClient\Models\WebhookCall;
use Stripe\Event;
use Throwable;

class HandlePaymentIntentCreated implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public WebhookCall $webhookCall;

    public function __construct(WebhookCall $webhookCall)
    {
        $this->webhookCall = $webhookCall;
    }

    /**
     * Handle created payment intent.
     */
    public function handle(): void
    {
        try {
            $event = Event::constructFrom($this->webhookCall->payload);
        } catch (Throwable $e) {
            $this->fail($e);
        }
    }
}
