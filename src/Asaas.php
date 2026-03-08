<?php

namespace LumenSistemas\Asaas;

use LumenSistemas\Asaas\Contracts\AsaasClientInterface;
use LumenSistemas\Asaas\Services\CustomerService;

class Asaas
{
    public function __construct(
        private readonly AsaasClientInterface $client,
    ) {}

    /**
     * Return a new instance scoped to a specific API key.
     * Use this in multi-tenant applications to inject the tenant's key at runtime.
     */
    public function withApiKey(string $apiKey): static
    {
        return new static($this->client->withApiKey($apiKey));
    }

    public function customers(): CustomerService
    {
        return new CustomerService($this->client);
    }
}
