<?php

use LumenSistemas\Asaas\DTOs\Bill\BillData;
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

/**
 * @return array<string, mixed>
 */
function minimalBillArray(): array
{
    return [
        'id' => 'f1bce822-6f37-4905-8de8-f1af9f2f4bab',
        'status' => 'PAID',
        'value' => 29.90,
        'identificationField' => '03399.77779 29900.000000 04751.101017 1 81510000002990',
        'dueDate' => '2020-01-31',
        'scheduleDate' => '2020-01-31',
        'canBeCancelled' => false,
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

    it('deserializes a BILL_PAID event with bill data', function (): void {
        $payload = WebhookEventPayload::fromArray([
            'id' => 'evt_bill123',
            'event' => 'BILL_PAID',
            'bill' => minimalBillArray(),
        ]);

        expect($payload->id)->toBe('evt_bill123')
            ->and($payload->event)->toBe(WebhookEvent::BillPaid)
            ->and($payload->bill)->toBeInstanceOf(BillData::class)
            ->and($payload->bill->id)->toBe('f1bce822-6f37-4905-8de8-f1af9f2f4bab')
            ->and($payload->bill->status)->toBe('PAID')
            ->and($payload->bill->value)->toBe(29.90)
            ->and($payload->payment)->toBeNull();
    });

    it('sets payment and bill to null for non-payment/non-bill events', function (): void {
        $payload = WebhookEventPayload::fromArray([
            'id' => 'evt_def456',
            'event' => 'TRANSFER_DONE',
        ]);

        expect($payload->event)->toBe(WebhookEvent::TransferDone)
            ->and($payload->payment)->toBeNull()
            ->and($payload->bill)->toBeNull();
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

    it('parses JSON and returns a typed payload with bill', function (): void {
        $json = json_encode([
            'id' => 'evt_billjson',
            'event' => 'BILL_CREATED',
            'bill' => minimalBillArray(),
        ]);

        $payload = WebhookEventPayload::fromJson($json);

        expect($payload->id)->toBe('evt_billjson')
            ->and($payload->event)->toBe(WebhookEvent::BillCreated)
            ->and($payload->bill)->toBeInstanceOf(BillData::class)
            ->and($payload->payment)->toBeNull();
    });

    it('throws JsonException for invalid JSON', function (): void {
        WebhookEventPayload::fromJson('not-valid-json{');
    })->throws(JsonException::class);
});
