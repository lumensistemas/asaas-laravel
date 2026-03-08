<?php

declare(strict_types=1);

namespace LumenSistemas\Asaas\DTOs\Webhook;

use InvalidArgumentException;
use LumenSistemas\Asaas\Enums\Webhook\WebhookEvent;
use LumenSistemas\Asaas\Enums\Webhook\WebhookSendType;

final readonly class CreateWebhookData
{
    /** @param WebhookEvent[] $events */
    public function __construct(
        public string $url,
        public array $events,
        public ?string $name = null,
        public ?string $email = null,
        public ?bool $enabled = null,
        public ?bool $interrupted = null,
        public ?int $apiVersion = null,
        public ?string $authToken = null,
        public ?WebhookSendType $sendType = null,
    ) {
        if (mb_trim($this->url) === '') {
            throw new InvalidArgumentException('Webhook url cannot be empty.');
        }

        if ($this->events === []) {
            throw new InvalidArgumentException('Webhook events cannot be empty.');
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'url' => $this->url,
            'events' => array_map(fn (WebhookEvent $e): string => $e->value, $this->events),
            'name' => $this->name,
            'email' => $this->email,
            'enabled' => $this->enabled,
            'interrupted' => $this->interrupted,
            'apiVersion' => $this->apiVersion,
            'authToken' => $this->authToken,
            'sendType' => $this->sendType?->value,
        ], fn (mixed $v): bool => $v !== null);
    }
}
