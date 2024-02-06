<?php

namespace Dystcz\LunarApiStripeAdapter\Jobs\Webhooks;

use Dystcz\LunarApi\Domain\Orders\Actions\FindOrderByIntent;
use Dystcz\LunarApi\Domain\Orders\Events\OrderPaymentCanceled;
use Dystcz\LunarApi\Domain\Payments\Data\PaymentIntent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Lunar\Stripe\Facades\StripeFacade;
use Spatie\WebhookClient\Models\WebhookCall;
use Stripe\Event;
use Throwable;

class HandlePaymentIntentCancelled implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public WebhookCall $webhookCall;

    public function __construct(WebhookCall $webhookCall)
    {
        $this->webhookCall = $webhookCall;
    }

    public function handle(): void
    {
        try {
            $event = Event::constructFrom($this->webhookCall->payload);
            $paymentIntent = new PaymentIntent(intent: $event->data->object);
        } catch (Throwable $e) {
            $this->fail($e);
        }

        try {
            $order = App::make(FindOrderByIntent::class)($paymentIntent);
        } catch (Throwable $e) {
            $this->fail($e);
        }

        $paymentAdapter = StripeFacade::getFacadeRoot();

        OrderPaymentCanceled::dispatch($order, $paymentAdapter, $paymentIntent);
    }
}
