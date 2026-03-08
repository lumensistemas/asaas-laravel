<?php

use Illuminate\Support\Facades\Http;
use LumenSistemas\Asaas\DTOs\Customer\CreateCustomerData;
use LumenSistemas\Asaas\DTOs\Customer\CustomerData;
use LumenSistemas\Asaas\DTOs\Customer\CustomerListFilters;
use LumenSistemas\Asaas\DTOs\Customer\CustomerListResult;
use LumenSistemas\Asaas\DTOs\Customer\UpdateCustomerData;
use LumenSistemas\Asaas\Exceptions\AsaasApiException;
use LumenSistemas\Asaas\Facades\Asaas;

function customerPayload(array $overrides = []): array
{
    return array_merge([
        'object'               => 'customer',
        'id'                   => 'cus_000005401844',
        'dateCreated'          => '2024-07-12',
        'name'                 => 'John Doe',
        'cpfCnpj'              => '24971563792',
        'email'                => 'john.doe@example.com',
        'personType'           => 'FISICA',
        'deleted'              => false,
        'notificationDisabled' => false,
        'foreignCustomer'      => false,
    ], $overrides);
}

describe('CustomerService::list()', function () {
    it('returns a CustomerListResult with data', function () {
        Http::fake(['*' => Http::response([
            'object'     => 'list',
            'hasMore'    => false,
            'totalCount' => 1,
            'limit'      => 10,
            'offset'     => 0,
            'data'       => [customerPayload()],
        ])]);

        $result = Asaas::customers()->list();

        expect($result)->toBeInstanceOf(CustomerListResult::class)
            ->and($result->totalCount)->toBe(1)
            ->and($result->hasMore)->toBeFalse()
            ->and($result->data)->toHaveCount(1)
            ->and($result->data[0])->toBeInstanceOf(CustomerData::class)
            ->and($result->data[0]->id)->toBe('cus_000005401844');
    });

    it('forwards filter query parameters', function () {
        Http::fake(['*' => Http::response([
            'object' => 'list', 'hasMore' => false,
            'totalCount' => 0, 'limit' => 10, 'offset' => 0, 'data' => [],
        ])]);

        Asaas::customers()->list(new CustomerListFilters(name: 'John', cpfCnpj: '24971563792'));

        Http::assertSent(fn ($request) =>
            $request->url() === 'https://api-sandbox.asaas.com/v3/customers?offset=0&limit=10&name=John&cpfCnpj=24971563792'
        );
    });
});

describe('CustomerService::find()', function () {
    it('returns a CustomerData for a valid ID', function () {
        Http::fake(['*' => Http::response(customerPayload())]);

        $customer = Asaas::customers()->find('cus_000005401844');

        expect($customer)->toBeInstanceOf(CustomerData::class)
            ->and($customer->id)->toBe('cus_000005401844')
            ->and($customer->name)->toBe('John Doe');

        Http::assertSent(fn ($request) =>
            str_ends_with($request->url(), '/v3/customers/cus_000005401844')
            && $request->method() === 'GET'
        );
    });
});

describe('CustomerService::create()', function () {
    it('POSTs and returns the created customer', function () {
        Http::fake(['*' => Http::response(customerPayload())]);

        $dto = new CreateCustomerData(name: 'John Doe', cpfCnpj: '24971563792', email: 'john.doe@example.com');
        $customer = Asaas::customers()->create($dto);

        expect($customer)->toBeInstanceOf(CustomerData::class)
            ->and($customer->name)->toBe('John Doe');

        Http::assertSent(fn ($request) =>
            str_ends_with($request->url(), '/v3/customers')
            && $request->method() === 'POST'
            && $request->data()['name'] === 'John Doe'
            && $request->data()['cpfCnpj'] === '24971563792'
        );
    });

    it('throws AsaasApiException when the API rejects the payload', function () {
        Http::fake(['*' => Http::response(
            ['errors' => [['code' => 'invalid_cpfCnpj', 'description' => 'CPF/CNPJ inválido']]],
            400
        )]);

        Asaas::customers()->create(new CreateCustomerData(name: 'X', cpfCnpj: '000'));
    })->throws(AsaasApiException::class, 'CPF/CNPJ inválido');
});

describe('CustomerService::update()', function () {
    it('PUTs and returns the updated customer', function () {
        Http::fake(['*' => Http::response(customerPayload(['name' => 'Jane Doe']))]);

        $customer = Asaas::customers()->update('cus_000005401844', new UpdateCustomerData(name: 'Jane Doe'));

        expect($customer->name)->toBe('Jane Doe');

        Http::assertSent(fn ($request) =>
            str_ends_with($request->url(), '/v3/customers/cus_000005401844')
            && $request->method() === 'PUT'
            && $request->data()['name'] === 'Jane Doe'
        );
    });
});

describe('CustomerService::delete()', function () {
    it('DELETEs the customer and returns true', function () {
        Http::fake(['*' => Http::response(['deleted' => true, 'id' => 'cus_000005401844'])]);

        $result = Asaas::customers()->delete('cus_000005401844');

        expect($result)->toBeTrue();

        Http::assertSent(fn ($request) =>
            str_ends_with($request->url(), '/v3/customers/cus_000005401844')
            && $request->method() === 'DELETE'
        );
    });
});

describe('CustomerService::restore()', function () {
    it('POSTs to the restore endpoint and returns the customer', function () {
        Http::fake(['*' => Http::response(customerPayload(['deleted' => false]))]);

        $customer = Asaas::customers()->restore('cus_000005401844');

        expect($customer)->toBeInstanceOf(CustomerData::class)
            ->and($customer->deleted)->toBeFalse();

        Http::assertSent(fn ($request) =>
            str_ends_with($request->url(), '/v3/customers/cus_000005401844/restore')
            && $request->method() === 'POST'
        );
    });
});

describe('Multi-tenant key injection', function () {
    it('uses the tenant key and does not mutate the default instance', function () {
        Http::fake(['*' => Http::response(customerPayload())]);

        Asaas::withApiKey('tenant_abc')->customers()->find('cus_000005401844');

        Http::assertSent(fn ($request) => $request->header('access_token')[0] === 'tenant_abc');

        // The facade's default instance still uses the configured key
        Asaas::customers()->find('cus_000005401844');

        Http::assertSent(fn ($request) => $request->header('access_token')[0] === 'test_api_key');
    });
});
