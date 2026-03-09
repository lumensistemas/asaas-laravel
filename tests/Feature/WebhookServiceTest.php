<?php

use Illuminate\Support\Facades\Http;
use LumenSistemas\Asaas\DTOs\Webhook\CreateWebhookData;
use LumenSistemas\Asaas\DTOs\Webhook\UpdateWebhookData;
use LumenSistemas\Asaas\DTOs\Webhook\WebhookData;
use LumenSistemas\Asaas\DTOs\Webhook\WebhookListResult;
use LumenSistemas\Asaas\Enums\Webhook\WebhookEvent;
use LumenSistemas\Asaas\Enums\Webhook\WebhookSendType;
use LumenSistemas\Asaas\Exceptions\AsaasApiException;
use LumenSistemas\Asaas\Services\WebhookService;

/**
 * @return array<string, mixed>
 */
function webhookPayload(array $overrides = []): array
{
    return array_merge([
        'id' => 'wbh_123',
        'name' => 'My Webhook',
        'url' => 'https://example.com/webhook',
        'email' => 'dev@example.com',
        'enabled' => true,
        'interrupted' => false,
        'hasAuthToken' => false,
        'sendType' => 'SEQUENTIALLY',
        'apiVersion' => 3,
        'penalizedRequestsCount' => 0,
        'events' => ['PAYMENT_RECEIVED', 'PAYMENT_CONFIRMED'],
    ], $overrides);
}

describe('WebhookService::list()', function (): void {
    it('returns a WebhookListResult with hydrated WebhookData items', function (): void {
        Http::fake(['*' => Http::response([
            'data' => [webhookPayload()],
            'hasMore' => false,
            'totalCount' => 1,
            'limit' => 10,
            'offset' => 0,
        ])]);

        $result = app(WebhookService::class)->list();

        expect($result)->toBeInstanceOf(WebhookListResult::class)
            ->and($result->totalCount)->toBe(1)
            ->and($result->hasMore)->toBeFalse()
            ->and($result->data)->toHaveCount(1)
            ->and($result->data[0])->toBeInstanceOf(WebhookData::class)
            ->and($result->data[0]->id)->toBe('wbh_123');

        Http::assertSent(
            fn ($r): bool => $r->method() === 'GET'
            && str_ends_with((string) $r->url(), '/v3/webhooks')
        );
    });
});

describe('WebhookService::find()', function (): void {
    it('returns a WebhookData for a valid ID', function (): void {
        Http::fake(['*' => Http::response(webhookPayload())]);

        $webhook = app(WebhookService::class)->find('wbh_123');

        expect($webhook)->toBeInstanceOf(WebhookData::class)
            ->and($webhook->id)->toBe('wbh_123')
            ->and($webhook->sendType)->toBe(WebhookSendType::Sequentially)
            ->and($webhook->events)->toContain(WebhookEvent::PaymentReceived)
            ->and($webhook->events)->toContain(WebhookEvent::PaymentConfirmed);

        Http::assertSent(
            fn ($r): bool => $r->method() === 'GET'
            && str_ends_with((string) $r->url(), '/v3/webhooks/wbh_123')
        );
    });
});

describe('WebhookService::create()', function (): void {
    it('POSTs and returns the created webhook', function (): void {
        Http::fake(['*' => Http::response(webhookPayload())]);

        $webhook = app(WebhookService::class)->create(new CreateWebhookData(
            url: 'https://example.com/webhook',
            events: [WebhookEvent::PaymentReceived, WebhookEvent::PaymentConfirmed],
            name: 'My Webhook',
            email: 'dev@example.com',
            sendType: WebhookSendType::Sequentially,
        ));

        expect($webhook)->toBeInstanceOf(WebhookData::class)
            ->and($webhook->id)->toBe('wbh_123');

        Http::assertSent(
            fn ($r): bool => $r->method() === 'POST'
            && str_ends_with((string) $r->url(), '/v3/webhooks')
            && $r->data()['url'] === 'https://example.com/webhook'
            && $r->data()['events'] === ['PAYMENT_RECEIVED', 'PAYMENT_CONFIRMED']
            && $r->data()['sendType'] === 'SEQUENTIALLY'
        );
    });

    it('throws AsaasApiException on a 4xx error', function (): void {
        Http::fake(['*' => Http::response(
            ['errors' => [['code' => 'invalid_url', 'description' => 'URL is invalid.']]],
            422
        )]);

        app(WebhookService::class)->create(new CreateWebhookData(
            url: 'https://example.com/webhook',
            events: [WebhookEvent::PaymentReceived],
            name: 'My Webhook',
            email: 'dev@example.com',
            sendType: WebhookSendType::Sequentially,
        ));
    })->throws(AsaasApiException::class);

    it('throws when url is empty', function (): void {
        new CreateWebhookData(url: '', events: [WebhookEvent::PaymentReceived], name: 'My Webhook', email: 'dev@example.com', sendType: WebhookSendType::Sequentially);
    })->throws(InvalidArgumentException::class, 'Webhook url cannot be empty.');

    it('throws when events list is empty', function (): void {
        new CreateWebhookData(url: 'https://example.com/webhook', events: [], name: 'My Webhook', email: 'dev@example.com', sendType: WebhookSendType::Sequentially);
    })->throws(InvalidArgumentException::class, 'Webhook events cannot be empty.');

    it('throws when name is empty', function (): void {
        new CreateWebhookData(url: 'https://example.com/webhook', events: [WebhookEvent::PaymentReceived], name: '', email: 'dev@example.com', sendType: WebhookSendType::Sequentially);
    })->throws(InvalidArgumentException::class, 'Webhook name cannot be empty.');
});

describe('WebhookService::update()', function (): void {
    it('PUTs and returns the updated webhook', function (): void {
        Http::fake(['*' => Http::response(webhookPayload(['enabled' => false]))]);

        $webhook = app(WebhookService::class)->update('wbh_123', new UpdateWebhookData(enabled: false));

        expect($webhook)->toBeInstanceOf(WebhookData::class)
            ->and($webhook->enabled)->toBeFalse();

        Http::assertSent(
            fn ($r): bool => $r->method() === 'PUT'
            && str_ends_with((string) $r->url(), '/v3/webhooks/wbh_123')
            && $r->data()['enabled'] === false
        );
    });

    it('serializes events enum array to strings', function (): void {
        Http::fake(['*' => Http::response(webhookPayload())]);

        app(WebhookService::class)->update('wbh_123', new UpdateWebhookData(
            events: [WebhookEvent::PaymentCreated],
        ));

        Http::assertSent(
            fn ($r): bool => $r->data()['events'] === ['PAYMENT_CREATED']
        );
    });
});

describe('WebhookService::delete()', function (): void {
    it('DELETEs the webhook and returns true', function (): void {
        Http::fake(['*' => Http::response(['deleted' => true, 'id' => 'wbh_123'])]);

        $result = app(WebhookService::class)->delete('wbh_123');

        expect($result)->toBeTrue();

        Http::assertSent(
            fn ($r): bool => $r->method() === 'DELETE'
            && str_ends_with((string) $r->url(), '/v3/webhooks/wbh_123')
        );
    });
});

describe('WebhookService::removeBackoff()', function (): void {
    it('POSTs to removeBackoff endpoint and returns WebhookData', function (): void {
        Http::fake(['*' => Http::response(webhookPayload(['penalizedRequestsCount' => 0]))]);

        $webhook = app(WebhookService::class)->removeBackoff('wbh_123');

        expect($webhook)->toBeInstanceOf(WebhookData::class)
            ->and($webhook->penalizedRequestsCount)->toBe(0);

        Http::assertSent(
            fn ($r): bool => $r->method() === 'POST'
            && str_ends_with((string) $r->url(), '/v3/webhooks/wbh_123/removeBackoff')
        );
    });
});
