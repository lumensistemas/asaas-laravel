<?php

declare(strict_types=1);

namespace LumenSistemas\Asaas\Webhook;

use Illuminate\Support\Collection;
use LumenSistemas\Asaas\DTOs\Webhook\WebhookEventPayload;
use LumenSistemas\Asaas\Enums\Webhook\WebhookEvent;

class WebhookHandler
{
    /**
     * @var Collection<string, Collection<int, callable(WebhookEventPayload): void>>
     */
    private readonly Collection $handlers;

    /**
     * @var Collection<int, callable(WebhookEventPayload): void>
     */
    private readonly Collection $anyHandlers;

    public function __construct()
    {
        $this->handlers = new Collection();
        $this->anyHandlers = new Collection();
    }

    public function on(WebhookEvent $event, callable $handler): self
    {
        if (!$this->handlers->has($event->value)) {
            $this->handlers->put($event->value, new Collection());
        }

        /** @var Collection<int, callable(WebhookEventPayload): void> $eventHandlers */
        $eventHandlers = $this->handlers->get($event->value);
        $eventHandlers->push($handler);

        return $this;
    }

    public function onAny(callable $handler): self
    {
        $this->anyHandlers->push($handler);

        return $this;
    }

    public function handle(WebhookEventPayload $payload): void
    {
        $event = $payload->event->value;

        if ($this->handlers->has($event)) {
            /** @var Collection<int, callable(WebhookEventPayload): void> $eventHandlers */
            $eventHandlers = $this->handlers->get($event);

            foreach ($eventHandlers as $eventHandler) {
                $eventHandler($payload);
            }
        }

        foreach ($this->anyHandlers as $anyHandler) {
            $anyHandler($payload);
        }
    }
}
