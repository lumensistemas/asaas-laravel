<?php

/**
 * Live integration tests for webhook event delivery.
 *
 * These tests require:
 *   1. A running webhook server:  composer webhook:serve
 *   2. A public URL via expose:   expose share http://localhost:9876
 *   3. Environment variables:
 *
 *   export ASAAS_TEST_API_KEY="your_sandbox_key"
 *   export ASAAS_WEBHOOK_URL="https://your-expose-subdomain.sharedwithexpose.com"
 *   export ASAAS_WEBHOOK_TOKEN="your_secret_token"   # optional — must match server env
 *   export ASAAS_WEBHOOK_SERVER_PORT=9876            # optional, default: 9876
 *
 * Run with:
 *   ./vendor/bin/pest --testsuite=Integration --filter=Webhook
 */

use LumenSistemas\Asaas\DTOs\Customer\CreateCustomerData;
use LumenSistemas\Asaas\DTOs\Payment\CreatePaymentData;
use LumenSistemas\Asaas\DTOs\Webhook\CreateWebhookData;
use LumenSistemas\Asaas\DTOs\Webhook\WebhookEventPayload;
use LumenSistemas\Asaas\Enums\Payment\PaymentBillingType;
use LumenSistemas\Asaas\Enums\Webhook\WebhookEvent;
use LumenSistemas\Asaas\Enums\Webhook\WebhookSendType;
use LumenSistemas\Asaas\Services\CustomerService;
use LumenSistemas\Asaas\Services\PaymentService;
use LumenSistemas\Asaas\Services\WebhookService;

// ──────────────────────────────────────────────────────────────
// Prerequisites check
// ──────────────────────────────────────────────────────────────

$apiKey = (string) env('ASAAS_TEST_API_KEY', '');
$webhookUrl = (string) env('ASAAS_WEBHOOK_URL', '');

if ($apiKey === '' || $webhookUrl === '') {
    test('Webhook live tests are skipped — set ASAAS_TEST_API_KEY and ASAAS_WEBHOOK_URL to run them')
        ->skip('ASAAS_TEST_API_KEY or ASAAS_WEBHOOK_URL is not set.');

    return;
}

// ──────────────────────────────────────────────────────────────
// Helpers
// ──────────────────────────────────────────────────────────────

function webhookServerUrl(): string
{
    $port = (int) env('ASAAS_WEBHOOK_SERVER_PORT', 9876);
    return sprintf('http://localhost:%d', $port);
}

/**
 * Poll GET /events on the local server until at least one event matching
 * $eventType is found, or $timeoutSeconds is reached.
 *
 * @return null|array<string, mixed> the first matching raw event array, or null on timeout
 */
function pollForEvent(string $eventType, int $timeoutSeconds = 30): ?array
{
    $deadline = time() + $timeoutSeconds;

    while (time() < $deadline) {
        $response = file_get_contents(webhookServerUrl().'/events');

        if ($response !== false) {
            /** @var null|array<int, array<string, mixed>> $events */
            $events = json_decode($response, true);

            if (is_array($events)) {
                foreach ($events as $event) {
                    if (isset($event['event']) && $event['event'] === $eventType) {
                        return $event;
                    }
                }
            }
        }

        sleep(2);
    }

    return null;
}

/** Clear all events stored on the local server. */
function clearServerEvents(): void
{
    $context = stream_context_create(['http' => ['method' => 'DELETE']]);
    file_get_contents(webhookServerUrl().'/events', false, $context);
}

function liveWebhookService(): WebhookService
{
    return app(WebhookService::class);
}

function liveWebhookCustomerService(): CustomerService
{
    return app(CustomerService::class);
}

function liveWebhookPaymentService(): PaymentService
{
    return app(PaymentService::class);
}

// ──────────────────────────────────────────────────────────────
// State shared across tests
// ──────────────────────────────────────────────────────────────

$suffix = mb_substr(md5((string) microtime(true)), 0, 8);
$webhookId = null;
$customerId = null;
$paymentId = null;

// ──────────────────────────────────────────────────────────────
// Tests
// ──────────────────────────────────────────────────────────────

describe('Webhook event delivery (live)', function () use ($webhookUrl, $suffix, &$webhookId, &$customerId, &$paymentId): void {
    it('registers a webhook pointing to the expose URL', function () use ($webhookUrl, $suffix, &$webhookId): void {
        clearServerEvents();

        $token = (string) env('ASAAS_WEBHOOK_TOKEN', '');

        $webhook = liveWebhookService()->create(new CreateWebhookData(
            url: mb_rtrim($webhookUrl, '/'),
            events: [
                WebhookEvent::PaymentCreated,
                WebhookEvent::PaymentReceived,
            ],
            name: 'Integration test webhook '.$suffix,
            email: sprintf('webhook-integration-%s@example.com', $suffix),
            sendType: WebhookSendType::Sequentially,
            enabled: true,
            interrupted: false,
            authToken: $token !== '' ? $token : null,
        ));

        expect($webhook->id)->not->toBeEmpty();

        $webhookId = $webhook->id;
    });

    it('creates a customer and a payment that triggers PAYMENT_CREATED', function () use ($suffix, &$customerId, &$paymentId): void {
        $customerId = liveWebhookCustomerService()->create(new CreateCustomerData(
            name: 'Webhook Integration Customer '.$suffix,
            cpfCnpj: '24971563792',
            email: sprintf('webhook-integration-%s@example.com', $suffix),
        ))->id;

        $payment = liveWebhookPaymentService()->create(new CreatePaymentData(
            customer: $customerId,
            billingType: PaymentBillingType::Pix,
            value: 10.00,
            dueDate: '2026-12-31',
            description: 'Webhook integration test '.$suffix,
        ));

        expect($payment->id)->not->toBeEmpty();

        $paymentId = $payment->id;
    });

    it('receives a PAYMENT_CREATED event on the local server within 30 seconds', function () use (&$paymentId): void {
        expect($paymentId)->not->toBeNull('payment creation test must run first');

        $raw = pollForEvent('PAYMENT_CREATED', 30);

        expect($raw)->not->toBeNull('timed out waiting for PAYMENT_CREATED event')
            ->and($raw)->toBeArray()
            ->and($raw)->toHaveKey('id')
            ->and($raw)->toHaveKey('event')
            ->and($raw['event'])->toBe('PAYMENT_CREATED')
            ->and($raw)->toHaveKey('payment');

        // Parse through the DTO to assert it round-trips correctly
        $payload = WebhookEventPayload::fromArray($raw);

        expect($payload->event)->toBe(WebhookEvent::PaymentCreated)
            ->and($payload->payment)->not->toBeNull()
            ->and($payload->payment->id)->toBe($paymentId);
    });
})->group('webhook');

// ──────────────────────────────────────────────────────────────
// Cleanup
// ──────────────────────────────────────────────────────────────

afterAll(function () use (&$webhookId, &$paymentId, &$customerId): void {
    clearServerEvents();

    if ($webhookId !== null) {
        try {
            liveWebhookService()->delete($webhookId);
        } catch (Throwable) {
        }
    }

    if ($paymentId !== null) {
        try {
            liveWebhookPaymentService()->delete($paymentId);
        } catch (Throwable) {
        }
    }

    if ($customerId !== null) {
        try {
            liveWebhookCustomerService()->delete($customerId);
        } catch (Throwable) {
        }
    }
});
