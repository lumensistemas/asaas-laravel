<?php

declare(strict_types=1);

namespace LumenSistemas\Asaas\DTOs\Webhook;

use LumenSistemas\Asaas\Enums\Webhook\WebhookEvent;
use LumenSistemas\Asaas\Enums\Webhook\WebhookSendType;

final readonly class UpdateWebhookData
{
    /** @param null|WebhookEvent[] $events */
    public function __construct(
        public ?string $url = null,
        public ?string $name = null,
        public ?string $email = null,
        public ?bool $enabled = null,
        public ?bool $interrupted = null,
        public ?int $apiVersion = null,
        public ?string $authToken = null,
        public ?WebhookSendType $sendType = null,
        public ?array $events = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'url' => $this->url,
            'name' => $this->name,
            'email' => $this->email,
            'enabled' => $this->enabled,
            'interrupted' => $this->interrupted,
            'apiVersion' => $this->apiVersion,
            'authToken' => $this->authToken,
            'sendType' => $this->sendType?->value,
            'events' => $this->events !== null
                ? array_map(fn (WebhookEvent $e): string => $e->value, $this->events)
                : null,
        ], fn (mixed $v): bool => $v !== null);
    }
}
