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
        public string $name,
        public string $email,
        public WebhookSendType $sendType,
        public bool $enabled = true,
        public bool $interrupted = false,
        public ?int $apiVersion = null,
        public ?string $authToken = null,
    ) {
        if (mb_trim($this->url) === '') {
            throw new InvalidArgumentException('Webhook url cannot be empty.');
        }

        if ($this->events === []) {
            throw new InvalidArgumentException('Webhook events cannot be empty.');
        }

        if (mb_trim($this->name) === '') {
            throw new InvalidArgumentException('Webhook name cannot be empty.');
        }

        if (mb_trim($this->email) === '') {
            throw new InvalidArgumentException('Webhook email cannot be empty.');
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
            'sendType' => $this->sendType->value,
            'enabled' => $this->enabled,
            'interrupted' => $this->interrupted,
            'apiVersion' => $this->apiVersion,
            'authToken' => $this->authToken,
        ], fn (mixed $v): bool => $v !== null);
    }
}
