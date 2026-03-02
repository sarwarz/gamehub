<?php

namespace App\Jobs;

use App\Models\WebhookEvent;
use App\Models\PaymentMethod;
use App\Models\CheckoutSession;
use App\PaymentGateways\GatewayFactory;
use App\Services\CheckoutService;
use App\Services\WalletService;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProcessWebhookJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public array $backoff = [30, 120, 600];
    public int $maxExceptions = 2;

    public function __construct(public int $webhookEventId) {}

    public function handle(): void
    {
        $event = WebhookEvent::find($this->webhookEventId);

        if (!$event || $event->status === 'processed') {
            return;
        }

        $event->markProcessing();

        try {
            $method = PaymentMethod::where('code', $event->gateway)
                ->where('is_enabled', true)
                ->first();

            if (!$method) {
                $event->markFailed('Payment method not found or disabled.');
                return;
            }

            $factory = app(GatewayFactory::class);

            if (!$factory->supports($event->gateway)) {
                $event->markFailed("Unsupported gateway: {$event->gateway}");
                return;
            }

            $gateway = $factory->make($event->gateway);

            $fakeRequest = Request::create(
                uri: '/webhook',
                method: 'POST',
                content: json_encode($event->payload),
            );

            $fakeRequest->headers->set('Content-Type', 'application/json');
            foreach ($event->headers ?? [] as $key => $value) {
                $fakeRequest->headers->set($key, $value);
            }

            $fakeRequest->merge($event->payload);

            $payload = $gateway->verifyWebhook(
                $fakeRequest,
                $method->config ?? [],
                $method->mode ?? 'live',
            );

            if (!$payload->success) {
                $event->markFailed('Webhook verification failed.');
                return;
            }

            if ($payload->isWalletDeposit()) {
                $this->handleWalletDeposit($payload, $event);
                return;
            }

            if (!$payload->trx) {
                $event->markFailed('Missing transaction reference in webhook payload.');
                return;
            }

            $this->handleOrderPayment($payload, $event);

        } catch (\Throwable $e) {
            Log::error('ProcessWebhookJob failed', [
                'event_id' => $this->webhookEventId,
                'error'    => $e->getMessage(),
            ]);

            $event->markFailed($e->getMessage());
            throw $e;
        }
    }

    protected function handleOrderPayment($payload, WebhookEvent $event): void
    {
        $session = CheckoutSession::where('trx', $payload->trx)
            ->whereIn('status', ['paying', 'completed'])
            ->first();

        if (!$session) {
            $event->markFailed("Checkout session not found for trx: {$payload->trx}");
            return;
        }

        if ($session->status === 'completed') {
            Log::info('Session already completed, skipping', ['trx' => $payload->trx]);
            $event->markProcessed();
            return;
        }

        app(CheckoutService::class)->fulfillSession($session, $payload);

        $event->markProcessed();
    }

    protected function handleWalletDeposit($payload, WebhookEvent $event): void
    {
        try {
            $walletService = app(WalletService::class);

            $walletService->confirmDeposit(
                $payload->walletTransactionId,
                $payload->gatewayReference ?? $event->gateway
            );

            Log::info('Wallet deposit confirmed via webhook', [
                'gateway'        => $event->gateway,
                'transaction_id' => $payload->walletTransactionId,
            ]);

            $event->markProcessed();
        } catch (\Throwable $e) {
            $event->markFailed("Wallet deposit error: {$e->getMessage()}");
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::critical('ProcessWebhookJob permanently failed', [
            'event_id' => $this->webhookEventId,
            'error'    => $exception->getMessage(),
        ]);
    }
}
