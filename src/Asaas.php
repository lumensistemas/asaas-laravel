<?php

declare(strict_types=1);

namespace LumenSistemas\Asaas;

use LumenSistemas\Asaas\Contracts\AsaasClientInterface;
use LumenSistemas\Asaas\Services\BillService;
use LumenSistemas\Asaas\Services\CustomerService;
use LumenSistemas\Asaas\Services\PaymentService;
use LumenSistemas\Asaas\Services\WebhookService;

final readonly class Asaas
{
    public function __construct(
        private AsaasClientInterface $client,
    ) {}

    /**
     * Return a new instance scoped to a specific API key.
     * Use this in multi-tenant applications to inject the tenant's key at runtime.
     */
    public function withApiKey(string $apiKey): static
    {
        return new self($this->client->withApiKey($apiKey));
    }

    public function customers(): CustomerService
    {
        return new CustomerService($this->client);
    }

    public function payments(): PaymentService
    {
        return new PaymentService($this->client);
    }

    public function webhooks(): WebhookService
    {
        return new WebhookService($this->client);
    }

    public function bills(): BillService
    {
        return new BillService($this->client);
    }
}
