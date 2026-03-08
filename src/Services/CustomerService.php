<?php

declare(strict_types=1);

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
        $query = $filters instanceof CustomerListFilters ? $filters->toArray() : [];
        /** @var array{data?: array<int, array{id: string, name: string, cpfCnpj: string, personType?: string, deleted?: bool, dateCreated?: null|string, email?: null|string, phone?: null|string, mobilePhone?: null|string, address?: null|string, addressNumber?: null|string, complement?: null|string, province?: null|string, city?: null|int, cityName?: null|string, state?: null|string, country?: null|string, postalCode?: null|string, additionalEmails?: null|string, externalReference?: null|string, notificationDisabled?: bool, observations?: null|string, foreignCustomer?: bool, groupName?: null|string, company?: null|string}>, hasMore?: bool, totalCount?: int, limit?: int, offset?: int} $response */
        $response = $this->client->get('/v3/customers', $query);

        return CustomerListResult::fromArray($response);
    }

    public function find(string $id): CustomerData
    {
        /** @var array{id: string, name: string, cpfCnpj: string, personType?: string, deleted?: bool, dateCreated?: null|string, email?: null|string, phone?: null|string, mobilePhone?: null|string, address?: null|string, addressNumber?: null|string, complement?: null|string, province?: null|string, city?: null|int, cityName?: null|string, state?: null|string, country?: null|string, postalCode?: null|string, additionalEmails?: null|string, externalReference?: null|string, notificationDisabled?: bool, observations?: null|string, foreignCustomer?: bool, groupName?: null|string, company?: null|string} $response */
        $response = $this->client->get('/v3/customers/'.$id);

        return CustomerData::fromArray($response);
    }

    public function create(CreateCustomerData $data): CustomerData
    {
        /** @var array{id: string, name: string, cpfCnpj: string, personType?: string, deleted?: bool, dateCreated?: null|string, email?: null|string, phone?: null|string, mobilePhone?: null|string, address?: null|string, addressNumber?: null|string, complement?: null|string, province?: null|string, city?: null|int, cityName?: null|string, state?: null|string, country?: null|string, postalCode?: null|string, additionalEmails?: null|string, externalReference?: null|string, notificationDisabled?: bool, observations?: null|string, foreignCustomer?: bool, groupName?: null|string, company?: null|string} $response */
        $response = $this->client->post('/v3/customers', $data->toArray());

        return CustomerData::fromArray($response);
    }

    public function update(string $id, UpdateCustomerData $data): CustomerData
    {
        /** @var array{id: string, name: string, cpfCnpj: string, personType?: string, deleted?: bool, dateCreated?: null|string, email?: null|string, phone?: null|string, mobilePhone?: null|string, address?: null|string, addressNumber?: null|string, complement?: null|string, province?: null|string, city?: null|int, cityName?: null|string, state?: null|string, country?: null|string, postalCode?: null|string, additionalEmails?: null|string, externalReference?: null|string, notificationDisabled?: bool, observations?: null|string, foreignCustomer?: bool, groupName?: null|string, company?: null|string} $response */
        $response = $this->client->put('/v3/customers/'.$id, $data->toArray());

        return CustomerData::fromArray($response);
    }

    public function delete(string $id): bool
    {
        /** @var array{deleted?: bool} $response */
        $response = $this->client->delete('/v3/customers/'.$id);

        return $response['deleted'] ?? false;
    }

    public function restore(string $id): CustomerData
    {
        /** @var array{id: string, name: string, cpfCnpj: string, personType?: string, deleted?: bool, dateCreated?: null|string, email?: null|string, phone?: null|string, mobilePhone?: null|string, address?: null|string, addressNumber?: null|string, complement?: null|string, province?: null|string, city?: null|int, cityName?: null|string, state?: null|string, country?: null|string, postalCode?: null|string, additionalEmails?: null|string, externalReference?: null|string, notificationDisabled?: bool, observations?: null|string, foreignCustomer?: bool, groupName?: null|string, company?: null|string} $response */
        $response = $this->client->post(sprintf('/v3/customers/%s/restore', $id));

        return CustomerData::fromArray($response);
    }
}
