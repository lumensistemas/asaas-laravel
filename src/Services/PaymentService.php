<?php

declare(strict_types=1);

namespace LumenSistemas\Asaas\Services;

use LumenSistemas\Asaas\Contracts\AsaasClientInterface;
use LumenSistemas\Asaas\DTOs\Payment\CreatePaymentData;
use LumenSistemas\Asaas\DTOs\Payment\PaymentData;
use LumenSistemas\Asaas\DTOs\Payment\PaymentListFilters;
use LumenSistemas\Asaas\DTOs\Payment\PaymentListResult;
use LumenSistemas\Asaas\DTOs\Payment\UpdatePaymentData;

/**
 * @phpstan-type PaymentArray array{id: string, customer: string, billingType: string, value: float|int, netValue: float|int, status: string, dueDate: string, deleted: bool, object?: null|string, dateCreated?: null|string, subscription?: null|string, installment?: null|string, checkoutSession?: null|string, paymentLink?: null|string, originalValue?: null|float|int, interestValue?: null|float|int, description?: null|string, originalDueDate?: null|string, paymentDate?: null|string, clientPaymentDate?: null|string, canBePaidAfterDueDate?: null|bool, externalReference?: null|string, invoiceUrl?: null|string, invoiceNumber?: null|string, nossoNumero?: null|string, bankSlipUrl?: null|string, transactionReceiptUrl?: null|string, creditDate?: null|string, estimatedCreditDate?: null|string, anticipated?: null|bool, anticipable?: null|bool, installmentNumber?: null|int, pixTransaction?: null|string, pixQrCodeId?: null|string, postalService?: null|bool, daysAfterDueDateToRegistrationCancellation?: null|int, discount?: null|array<string, mixed>, interest?: null|array<string, mixed>, fine?: null|array<string, mixed>, split?: null|array<string, mixed>, creditCard?: null|array<string, mixed>, chargeback?: null|array<string, mixed>, escrow?: null|array<string, mixed>, refunds?: null|array<string, mixed>, callback?: null|array<string, mixed>}
 */
class PaymentService
{
    public function __construct(
        private readonly AsaasClientInterface $client,
    ) {}

    public function list(?PaymentListFilters $filters = null): PaymentListResult
    {
        $query = $filters instanceof PaymentListFilters ? $filters->toArray() : [];
        /** @var array{data?: array<int, PaymentArray>, hasMore?: bool, totalCount?: int, limit?: int, offset?: int} $response */
        $response = $this->client->get('/v3/payments', $query);

        return PaymentListResult::fromArray($response);
    }

    public function find(string $id): PaymentData
    {
        /** @var PaymentArray $response */
        $response = $this->client->get('/v3/payments/'.$id);

        return PaymentData::fromArray($response);
    }

    public function create(CreatePaymentData $data): PaymentData
    {
        /** @var PaymentArray $response */
        $response = $this->client->post('/v3/payments', $data->toArray());

        return PaymentData::fromArray($response);
    }

    public function update(string $id, UpdatePaymentData $data): PaymentData
    {
        /** @var PaymentArray $response */
        $response = $this->client->put('/v3/payments/'.$id, $data->toArray());

        return PaymentData::fromArray($response);
    }

    public function delete(string $id): bool
    {
        /** @var array{deleted?: bool} $response */
        $response = $this->client->delete('/v3/payments/'.$id);

        return $response['deleted'] ?? false;
    }

    public function restore(string $id): PaymentData
    {
        /** @var PaymentArray $response */
        $response = $this->client->post(sprintf('/v3/payments/%s/restore', $id));

        return PaymentData::fromArray($response);
    }

    public function refund(string $id, ?float $value = null, ?string $description = null): PaymentData
    {
        $payload = array_filter([
            'value' => $value,
            'description' => $description,
        ], fn (mixed $v): bool => $v !== null);

        /** @var PaymentArray $response */
        $response = $this->client->post(sprintf('/v3/payments/%s/refund', $id), $payload);

        return PaymentData::fromArray($response);
    }

    public function receiveInCash(
        string $id,
        string $paymentDate,
        ?float $value = null,
        bool $notifyCustomer = false,
    ): PaymentData {
        $payload = array_filter([
            'paymentDate' => $paymentDate,
            'value' => $value,
            'notifyCustomer' => $notifyCustomer ?: null,
        ], fn (mixed $v): bool => $v !== null);

        /** @var PaymentArray $response */
        $response = $this->client->post(sprintf('/v3/payments/%s/receiveInCash', $id), $payload);

        return PaymentData::fromArray($response);
    }

    public function getStatus(string $id): string
    {
        /** @var array{status: string} $response */
        $response = $this->client->get(sprintf('/v3/payments/%s/status', $id));

        return $response['status'];
    }

    /** @return array<string, mixed> */
    public function getPixQrCode(string $id): array
    {
        /** @var array<string, mixed> $response */
        $response = $this->client->get(sprintf('/v3/payments/%s/pixQrCode', $id));

        return $response;
    }
}
