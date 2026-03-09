<?php

use Illuminate\Support\Facades\Http;
use LumenSistemas\Asaas\DTOs\Payment\CreatePaymentData;
use LumenSistemas\Asaas\DTOs\Payment\PaymentBillingInfoBankSlipData;
use LumenSistemas\Asaas\DTOs\Payment\PaymentBillingInfoCreditCardData;
use LumenSistemas\Asaas\DTOs\Payment\PaymentBillingInfoData;
use LumenSistemas\Asaas\DTOs\Payment\PaymentData;
use LumenSistemas\Asaas\DTOs\Payment\PaymentListFilters;
use LumenSistemas\Asaas\DTOs\Payment\PaymentListResult;
use LumenSistemas\Asaas\DTOs\Payment\PaymentPixData;
use LumenSistemas\Asaas\DTOs\Payment\UpdatePaymentData;
use LumenSistemas\Asaas\Enums\Payment\PaymentBillingType;
use LumenSistemas\Asaas\Enums\Payment\PaymentStatus;
use LumenSistemas\Asaas\Exceptions\AsaasApiException;
use LumenSistemas\Asaas\Services\PaymentService;

/**
 * @return array<string, mixed>
 */
function paymentPayload(array $overrides = []): array
{
    return array_merge([
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
    ], $overrides);
}

describe('PaymentService::list()', function (): void {
    it('returns a PaymentListResult with hydrated PaymentData items', function (): void {
        Http::fake(['*' => Http::response([
            'data' => [paymentPayload()],
            'hasMore' => false,
            'totalCount' => 1,
            'limit' => 10,
            'offset' => 0,
        ])]);

        $service = app(PaymentService::class);
        $result = $service->list();

        expect($result)->toBeInstanceOf(PaymentListResult::class)
            ->and($result->totalCount)->toBe(1)
            ->and($result->hasMore)->toBeFalse()
            ->and($result->data)->toHaveCount(1)
            ->and($result->data[0])->toBeInstanceOf(PaymentData::class)
            ->and($result->data[0]->id)->toBe('pay_123');
    });

    it('forwards filter query parameters', function (): void {
        Http::fake(['*' => Http::response([
            'data' => [],
            'hasMore' => false,
            'totalCount' => 0,
            'limit' => 10,
            'offset' => 0,
        ])]);

        $service = app(PaymentService::class);
        $service->list(new PaymentListFilters(
            customer: 'cus_abc',
            status: PaymentStatus::Pending,
            dueDateGe: '2026-01-01',
        ));

        Http::assertSent(
            fn ($request): bool => $request->method() === 'GET'
            && $request->data()['customer'] === 'cus_abc'
            && $request->data()['status'] === 'PENDING'
            && $request->data()['dueDate[ge]'] === '2026-01-01'
        );
    });
});

describe('PaymentService::find()', function (): void {
    it('returns a PaymentData for a valid ID', function (): void {
        Http::fake(['*' => Http::response(paymentPayload())]);

        $service = app(PaymentService::class);
        $payment = $service->find('pay_123');

        expect($payment)->toBeInstanceOf(PaymentData::class)
            ->and($payment->id)->toBe('pay_123')
            ->and($payment->status)->toBe(PaymentStatus::Pending);

        Http::assertSent(
            fn ($request): bool => $request->method() === 'GET'
            && str_ends_with((string) $request->url(), '/v3/payments/pay_123')
        );
    });
});

describe('PaymentService::create()', function (): void {
    it('POSTs and returns the created payment', function (): void {
        Http::fake(['*' => Http::response(paymentPayload(['status' => 'PENDING']))]);

        $service = app(PaymentService::class);
        $payment = $service->create(new CreatePaymentData(
            customer: 'cus_abc',
            billingType: PaymentBillingType::Pix,
            value: 100.0,
            dueDate: '2026-04-01',
            description: 'Test payment',
        ));

        expect($payment)->toBeInstanceOf(PaymentData::class)
            ->and($payment->id)->toBe('pay_123');

        Http::assertSent(
            fn ($request): bool => $request->method() === 'POST'
            && str_ends_with((string) $request->url(), '/v3/payments')
            && $request->data()['customer'] === 'cus_abc'
            && $request->data()['billingType'] === 'PIX'
            && $request->data()['value'] === 100.0
        );
    });

    it('throws AsaasApiException when the API returns a 4xx error', function (): void {
        Http::fake(['*' => Http::response(
            ['errors' => [['code' => 'invalid_customer', 'description' => 'Customer not found.']]],
            422
        )]);

        $service = app(PaymentService::class);
        $service->create(new CreatePaymentData(
            customer: 'cus_invalid',
            billingType: PaymentBillingType::Pix,
            value: 100.0,
            dueDate: '2026-04-01',
        ));
    })->throws(AsaasApiException::class);
});

describe('PaymentService::update()', function (): void {
    it('PUTs and returns the updated payment', function (): void {
        Http::fake(['*' => Http::response(paymentPayload(['value' => 200.0]))]);

        $service = app(PaymentService::class);
        $payment = $service->update('pay_123', new UpdatePaymentData(value: 200.0));

        expect($payment)->toBeInstanceOf(PaymentData::class)
            ->and($payment->value)->toBe(200.0);

        Http::assertSent(
            fn ($request): bool => $request->method() === 'PUT'
            && str_ends_with((string) $request->url(), '/v3/payments/pay_123')
            && $request->data()['value'] === 200.0
        );
    });
});

describe('PaymentService::delete()', function (): void {
    it('DELETEs the payment and returns true', function (): void {
        Http::fake(['*' => Http::response(['deleted' => true, 'id' => 'pay_123'])]);

        $service = app(PaymentService::class);
        $result = $service->delete('pay_123');

        expect($result)->toBeTrue();

        Http::assertSent(
            fn ($request): bool => $request->method() === 'DELETE'
            && str_ends_with((string) $request->url(), '/v3/payments/pay_123')
        );
    });
});

describe('PaymentService::restore()', function (): void {
    it('POSTs to the restore endpoint and returns PaymentData', function (): void {
        Http::fake(['*' => Http::response(paymentPayload(['deleted' => false]))]);

        $service = app(PaymentService::class);
        $payment = $service->restore('pay_123');

        expect($payment)->toBeInstanceOf(PaymentData::class)
            ->and($payment->deleted)->toBeFalse();

        Http::assertSent(
            fn ($request): bool => $request->method() === 'POST'
            && str_ends_with((string) $request->url(), '/v3/payments/pay_123/restore')
        );
    });
});

describe('PaymentService::refund()', function (): void {
    it('POSTs to the refund endpoint and returns PaymentData', function (): void {
        Http::fake(['*' => Http::response(paymentPayload(['status' => 'REFUNDED']))]);

        $service = app(PaymentService::class);
        $payment = $service->refund('pay_123', 50.0, 'Partial refund');

        expect($payment)->toBeInstanceOf(PaymentData::class)
            ->and($payment->status)->toBe(PaymentStatus::Refunded);

        Http::assertSent(
            fn ($request): bool => $request->method() === 'POST'
            && str_ends_with((string) $request->url(), '/v3/payments/pay_123/refund')
            && $request->data()['value'] === 50.0
            && $request->data()['description'] === 'Partial refund'
        );
    });

    it('sends no body when no optional params are given', function (): void {
        Http::fake(['*' => Http::response(paymentPayload(['status' => 'REFUNDED']))]);

        $service = app(PaymentService::class);
        $service->refund('pay_123');

        Http::assertSent(
            fn ($request): bool => $request->method() === 'POST'
            && str_ends_with((string) $request->url(), '/v3/payments/pay_123/refund')
            && $request->data() === []
        );
    });
});

describe('PaymentService::receiveInCash()', function (): void {
    it('POSTs to the receiveInCash endpoint and returns PaymentData', function (): void {
        Http::fake(['*' => Http::response(paymentPayload(['status' => 'RECEIVED_IN_CASH']))]);

        $service = app(PaymentService::class);
        $payment = $service->receiveInCash('pay_123', '2026-03-08', 100.0, true);

        expect($payment)->toBeInstanceOf(PaymentData::class)
            ->and($payment->status)->toBe(PaymentStatus::ReceivedInCash);

        Http::assertSent(
            fn ($request): bool => $request->method() === 'POST'
            && str_ends_with((string) $request->url(), '/v3/payments/pay_123/receiveInCash')
            && $request->data()['paymentDate'] === '2026-03-08'
            && $request->data()['value'] === 100.0
            && $request->data()['notifyCustomer'] === true
        );
    });
});

describe('PaymentService::getStatus()', function (): void {
    it('returns the payment status string', function (): void {
        Http::fake(['*' => Http::response(['status' => 'CONFIRMED'])]);

        $service = app(PaymentService::class);
        $status = $service->getStatus('pay_123');

        expect($status)->toBe('CONFIRMED');

        Http::assertSent(
            fn ($request): bool => $request->method() === 'GET'
            && str_ends_with((string) $request->url(), '/v3/payments/pay_123/status')
        );
    });
});

describe('PaymentService::getIdentificationField()', function (): void {
    it('returns a hydrated PaymentBillingInfoBankSlipData', function (): void {
        Http::fake(['*' => Http::response([
            'identificationField' => '00190000090275928800021932978170187890000005000',
            'nossoNumero' => '6543',
            'barCode' => '00191878900000050000000002759288002193297817',
        ])]);

        $service = app(PaymentService::class);
        $result = $service->getIdentificationField('pay_123');

        expect($result)->toBeInstanceOf(PaymentBillingInfoBankSlipData::class)
            ->and($result->identificationField)->toBe('00190000090275928800021932978170187890000005000')
            ->and($result->nossoNumero)->toBe('6543')
            ->and($result->barCode)->toBe('00191878900000050000000002759288002193297817')
            ->and($result->bankSlipUrl)->toBeNull()
            ->and($result->daysAfterDueDateToRegistrationCancellation)->toBeNull();

        Http::assertSent(
            fn ($request): bool => $request->method() === 'GET'
            && str_ends_with((string) $request->url(), '/v3/payments/pay_123/identificationField')
        );
    });

    it('throws AsaasApiException on API error', function (): void {
        Http::fake(['*' => Http::response(['errors' => [['code' => 'invalid_id', 'description' => 'Payment not found']]], 404)]);

        $service = app(PaymentService::class);

        expect(fn () => $service->getIdentificationField('pay_invalid'))
            ->toThrow(AsaasApiException::class);
    });
});

describe('PaymentService::getPixQrCode()', function (): void {
    it('returns a hydrated PaymentPixData', function (): void {
        Http::fake(['*' => Http::response([
            'encodedImage' => 'base64string==',
            'payload' => '00020126...',
            'expirationDate' => '2026-04-01 23:59:59',
            'description' => 'Test charge',
        ])]);

        $service = app(PaymentService::class);
        $result = $service->getPixQrCode('pay_123');

        expect($result)->toBeInstanceOf(PaymentPixData::class)
            ->and($result->encodedImage)->toBe('base64string==')
            ->and($result->payload)->toBe('00020126...')
            ->and($result->expirationDate)->toBe('2026-04-01 23:59:59')
            ->and($result->description)->toBe('Test charge');

        Http::assertSent(
            fn ($request): bool => $request->method() === 'GET'
            && str_ends_with((string) $request->url(), '/v3/payments/pay_123/pixQrCode')
        );
    });
});

describe('PaymentService::getBillingInfo()', function (): void {
    it('returns PaymentBillingInfoData with pix data hydrated', function (): void {
        Http::fake(['*' => Http::response([
            'pix' => [
                'encodedImage' => 'base64==',
                'payload' => '00020101021226...',
                'expirationDate' => '2026-04-01 23:59:59',
                'description' => 'Test charge',
            ],
        ])]);

        $service = app(PaymentService::class);
        $result = $service->getBillingInfo('pay_123');

        expect($result)->toBeInstanceOf(PaymentBillingInfoData::class)
            ->and($result->pix)->toBeInstanceOf(PaymentPixData::class)
            ->and($result->pix->encodedImage)->toBe('base64==')
            ->and($result->pix->payload)->toBe('00020101021226...')
            ->and($result->pix->expirationDate)->toBe('2026-04-01 23:59:59')
            ->and($result->pix->description)->toBe('Test charge')
            ->and($result->creditCard)->toBeNull()
            ->and($result->bankSlip)->toBeNull();

        Http::assertSent(
            fn ($request): bool => $request->method() === 'GET'
            && str_ends_with((string) $request->url(), '/v3/payments/pay_123/billingInfo')
        );
    });

    it('returns PaymentBillingInfoData with creditCard data hydrated', function (): void {
        Http::fake(['*' => Http::response([
            'creditCard' => [
                'creditCardNumber' => '8829',
                'creditCardBrand' => 'VISA',
                'creditCardToken' => 'a75a1d98-c52d-4a6b-a413-71e00b193c99',
            ],
        ])]);

        $service = app(PaymentService::class);
        $result = $service->getBillingInfo('pay_123');

        expect($result)->toBeInstanceOf(PaymentBillingInfoData::class)
            ->and($result->creditCard)->toBeInstanceOf(PaymentBillingInfoCreditCardData::class)
            ->and($result->creditCard->creditCardNumber)->toBe('8829')
            ->and($result->creditCard->creditCardBrand)->toBe('VISA')
            ->and($result->creditCard->creditCardToken)->toBe('a75a1d98-c52d-4a6b-a413-71e00b193c99')
            ->and($result->pix)->toBeNull()
            ->and($result->bankSlip)->toBeNull();
    });

    it('returns PaymentBillingInfoData with bankSlip data hydrated', function (): void {
        Http::fake(['*' => Http::response([
            'bankSlip' => [
                'identificationField' => '00190000090275928800021932978170187890000005000',
                'nossoNumero' => '6543',
                'barCode' => '00191878900000050000000002759288002193297817',
                'bankSlipUrl' => 'https://www.asaas.com/b/pdf/080225913252',
                'daysAfterDueDateToRegistrationCancellation' => 1,
            ],
        ])]);

        $service = app(PaymentService::class);
        $result = $service->getBillingInfo('pay_123');

        expect($result)->toBeInstanceOf(PaymentBillingInfoData::class)
            ->and($result->bankSlip)->toBeInstanceOf(PaymentBillingInfoBankSlipData::class)
            ->and($result->bankSlip->identificationField)->toBe('00190000090275928800021932978170187890000005000')
            ->and($result->bankSlip->nossoNumero)->toBe('6543')
            ->and($result->bankSlip->barCode)->toBe('00191878900000050000000002759288002193297817')
            ->and($result->bankSlip->bankSlipUrl)->toBe('https://www.asaas.com/b/pdf/080225913252')
            ->and($result->bankSlip->daysAfterDueDateToRegistrationCancellation)->toBe(1)
            ->and($result->pix)->toBeNull()
            ->and($result->creditCard)->toBeNull();
    });

    it('throws AsaasApiException on API error', function (): void {
        Http::fake(['*' => Http::response(['errors' => [['code' => 'invalid_id', 'description' => 'Payment not found']]], 404)]);

        $service = app(PaymentService::class);

        expect(fn () => $service->getBillingInfo('pay_invalid'))
            ->toThrow(AsaasApiException::class);
    });
});
