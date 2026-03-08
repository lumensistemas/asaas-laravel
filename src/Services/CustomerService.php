<?php

namespace LumenSistemas\Asaas\Services;

use LumenSistemas\Asaas\Contracts\AsaasClientInterface;
use LumenSistemas\Asaas\DTOs\Customer\CreateCustomerData;
use LumenSistemas\Asaas\DTOs\Customer\CustomerData;
use LumenSistemas\Asaas\DTOs\Customer\CustomerListFilters;
use LumenSistemas\Asaas\DTOs\Customer\CustomerListResult;
use LumenSistemas\Asaas\DTOs\Customer\UpdateCustomerData;

class CustomerService
{
    public function __construct(
        private readonly AsaasClientInterface $client,
    ) {}

    public function list(?CustomerListFilters $filters = null): CustomerListResult
    {
        $query = $filters ? $filters->toArray() : [];
        $response = $this->client->get('/v3/customers', $query);

        return CustomerListResult::fromArray($response);
    }

    public function find(string $id): CustomerData
    {
        $response = $this->client->get("/v3/customers/{$id}");

        return CustomerData::fromArray($response);
    }

    public function create(CreateCustomerData $data): CustomerData
    {
        $response = $this->client->post('/v3/customers', $data->toArray());

        return CustomerData::fromArray($response);
    }

    public function update(string $id, UpdateCustomerData $data): CustomerData
    {
        $response = $this->client->put("/v3/customers/{$id}", $data->toArray());

        return CustomerData::fromArray($response);
    }

    public function delete(string $id): bool
    {
        $response = $this->client->delete("/v3/customers/{$id}");

        return $response['deleted'] ?? false;
    }

    public function restore(string $id): CustomerData
    {
        $response = $this->client->post("/v3/customers/{$id}/restore");

        return CustomerData::fromArray($response);
    }
}
