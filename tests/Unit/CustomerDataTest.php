<?php

use LumenSistemas\Asaas\DTOs\Customer\CreateCustomerData;
use LumenSistemas\Asaas\DTOs\Customer\CustomerData;
use LumenSistemas\Asaas\DTOs\Customer\CustomerListFilters;
use LumenSistemas\Asaas\DTOs\Customer\UpdateCustomerData;

describe('CustomerData', function () {
    it('deserializes from a full API response', function () {
        $data = CustomerData::fromArray([
            'id'                  => 'cus_123',
            'name'                => 'John Doe',
            'cpfCnpj'             => '24971563792',
            'personType'          => 'FISICA',
            'deleted'             => false,
            'dateCreated'         => '2024-07-12',
            'email'               => 'john@example.com',
            'notificationDisabled' => true,
            'foreignCustomer'     => false,
        ]);

        expect($data->id)->toBe('cus_123')
            ->and($data->name)->toBe('John Doe')
            ->and($data->cpfCnpj)->toBe('24971563792')
            ->and($data->personType)->toBe('FISICA')
            ->and($data->deleted)->toBeFalse()
            ->and($data->email)->toBe('john@example.com')
            ->and($data->notificationDisabled)->toBeTrue();
    });

    it('defaults optional fields to null or false', function () {
        $data = CustomerData::fromArray([
            'id'       => 'cus_456',
            'name'     => 'Jane',
            'cpfCnpj'  => '11122233344',
            'deleted'  => false,
        ]);

        expect($data->email)->toBeNull()
            ->and($data->phone)->toBeNull()
            ->and($data->personType)->toBe('FISICA')
            ->and($data->notificationDisabled)->toBeFalse()
            ->and($data->foreignCustomer)->toBeFalse();
    });
});

describe('CreateCustomerData', function () {
    it('strips null fields from toArray()', function () {
        $dto = new CreateCustomerData(
            name: 'John Doe',
            cpfCnpj: '24971563792',
            email: 'john@example.com',
        );

        $payload = $dto->toArray();

        expect($payload)
            ->toHaveKeys(['name', 'cpfCnpj', 'email'])
            ->not->toHaveKey('phone')
            ->not->toHaveKey('address');
    });

    it('includes notificationDisabled when true', function () {
        $dto = new CreateCustomerData(
            name: 'John',
            cpfCnpj: '24971563792',
            notificationDisabled: true,
        );

        expect($dto->toArray())->toHaveKey('notificationDisabled', true);
    });

    it('excludes notificationDisabled when false', function () {
        $dto = new CreateCustomerData(name: 'John', cpfCnpj: '24971563792');

        expect($dto->toArray())->not->toHaveKey('notificationDisabled');
    });
});

describe('UpdateCustomerData', function () {
    it('only includes non-null fields', function () {
        $dto = new UpdateCustomerData(name: 'Updated Name');

        $payload = $dto->toArray();

        expect($payload)->toBe(['name' => 'Updated Name']);
    });
});

describe('CustomerListFilters', function () {
    it('always includes offset and limit', function () {
        $filters = new CustomerListFilters();

        expect($filters->toArray())->toMatchArray(['offset' => 0, 'limit' => 10]);
    });

    it('caps limit at 100', function () {
        $filters = new CustomerListFilters(limit: 200);

        expect($filters->toArray()['limit'])->toBe(100);
    });

    it('includes optional filters when set', function () {
        $filters = new CustomerListFilters(name: 'John', cpfCnpj: '24971563792');

        expect($filters->toArray())
            ->toHaveKey('name', 'John')
            ->toHaveKey('cpfCnpj', '24971563792')
            ->not->toHaveKey('email');
    });
});
