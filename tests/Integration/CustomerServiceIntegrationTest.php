<?php

/**
 * Live integration tests for CustomerService.
 *
 * These tests hit the real Asaas sandbox API and are NOT run in the default suite.
 *
 * Prerequisites:
 *   export ASAAS_TEST_API_KEY="your_sandbox_key"
 *
 * Run with:
 *   ./vendor/bin/pest --testsuite=Live
 *   ./vendor/bin/pest --group=live
 */

use LumenSistemas\Asaas\DTOs\Customer\CreateCustomerData;
use LumenSistemas\Asaas\DTOs\Customer\CustomerData;
use LumenSistemas\Asaas\DTOs\Customer\CustomerListFilters;
use LumenSistemas\Asaas\DTOs\Customer\CustomerListResult;
use LumenSistemas\Asaas\DTOs\Customer\UpdateCustomerData;
use LumenSistemas\Asaas\Services\CustomerService;

$apiKey = (string) env('ASAAS_TEST_API_KEY', '');

if ($apiKey === '') {
    test('CustomerService live tests are skipped — set ASAAS_TEST_API_KEY to run them')
        ->skip('ASAAS_TEST_API_KEY is not set.');

    return;
}

// Unique suffix so parallel runs don't clash
$suffix = mb_substr(md5((string) microtime(true)), 0, 8);

// ──────────────────────────────────────────────────────────────
// Helpers
// ──────────────────────────────────────────────────────────────

function liveCustomerService(): CustomerService
{
    return app(CustomerService::class);
}

// ──────────────────────────────────────────────────────────────
// Tests
// ──────────────────────────────────────────────────────────────

$createdId = null;

describe('CustomerService (live)', function () use ($suffix, &$createdId): void {
    it('creates a customer', function () use ($suffix, &$createdId): void {
        $service = liveCustomerService();

        $customer = $service->create(new CreateCustomerData(
            name: 'Live Test Customer '.$suffix,
            cpfCnpj: '24971563792',
            email: sprintf('live-%s@example.com', $suffix),
        ));

        expect($customer)->toBeInstanceOf(CustomerData::class)
            ->and($customer->id)->not->toBeEmpty()
            ->and($customer->name)->toBe('Live Test Customer '.$suffix)
            ->and($customer->cpfCnpj)->toBe('24971563792');

        $createdId = $customer->id;
    });

    it('finds the created customer by ID', function () use (&$createdId): void {
        expect($createdId)->not->toBeNull('create test must run first');

        $customer = liveCustomerService()->find($createdId);

        expect($customer)->toBeInstanceOf(CustomerData::class)
            ->and($customer->id)->toBe($createdId);
    });

    it('lists customers and includes the created one', function () use ($suffix, &$createdId): void {
        expect($createdId)->not->toBeNull('create test must run first');

        $result = liveCustomerService()->list(new CustomerListFilters(
            email: sprintf('live-%s@example.com', $suffix),
        ));

        expect($result)->toBeInstanceOf(CustomerListResult::class)
            ->and($result->totalCount)->toBeGreaterThanOrEqual(1);

        $ids = array_map(fn (CustomerData $c): string => $c->id, $result->data);
        expect($ids)->toContain($createdId);
    });

    it('updates the customer', function () use (&$createdId): void {
        expect($createdId)->not->toBeNull('create test must run first');

        $updated = liveCustomerService()->update($createdId, new UpdateCustomerData(
            observations: 'Updated by live test.',
        ));

        expect($updated)->toBeInstanceOf(CustomerData::class)
            ->and($updated->id)->toBe($createdId)
            ->and($updated->observations)->toBe('Updated by live test.');
    });

    it('deletes the customer', function () use (&$createdId): void {
        expect($createdId)->not->toBeNull('create test must run first');

        $deleted = liveCustomerService()->delete($createdId);

        expect($deleted)->toBeTrue();
    });

    it('restores the deleted customer', function () use (&$createdId): void {
        expect($createdId)->not->toBeNull('create test must run first');

        $restored = liveCustomerService()->restore($createdId);

        expect($restored)->toBeInstanceOf(CustomerData::class)
            ->and($restored->id)->toBe($createdId)
            ->and($restored->deleted)->toBeFalse();
    });
});

afterAll(function () use (&$createdId): void {
    // Best-effort cleanup: delete the customer if the test left it alive
    if ($createdId !== null) {
        try {
            liveCustomerService()->delete($createdId);
        } catch (Throwable) {
            // Already deleted or doesn't exist — ignore
        }
    }
});
