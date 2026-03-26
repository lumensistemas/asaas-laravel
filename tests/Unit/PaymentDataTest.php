<?php

use LumenSistemas\Asaas\DTOs\Payment\CreatePaymentData;
use LumenSistemas\Asaas\DTOs\Payment\PaymentData;
use LumenSistemas\Asaas\DTOs\Payment\PaymentDiscount;
use LumenSistemas\Asaas\DTOs\Payment\PaymentFine;
use LumenSistemas\Asaas\DTOs\Payment\PaymentInterest;
use LumenSistemas\Asaas\DTOs\Payment\PaymentListFilters;
use LumenSistemas\Asaas\DTOs\Payment\UpdatePaymentData;
use LumenSistemas\Asaas\Enums\Payment\PaymentBillingType;
use LumenSistemas\Asaas\Enums\Payment\PaymentStatus;

describe('PaymentData', function (): void {
    it('deserializes from a full API response', function (): void {
        $data = PaymentData::fromArray([
            'id' => 'pay_123',
            'customer' => 'cus_abc',
            'billingType' => 'PIX',
            'value' => 100.0,
            'netValue' => 97.5,
            'status' => 'PENDING',
            'dueDate' => '2026-04-01',
            'deleted' => false,
            'dateCreated' => '2026-03-08',
            'description' => 'Test payment',
            'invoiceUrl' => 'https://asaas.com/invoice/123',
            'bankSlipUrl' => null,
            'discount' => ['value' => 5.0, 'type' => 'PERCENTAGE', 'dueDateLimitDays' => 0],
            'interest' => ['value' => 1.0],
            'fine' => ['value' => 2.0, 'type' => 'FIXED'],
        ]);

        expect($data->id)->toBe('pay_123')
            ->and($data->customer)->toBe('cus_abc')
            ->and($data->billingType)->toBe(PaymentBillingType::Pix)
            ->and($data->value)->toBe(100.0)
            ->and($data->netValue)->toBe(97.5)
            ->and($data->status)->toBe(PaymentStatus::Pending)
            ->and($data->dueDate)->toBe('2026-04-01')
            ->and($data->deleted)->toBeFalse()
            ->and($data->dateCreated)->toBe('2026-03-08')
            ->and($data->description)->toBe('Test payment')
            ->and($data->invoiceUrl)->toBe('https://asaas.com/invoice/123')
            ->and($data->bankSlipUrl)->toBeNull()
            ->and($data->discount)->toBeInstanceOf(PaymentDiscount::class)
            ->and($data->discount->value)->toBe(5.0)
            ->and($data->discount->type)->toBe('PERCENTAGE')
            ->and($data->discount->dueDateLimitDays)->toBe(0)
            ->and($data->interest)->toBeInstanceOf(PaymentInterest::class)
            ->and($data->interest->value)->toBe(1.0)
            ->and($data->fine)->toBeInstanceOf(PaymentFine::class)
            ->and($data->fine->value)->toBe(2.0)
            ->and($data->fine->type)->toBe('FIXED');
    });

    it('defaults optional fields to null', function (): void {
        $data = PaymentData::fromArray([
            'id' => 'pay_456',
            'customer' => 'cus_abc',
            'billingType' => 'BOLETO',
            'value' => 200.0,
            'netValue' => 195.0,
            'status' => 'PENDING',
            'dueDate' => '2026-04-01',
            'deleted' => false,
        ]);

        expect($data->description)->toBeNull()
            ->and($data->subscription)->toBeNull()
            ->and($data->paymentDate)->toBeNull()
            ->and($data->externalReference)->toBeNull()
            ->and($data->discount)->toBeNull()
            ->and($data->interest)->toBeNull()
            ->and($data->fine)->toBeNull()
            ->and($data->creditCard)->toBeNull()
            ->and($data->anticipated)->toBeNull()
            ->and($data->anticipable)->toBeNull();
    });

    it('casts integer values to float', function (): void {
        $data = PaymentData::fromArray([
            'id' => 'pay_789',
            'customer' => 'cus_abc',
            'billingType' => 'PIX',
            'value' => 150,
            'netValue' => 145,
            'status' => 'PENDING',
            'dueDate' => '2026-04-01',
            'deleted' => false,
        ]);

        expect($data->value)->toBe(150.0)
            ->and($data->netValue)->toBe(145.0);
    });
});

describe('CreatePaymentData', function (): void {
    it('strips null optional fields from toArray()', function (): void {
        $dto = new CreatePaymentData(
            customer: 'cus_abc',
            billingType: PaymentBillingType::Pix,
            value: 100.0,
            dueDate: '2026-04-01',
            description: 'Invoice #1',
        );

        $payload = $dto->toArray();

        expect($payload)
            ->toHaveKeys(['customer', 'billingType', 'value', 'dueDate', 'description'])
            ->not->toHaveKey('externalReference')
            ->not->toHaveKey('installmentCount')
            ->not->toHaveKey('discount');

        expect($payload['billingType'])->toBe('PIX');
    });

    it('includes nested DTOs when provided', function (): void {
        $dto = new CreatePaymentData(
            customer: 'cus_abc',
            billingType: PaymentBillingType::Boleto,
            value: 200.0,
            dueDate: '2026-04-01',
            discount: new PaymentDiscount(value: 10.0, dueDateLimitDays: 5, type: 'FIXED'),
            interest: new PaymentInterest(value: 1.0),
            fine: new PaymentFine(value: 2.0),
        );

        $payload = $dto->toArray();

        expect($payload)->toHaveKey('discount', ['value' => 10.0, 'dueDateLimitDays' => 5, 'type' => 'FIXED'])
            ->and($payload)->toHaveKey('fine', ['value' => 2.0])
            ->and($payload)->toHaveKey('interest', ['value' => 1.0]);
    });

    it('throws when customer is empty', function (): void {
        new CreatePaymentData(customer: '', billingType: PaymentBillingType::Pix, value: 100.0, dueDate: '2026-04-01');
    })->throws(InvalidArgumentException::class, 'Payment customer cannot be empty.');

    it('throws when dueDate is empty', function (): void {
        new CreatePaymentData(customer: 'cus_abc', billingType: PaymentBillingType::Pix, value: 100.0, dueDate: '');
    })->throws(InvalidArgumentException::class, 'Payment dueDate cannot be empty.');

    it('throws when value is zero', function (): void {
        new CreatePaymentData(customer: 'cus_abc', billingType: PaymentBillingType::Pix, value: 0.0, dueDate: '2026-04-01');
    })->throws(InvalidArgumentException::class, 'Payment value must be greater than zero.');

    it('throws when value is negative', function (): void {
        new CreatePaymentData(customer: 'cus_abc', billingType: PaymentBillingType::Pix, value: -5.0, dueDate: '2026-04-01');
    })->throws(InvalidArgumentException::class, 'Payment value must be greater than zero.');
});

describe('UpdatePaymentData', function (): void {
    it('only includes non-null fields', function (): void {
        $dto = new UpdatePaymentData(value: 150.0, dueDate: '2026-05-01');

        expect($dto->toArray())->toBe(['value' => 150.0, 'dueDate' => '2026-05-01']);
    });

    it('serializes billingType enum to string', function (): void {
        $dto = new UpdatePaymentData(billingType: PaymentBillingType::Boleto);

        expect($dto->toArray())->toBe(['billingType' => 'BOLETO']);
    });

    it('produces an empty array when nothing is set', function (): void {
        $dto = new UpdatePaymentData();

        expect($dto->toArray())->toBe([]);
    });
});

describe('PaymentListFilters', function (): void {
    it('always includes offset and limit', function (): void {
        $filters = new PaymentListFilters();

        expect($filters->toArray())->toMatchArray(['offset' => 0, 'limit' => 10]);
    });

    it('caps limit at 100', function (): void {
        $filters = new PaymentListFilters(limit: 500);

        expect($filters->toArray()['limit'])->toBe(100);
    });

    it('serializes enum filters to their string values', function (): void {
        $filters = new PaymentListFilters(
            customer: 'cus_abc',
            billingType: PaymentBillingType::Pix,
            status: PaymentStatus::Pending,
        );

        $params = $filters->toArray();

        expect($params)
            ->toHaveKey('customer', 'cus_abc')
            ->toHaveKey('status', 'PENDING')
            ->toHaveKey('billingType', 'PIX')
            ->not->toHaveKey('subscription');
    });

    it('maps date range filters to bracket notation', function (): void {
        $filters = new PaymentListFilters(
            dateCreatedGe: '2026-01-01',
            dueDateGe: '2026-01-01',
            dueDateLe: '2026-12-31',
        );

        $params = $filters->toArray();

        expect($params)
            ->toHaveKey('dueDate[ge]', '2026-01-01')
            ->toHaveKey('dueDate[le]', '2026-12-31')
            ->toHaveKey('dateCreated[ge]', '2026-01-01')
            ->not->toHaveKey('dueDateGe')
            ->not->toHaveKey('dateCreatedGe');
    });

    it('includes boolean filters when set', function (): void {
        $filters = new PaymentListFilters(anticipated: true, anticipable: false);

        $params = $filters->toArray();

        expect($params)
            ->toHaveKey('anticipated', true)
            ->toHaveKey('anticipable', false);
    });
});
