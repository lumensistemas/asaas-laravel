<?php

use LumenSistemas\Asaas\DTOs\Customer\CreateCustomerData;
use LumenSistemas\Asaas\DTOs\Customer\CustomerData;
use LumenSistemas\Asaas\DTOs\Customer\CustomerListFilters;
use LumenSistemas\Asaas\DTOs\Customer\UpdateCustomerData;

describe('CustomerData', function (): void {
    it('deserializes from a full API response', function (): void {
        $data = CustomerData::fromArray([
            'id' => 'cus_123',
            'name' => 'John Doe',
            'cpfCnpj' => '24971563792',
            'personType' => 'FISICA',
            'deleted' => false,
            'dateCreated' => '2024-07-12',
            'email' => 'john@example.com',
            'notificationDisabled' => true,
            'foreignCustomer' => false,
        ]);

        expect($data->id)->toBe('cus_123')
            ->and($data->name)->toBe('John Doe')
            ->and($data->cpfCnpj)->toBe('24971563792')
            ->and($data->personType)->toBe('FISICA')
            ->and($data->deleted)->toBeFalse()
            ->and($data->email)->toBe('john@example.com')
            ->and($data->notificationDisabled)->toBeTrue();
    });

    it('defaults optional fields to null or false', function (): void {
        $data = CustomerData::fromArray([
            'id' => 'cus_456',
            'name' => 'Jane',
            'cpfCnpj' => '11122233344',
            'deleted' => false,
        ]);

        expect($data->email)->toBeNull()
            ->and($data->phone)->toBeNull()
            ->and($data->personType)->toBe('FISICA')
            ->and($data->notificationDisabled)->toBeFalse()
            ->and($data->foreignCustomer)->toBeFalse();
    });
});

describe('CreateCustomerData', function (): void {
    it('strips null fields from toArray()', function (): void {
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

    it('includes notificationDisabled when true', function (): void {
        $dto = new CreateCustomerData(
            name: 'John',
            cpfCnpj: '24971563792',
            notificationDisabled: true,
        );

        expect($dto->toArray())->toHaveKey('notificationDisabled', true);
    });

    it('excludes notificationDisabled when false', function (): void {
        $dto = new CreateCustomerData(name: 'John', cpfCnpj: '24971563792');

        expect($dto->toArray())->not->toHaveKey('notificationDisabled');
    });

    it('throws when name is empty', function (): void {
        new CreateCustomerData(name: '', cpfCnpj: '24971563792');
    })->throws(InvalidArgumentException::class, 'Customer name cannot be empty.');

    it('throws when cpfCnpj is empty', function (): void {
        new CreateCustomerData(name: 'John', cpfCnpj: '');
    })->throws(InvalidArgumentException::class, 'Customer cpfCnpj cannot be empty.');
});

describe('UpdateCustomerData', function (): void {
    it('only includes non-null fields', function (): void {
        $dto = new UpdateCustomerData(name: 'Updated Name');

        $payload = $dto->toArray();

        expect($payload)->toBe(['name' => 'Updated Name']);
    });
});

describe('CustomerListFilters', function (): void {
    it('always includes offset and limit', function (): void {
        $filters = new CustomerListFilters();

        expect($filters->toArray())->toMatchArray(['offset' => 0, 'limit' => 10]);
    });

    it('caps limit at 100', function (): void {
        $filters = new CustomerListFilters(limit: 200);

        expect($filters->toArray()['limit'])->toBe(100);
    });

    it('includes optional filters when set', function (): void {
        $filters = new CustomerListFilters(name: 'John', cpfCnpj: '24971563792');

        expect($filters->toArray())
            ->toHaveKey('name', 'John')
            ->toHaveKey('cpfCnpj', '24971563792')
            ->not->toHaveKey('email');
    });
});
