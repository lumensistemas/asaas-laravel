<?php

use LumenSistemas\Asaas\DTOs\Payment\PaymentData;
use LumenSistemas\Asaas\DTOs\Webhook\WebhookEventPayload;
use LumenSistemas\Asaas\Enums\Webhook\WebhookEvent;

/**
 * @return array<string, mixed>
 */
function minimalPaymentArray(): array
{
    return [
        'id' => 'pay_123',
        'customer' => 'cus_abc',
        'billingType' => 'PIX',
        'value' => 100.0,
        'netValue' => 97.5,
        'status' => 'PENDING',
        'dueDate' => '2026-04-01',
        'deleted' => false,
    ];
}

describe('WebhookEventPayload::fromArray()', function (): void {
    it('deserializes a PAYMENT_RECEIVED event with payment data', function (): void {
        $payload = WebhookEventPayload::fromArray([
            'id' => 'evt_abc123',
            'event' => 'PAYMENT_RECEIVED',
            'payment' => minimalPaymentArray(),
        ]);

        expect($payload->id)->toBe('evt_abc123')
            ->and($payload->event)->toBe(WebhookEvent::PaymentReceived)
            ->and($payload->payment)->toBeInstanceOf(PaymentData::class)
            ->and($payload->payment->id)->toBe('pay_123');
    });

    it('sets payment to null for non-payment events', function (): void {
        $payload = WebhookEventPayload::fromArray([
            'id' => 'evt_def456',
            'event' => 'TRANSFER_DONE',
        ]);

        expect($payload->event)->toBe(WebhookEvent::TransferDone)
            ->and($payload->payment)->toBeNull();
    });

    it('throws ValueError for an unknown event string', function (): void {
        WebhookEventPayload::fromArray([
            'id' => 'evt_xyz',
            'event' => 'UNKNOWN_EVENT_TYPE',
        ]);
    })->throws(ValueError::class);
});

describe('WebhookEventPayload::fromJson()', function (): void {
    it('parses JSON and returns a typed payload with payment', function (): void {
        $json = json_encode([
            'id' => 'evt_json123',
            'event' => 'PAYMENT_CONFIRMED',
            'payment' => minimalPaymentArray(),
        ]);

        $payload = WebhookEventPayload::fromJson($json);

        expect($payload->id)->toBe('evt_json123')
            ->and($payload->event)->toBe(WebhookEvent::PaymentConfirmed)
            ->and($payload->payment)->toBeInstanceOf(PaymentData::class);
    });

    it('throws JsonException for invalid JSON', function (): void {
        WebhookEventPayload::fromJson('not-valid-json{');
    })->throws(JsonException::class);
});
