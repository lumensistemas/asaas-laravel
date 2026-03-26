<?php

declare(strict_types=1);

namespace LumenSistemas\Asaas\DTOs\Webhook;

use LumenSistemas\Asaas\Enums\Webhook\WebhookEvent;
use LumenSistemas\Asaas\Enums\Webhook\WebhookSendType;

final readonly class WebhookData
{
    /** @param WebhookEvent[] $events */
    public function __construct(
        public string $id,
        public string $name,
        public string $url,
        public bool $enabled,
        public bool $interrupted,
        public bool $hasAuthToken,
        public WebhookSendType $sendType,
        public int $apiVersion,
        public int $penalizedRequestsCount,
        public array $events,
        public ?string $email = null,
    ) {}

    /**
     * @param array{
     *     id: string,
     *     name: string,
     *     url: string,
     *     enabled: bool,
     *     interrupted: bool,
     *     hasAuthToken: bool,
     *     sendType: string,
     *     apiVersion: int,
     *     penalizedRequestsCount: int,
     *     events: list<string>,
     *     email?: null|string,
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            url: $data['url'],
            enabled: $data['enabled'],
            interrupted: $data['interrupted'],
            hasAuthToken: $data['hasAuthToken'],
            sendType: WebhookSendType::from($data['sendType']),
            apiVersion: $data['apiVersion'],
            penalizedRequestsCount: $data['penalizedRequestsCount'],
            events: array_map(WebhookEvent::from(...), $data['events']),
            email: $data['email'] ?? null,
        );
    }
}
