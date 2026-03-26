<?php

use Illuminate\Support\Facades\Http;
use LumenSistemas\Asaas\DTOs\Bill\BillData;
use LumenSistemas\Asaas\DTOs\Bill\BillListResult;
use LumenSistemas\Asaas\DTOs\Bill\BillSimulateRequest;
use LumenSistemas\Asaas\DTOs\Bill\BillSimulateResponse;
use LumenSistemas\Asaas\DTOs\Bill\CreateBillData;
use LumenSistemas\Asaas\Exceptions\AsaasApiException;
use LumenSistemas\Asaas\Services\BillService;

/**
 * @return array<string, mixed>
 */
function billPayload(array $overrides = []): array
{
    return array_merge([
        'id' => 'bill_123',
        'status' => 'PENDING',
        'value' => 150.00,
        'identificationField' => '12345.67890 12345.678901 12345.678901 1 12340000015000',
        'dueDate' => '2026-04-01',
        'scheduleDate' => '2026-03-20',
        'canBeCancelled' => true,
        'discount' => null,
        'interest' => null,
        'fine' => null,
        'paymentDate' => null,
        'fee' => 2.99,
        'description' => 'Test bill payment',
        'companyName' => 'ACME Corp',
        'transactionReceiptUrl' => null,
        'externalReference' => null,
        'failReasons' => [],
    ], $overrides);
}

/**
 * @return array<string, mixed>
 */
function bankSlipInfoPayload(array $overrides = []): array
{
    return array_merge([
        'identificationField' => '12345.67890 12345.678901 12345.678901 1 12340000015000',
        'value' => 150.00,
        'dueDate' => '2026-04-01',
        'bank' => '033',
        'beneficiaryCpfCnpj' => '12345678000199',
        'beneficiaryName' => 'ACME Corp',
        'allowChangeValue' => false,
        'minValue' => 150.00,
        'maxValue' => 150.00,
        'discountValue' => 0.00,
        'interestValue' => 0.00,
        'fineValue' => 0.00,
        'originalValue' => 150.00,
        'totalDiscountValue' => 0.00,
        'totalAdditionalValue' => 0.00,
        'isOverdue' => false,
        'companyName' => 'ACME Corp',
    ], $overrides);
}

describe('BillService::list()', function (): void {
    it('returns a BillListResult with hydrated BillData items', function (): void {
        Http::fake(['*' => Http::response([
            'data' => [billPayload()],
            'hasMore' => false,
            'totalCount' => 1,
            'limit' => 10,
            'offset' => 0,
        ])]);

        $result = app(BillService::class)->list();

        expect($result)->toBeInstanceOf(BillListResult::class)
            ->and($result->totalCount)->toBe(1)
            ->and($result->hasMore)->toBeFalse()
            ->and($result->data)->toHaveCount(1)
            ->and($result->data[0])->toBeInstanceOf(BillData::class)
            ->and($result->data[0]->id)->toBe('bill_123');

        Http::assertSent(
            fn ($r): bool => $r->method() === 'GET'
            && str_contains((string) $r->url(), '/v3/bill')
        );
    });

    it('passes pagination filters as query parameters', function (): void {
        Http::fake(['*' => Http::response([
            'data' => [],
            'hasMore' => false,
            'totalCount' => 0,
            'limit' => 5,
            'offset' => 10,
        ])]);

        $filters = new LumenSistemas\Asaas\DTOs\Bill\BillListFilters(offset: 10, limit: 5);
        app(BillService::class)->list($filters);

        Http::assertSent(
            fn ($r): bool => str_contains((string) $r->url(), 'offset=10')
            && str_contains((string) $r->url(), 'limit=5')
        );
    });
});

describe('BillService::find()', function (): void {
    it('returns a BillData for a valid ID', function (): void {
        Http::fake(['*' => Http::response(billPayload())]);

        $bill = app(BillService::class)->find('bill_123');

        expect($bill)->toBeInstanceOf(BillData::class)
            ->and($bill->id)->toBe('bill_123')
            ->and($bill->status)->toBe('PENDING')
            ->and($bill->value)->toBe(150.0)
            ->and($bill->canBeCancelled)->toBeTrue();

        Http::assertSent(
            fn ($r): bool => $r->method() === 'GET'
            && str_ends_with((string) $r->url(), '/v3/bill/bill_123')
        );
    });

    it('throws AsaasApiException on a 4xx error', function (): void {
        Http::fake(['*' => Http::response(
            ['errors' => [['code' => 'not_found', 'description' => 'Bill not found.']]],
            404
        )]);

        app(BillService::class)->find('bill_invalid');
    })->throws(AsaasApiException::class);
});

describe('BillService::create()', function (): void {
    it('POSTs and returns the created bill', function (): void {
        Http::fake(['*' => Http::response(billPayload())]);

        $bill = app(BillService::class)->create(new CreateBillData(
            identificationField: '12345.67890 12345.678901 12345.678901 1 12340000015000',
            scheduleDate: '2026-03-20',
            description: 'Test bill payment',
        ));

        expect($bill)->toBeInstanceOf(BillData::class)
            ->and($bill->id)->toBe('bill_123');

        Http::assertSent(
            fn ($r): bool => $r->method() === 'POST'
            && str_ends_with((string) $r->url(), '/v3/bill')
            && $r->data()['identificationField'] === '12345.67890 12345.678901 12345.678901 1 12340000015000'
            && $r->data()['scheduleDate'] === '2026-03-20'
        );
    });

    it('throws AsaasApiException on a 4xx error', function (): void {
        Http::fake(['*' => Http::response(
            ['errors' => [['code' => 'invalid_identification_field', 'description' => 'Identification field is invalid.']]],
            422
        )]);

        app(BillService::class)->create(new CreateBillData(
            identificationField: '00000.000000 00000.000000 00000.000000 0 00000000000000',
        ));
    })->throws(AsaasApiException::class);

    it('throws when identificationField is empty', function (): void {
        new CreateBillData(identificationField: '');
    })->throws(InvalidArgumentException::class, 'Bill identificationField cannot be empty.');

    it('strips null optional fields from payload', function (): void {
        Http::fake(['*' => Http::response(billPayload())]);

        app(BillService::class)->create(new CreateBillData(
            identificationField: '12345.67890 12345.678901 12345.678901 1 12340000015000',
        ));

        Http::assertSent(
            fn ($r): bool => !array_key_exists('scheduleDate', $r->data())
            && !array_key_exists('description', $r->data())
            && !array_key_exists('value', $r->data())
        );
    });
});

describe('BillService::cancel()', function (): void {
    it('POSTs to cancel endpoint and returns updated BillData', function (): void {
        Http::fake(['*' => Http::response(billPayload(['status' => 'CANCELLED', 'canBeCancelled' => false]))]);

        $bill = app(BillService::class)->cancel('bill_123');

        expect($bill)->toBeInstanceOf(BillData::class)
            ->and($bill->status)->toBe('CANCELLED')
            ->and($bill->canBeCancelled)->toBeFalse();

        Http::assertSent(
            fn ($r): bool => $r->method() === 'POST'
            && str_ends_with((string) $r->url(), '/v3/bill/bill_123/cancel')
        );
    });

    it('throws AsaasApiException when bill cannot be cancelled', function (): void {
        Http::fake(['*' => Http::response(
            ['errors' => [['code' => 'cannot_cancel_bill', 'description' => 'This bill cannot be cancelled.']]],
            422
        )]);

        app(BillService::class)->cancel('bill_123');
    })->throws(AsaasApiException::class);
});

describe('BillService::simulate()', function (): void {
    it('POSTs to simulate endpoint and returns BillSimulateResponse', function (): void {
        Http::fake(['*' => Http::response([
            'minimumScheduleDate' => '2026-03-17',
            'fee' => 2.99,
            'bankSlipInfo' => bankSlipInfoPayload(),
        ])]);

        $response = app(BillService::class)->simulate(new BillSimulateRequest(
            identificationField: '12345.67890 12345.678901 12345.678901 1 12340000015000',
        ));

        expect($response)->toBeInstanceOf(BillSimulateResponse::class)
            ->and($response->minimumScheduleDate)->toBe('2026-03-17')
            ->and($response->fee)->toBe(2.99)
            ->and($response->bankSlipInfo->bank)->toBe('033')
            ->and($response->bankSlipInfo->value)->toBe(150.0)
            ->and($response->bankSlipInfo->isOverdue)->toBeFalse()
            ->and($response->bankSlipInfo->allowChangeValue)->toBeFalse();

        Http::assertSent(
            fn ($r): bool => $r->method() === 'POST'
            && str_ends_with((string) $r->url(), '/v3/bill/simulate')
            && $r->data()['identificationField'] === '12345.67890 12345.678901 12345.678901 1 12340000015000'
        );
    });

    it('can simulate using barCode field', function (): void {
        Http::fake(['*' => Http::response([
            'minimumScheduleDate' => '2026-03-17',
            'fee' => 2.99,
            'bankSlipInfo' => bankSlipInfoPayload(),
        ])]);

        app(BillService::class)->simulate(new BillSimulateRequest(
            barCode: '12345678901234567890123456789012345678901234',
        ));

        Http::assertSent(
            fn ($r): bool => $r->data()['barCode'] === '12345678901234567890123456789012345678901234'
            && !array_key_exists('identificationField', $r->data())
        );
    });

    it('throws AsaasApiException on a 4xx error', function (): void {
        Http::fake(['*' => Http::response(
            ['errors' => [['code' => 'invalid_identification_field', 'description' => 'Identification field is invalid.']]],
            422
        )]);

        app(BillService::class)->simulate(new BillSimulateRequest(
            identificationField: '00000',
        ));
    })->throws(AsaasApiException::class);
});
