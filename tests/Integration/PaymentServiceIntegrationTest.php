<?php

/**
 * Live integration tests for PaymentService.
 *
 * These tests hit the real Asaas sandbox API and are NOT run in the default suite.
 *
 * Prerequisites:
 *   export ASAAS_TEST_API_KEY="your_sandbox_key"
 *
 * Run with:
 *   ./vendor/bin/pest --testsuite=Integration
 *   ./vendor/bin/pest --group=integration
 */

use LumenSistemas\Asaas\DTOs\Customer\CreateCustomerData;
use LumenSistemas\Asaas\DTOs\Payment\CreatePaymentData;
use LumenSistemas\Asaas\DTOs\Payment\PaymentData;
use LumenSistemas\Asaas\DTOs\Payment\PaymentListFilters;
use LumenSistemas\Asaas\DTOs\Payment\PaymentListResult;
use LumenSistemas\Asaas\DTOs\Payment\UpdatePaymentData;
use LumenSistemas\Asaas\Enums\Payment\PaymentBillingType;
use LumenSistemas\Asaas\Enums\Payment\PaymentStatus;
use LumenSistemas\Asaas\Services\CustomerService;
use LumenSistemas\Asaas\Services\PaymentService;

$apiKey = (string) env('ASAAS_TEST_API_KEY', '');

if ($apiKey === '') {
    test('PaymentService live tests are skipped — set ASAAS_TEST_API_KEY to run them')
        ->skip('ASAAS_TEST_API_KEY is not set.');

    return;
}

// Unique suffix so parallel runs don't clash
$suffix = mb_substr(md5((string) microtime(true)), 0, 8);

// ──────────────────────────────────────────────────────────────
// Helpers
// ──────────────────────────────────────────────────────────────

function livePaymentService(): PaymentService
{
    return app(PaymentService::class);
}

function livePaymentCustomerService(): CustomerService
{
    return app(CustomerService::class);
}

// ──────────────────────────────────────────────────────────────
// Tests
// ──────────────────────────────────────────────────────────────

$customerId = null;
$createdPaymentId = null;

describe('PaymentService (live)', function () use ($suffix, &$customerId, &$createdPaymentId): void {
    it('creates a customer and a payment', function () use ($suffix, &$customerId, &$createdPaymentId): void {
        $customerId = livePaymentCustomerService()->create(new CreateCustomerData(
            name: 'Payment Integration Customer '.$suffix,
            cpfCnpj: '24971563792',
            email: sprintf('payment-integration-%s@example.com', $suffix),
        ))->id;

        $payment = livePaymentService()->create(new CreatePaymentData(
            customer: $customerId,
            billingType: PaymentBillingType::Pix,
            value: 10.00,
            dueDate: '2026-12-31',
            description: 'Integration test payment '.$suffix,
        ));

        expect($payment)->toBeInstanceOf(PaymentData::class)
            ->and($payment->id)->not->toBeEmpty()
            ->and($payment->customer)->toBe($customerId)
            ->and($payment->billingType)->toBe(PaymentBillingType::Pix)
            ->and($payment->value)->toBe(10.0)
            ->and($payment->status)->toBe(PaymentStatus::Pending);

        $createdPaymentId = $payment->id;
    });

    it('finds the created payment by ID', function () use (&$createdPaymentId): void {
        expect($createdPaymentId)->not->toBeNull('create test must run first');

        $payment = livePaymentService()->find($createdPaymentId);

        expect($payment)->toBeInstanceOf(PaymentData::class)
            ->and($payment->id)->toBe($createdPaymentId);
    });

    it('lists payments and includes the created one', function () use (&$customerId, &$createdPaymentId): void {
        expect($createdPaymentId)->not->toBeNull('create test must run first');
        assert(is_string($customerId));

        $result = livePaymentService()->list(new PaymentListFilters(customer: $customerId));

        expect($result)->toBeInstanceOf(PaymentListResult::class)
            ->and($result->totalCount)->toBeGreaterThanOrEqual(1);

        $ids = array_map(fn (PaymentData $p): string => $p->id, $result->data);
        expect($ids)->toContain($createdPaymentId);
    });

    it('gets the payment status', function () use (&$createdPaymentId): void {
        expect($createdPaymentId)->not->toBeNull('create test must run first');

        $status = livePaymentService()->getStatus($createdPaymentId);

        expect($status)->toBeString()->not->toBeEmpty();
    });

    it('gets the Pix QR code', function () use (&$createdPaymentId): void {
        expect($createdPaymentId)->not->toBeNull('create test must run first');

        $qr = livePaymentService()->getPixQrCode($createdPaymentId);

        expect($qr)->toBeArray()
            ->and($qr)->toHaveKey('encodedImage')
            ->and($qr)->toHaveKey('payload');
    });

    it('updates the payment', function () use (&$createdPaymentId): void {
        expect($createdPaymentId)->not->toBeNull('create test must run first');

        $updated = livePaymentService()->update($createdPaymentId, new UpdatePaymentData(
            description: 'Updated description',
        ));

        expect($updated)->toBeInstanceOf(PaymentData::class)
            ->and($updated->id)->toBe($createdPaymentId)
            ->and($updated->description)->toBe('Updated description');
    });

    it('deletes the payment', function () use (&$createdPaymentId): void {
        expect($createdPaymentId)->not->toBeNull('create test must run first');

        $deleted = livePaymentService()->delete($createdPaymentId);

        expect($deleted)->toBeTrue();
    });

    it('restores the deleted payment', function () use (&$createdPaymentId): void {
        expect($createdPaymentId)->not->toBeNull('create test must run first');

        $restored = livePaymentService()->restore($createdPaymentId);

        expect($restored)->toBeInstanceOf(PaymentData::class)
            ->and($restored->id)->toBe($createdPaymentId)
            ->and($restored->deleted)->toBeFalse();
    });
});

afterAll(function () use (&$customerId, &$createdPaymentId): void {
    // Best-effort cleanup: remove the payment and the temporary customer
    if ($createdPaymentId !== null) {
        try {
            livePaymentService()->delete($createdPaymentId);
        } catch (Throwable) {
            // ignore
        }
    }

    if ($customerId !== null) {
        try {
            livePaymentCustomerService()->delete($customerId);
        } catch (Throwable) {
            // ignore
        }
    }
});
