<?php

use LumenSistemas\Asaas\DTOs\Payment\PaymentData;
use LumenSistemas\Asaas\DTOs\Webhook\WebhookEventPayload;
use LumenSistemas\Asaas\Enums\Webhook\WebhookEvent;
use LumenSistemas\Asaas\Webhook\WebhookHandler;

/**
 * @return array<string, mixed>
 */
function paymentReceivedPayloadArray(): array
{
    return [
        'id' => 'evt_pay_received',
        'event' => 'PAYMENT_RECEIVED',
        'payment' => [
            'id' => 'pay_123',
            'customer' => 'cus_abc',
            'billingType' => 'PIX',
            'value' => 100.0,
            'netValue' => 97.5,
            'status' => 'RECEIVED',
            'dueDate' => '2026-04-01',
            'deleted' => false,
        ],
    ];
}

describe('WebhookHandler::on()', function (): void {
    it('calls the handler when the matching event is dispatched', function (): void {
        $called = false;
        $payload = WebhookEventPayload::fromArray(paymentReceivedPayloadArray());

        $handler = new WebhookHandler();
        $handler->on(WebhookEvent::PaymentReceived, function (WebhookEventPayload $p) use (&$called): void {
            $called = true;
        });

        $handler->handle($payload);

        expect($called)->toBeTrue();
    });

    it('does not call the handler for a different event', function (): void {
        $called = false;
        $payload = WebhookEventPayload::fromArray(paymentReceivedPayloadArray());

        $handler = new WebhookHandler();
        $handler->on(WebhookEvent::PaymentOverdue, function (WebhookEventPayload $p) use (&$called): void {
            $called = true;
        });

        $handler->handle($payload);

        expect($called)->toBeFalse();
    });

    it('calls all handlers registered for the same event', function (): void {
        $callCount = 0;
        $payload = WebhookEventPayload::fromArray(paymentReceivedPayloadArray());

        $handler = new WebhookHandler();
        $handler
            ->on(WebhookEvent::PaymentReceived, function () use (&$callCount): void { ++$callCount; })
            ->on(WebhookEvent::PaymentReceived, function () use (&$callCount): void { ++$callCount; });

        $handler->handle($payload);

        expect($callCount)->toBe(2);
    });

    it('passes a WebhookEventPayload with a typed PaymentData to the handler', function (): void {
        $receivedPayload = null;
        $payload = WebhookEventPayload::fromArray(paymentReceivedPayloadArray());

        $handler = new WebhookHandler();
        $handler->on(WebhookEvent::PaymentReceived, function (WebhookEventPayload $p) use (&$receivedPayload): void {
            $receivedPayload = $p;
        });

        $handler->handle($payload);

        expect($receivedPayload)->toBeInstanceOf(WebhookEventPayload::class)
            ->and($receivedPayload->payment)->toBeInstanceOf(PaymentData::class)
            ->and($receivedPayload->payment->id)->toBe('pay_123');
    });
});

describe('WebhookHandler::onAny()', function (): void {
    it('calls the handler for every dispatched event', function (): void {
        $events = [];
        $handler = new WebhookHandler();
        $handler->onAny(function (WebhookEventPayload $p) use (&$events): void {
            $events[] = $p->event;
        });

        $handler->handle(WebhookEventPayload::fromArray([
            'id' => 'evt_1',
            'event' => 'PAYMENT_RECEIVED',
            'payment' => [
                'id' => 'pay_1', 'customer' => 'cus_1', 'billingType' => 'PIX',
                'value' => 10.0, 'netValue' => 9.0, 'status' => 'RECEIVED',
                'dueDate' => '2026-04-01', 'deleted' => false,
            ],
        ]));

        $handler->handle(WebhookEventPayload::fromArray([
            'id' => 'evt_2',
            'event' => 'PAYMENT_OVERDUE',
        ]));

        expect($events)->toHaveCount(2)
            ->and($events[0])->toBe(WebhookEvent::PaymentReceived)
            ->and($events[1])->toBe(WebhookEvent::PaymentOverdue);
    });
});
