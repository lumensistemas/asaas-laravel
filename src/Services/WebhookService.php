<?php

declare(strict_types=1);

namespace LumenSistemas\Asaas\Services;

use LumenSistemas\Asaas\Contracts\AsaasClientInterface;
use LumenSistemas\Asaas\DTOs\Webhook\CreateWebhookData;
use LumenSistemas\Asaas\DTOs\Webhook\UpdateWebhookData;
use LumenSistemas\Asaas\DTOs\Webhook\WebhookData;
use LumenSistemas\Asaas\DTOs\Webhook\WebhookListResult;

/**
 * @phpstan-type WebhookArray array{id: string, name: string, url: string, enabled: bool, interrupted: bool, hasAuthToken: bool, sendType: string, apiVersion: int, penalizedRequestsCount: int, events: list<string>, email?: null|string}
 */
class WebhookService
{
    public function __construct(
        private readonly AsaasClientInterface $client,
    ) {}

    public function list(): WebhookListResult
    {
        /** @var array{data?: array<int, WebhookArray>, hasMore?: bool, totalCount?: int, limit?: int, offset?: int} $response */
        $response = $this->client->get('/v3/webhooks');

        return WebhookListResult::fromArray($response);
    }

    public function find(string $id): WebhookData
    {
        /** @var WebhookArray $response */
        $response = $this->client->get('/v3/webhooks/'.$id);

        return WebhookData::fromArray($response);
    }

    public function create(CreateWebhookData $data): WebhookData
    {
        /** @var WebhookArray $response */
        $response = $this->client->post('/v3/webhooks', $data->toArray());

        return WebhookData::fromArray($response);
    }

    public function update(string $id, UpdateWebhookData $data): WebhookData
    {
        /** @var WebhookArray $response */
        $response = $this->client->put('/v3/webhooks/'.$id, $data->toArray());

        return WebhookData::fromArray($response);
    }

    public function delete(string $id): bool
    {
        /** @var array{deleted?: bool} $response */
        $response = $this->client->delete('/v3/webhooks/'.$id);

        return $response['deleted'] ?? false;
    }

    public function removeBackoff(string $id): WebhookData
    {
        /** @var WebhookArray $response */
        $response = $this->client->post(sprintf('/v3/webhooks/%s/removeBackoff', $id));

        return WebhookData::fromArray($response);
    }
}
