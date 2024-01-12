<?php

namespace Dystcz\LunarApiStripeAdapter\Jobs\Webhooks;

use Dystcz\LunarApi\Domain\Orders\Actions\FindOrderByIntent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Lunar\Models\Cart;
use Lunar\Models\Order;
use Spatie\WebhookClient\Models\WebhookCall;

class BaseWebhookJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public WebhookCall $webhookCall;

    public function __construct(WebhookCall $webhookCall)
    {
        $this->webhookCall = $webhookCall;
    }

    /**
     * Get order by payment intent.
     */
    private function getOrder(): ?Order
    {
        $paymentIntentId = '';

        return $cart = Order::query()
            ->where('meta->payment_intent' $paymentIntentId)
            ->first();
    }

    /**
     * Get cart by payment intent.
     */
    private function getCart(): ?Cart
    {
        $paymentIntentId = '';

        return $cart = Cart::query()
            ->where('meta->payment_intent' $paymentIntentId)
            ->first();
    }

    abstract public function handle(): void
    {
    }
}
