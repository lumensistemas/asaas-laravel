<?php

declare(strict_types=1);

namespace LumenSistemas\Asaas\Services;

use LumenSistemas\Asaas\Contracts\AsaasClientInterface;
use LumenSistemas\Asaas\DTOs\Payment\CreatePaymentData;
use LumenSistemas\Asaas\DTOs\Payment\PaymentBillingInfoBankSlipData;
use LumenSistemas\Asaas\DTOs\Payment\PaymentBillingInfoData;
use LumenSistemas\Asaas\DTOs\Payment\PaymentData;
use LumenSistemas\Asaas\DTOs\Payment\PaymentListFilters;
use LumenSistemas\Asaas\DTOs\Payment\PaymentListResult;
use LumenSistemas\Asaas\DTOs\Payment\PaymentPixData;
use LumenSistemas\Asaas\DTOs\Payment\UpdatePaymentData;

/**
 * @phpstan-type PaymentArray array{id: string, customer: string, billingType: string, value: float|int, netValue: float|int, status: string, dueDate: string, deleted: bool, object?: null|string, dateCreated?: null|string, subscription?: null|string, installment?: null|string, checkoutSession?: null|string, paymentLink?: null|string, originalValue?: null|float|int, interestValue?: null|float|int, description?: null|string, originalDueDate?: null|string, paymentDate?: null|string, clientPaymentDate?: null|string, canBePaidAfterDueDate?: null|bool, externalReference?: null|string, invoiceUrl?: null|string, invoiceNumber?: null|string, nossoNumero?: null|string, bankSlipUrl?: null|string, transactionReceiptUrl?: null|string, creditDate?: null|string, estimatedCreditDate?: null|string, anticipated?: null|bool, anticipable?: null|bool, installmentNumber?: null|int, pixTransaction?: null|string, pixQrCodeId?: null|string, postalService?: null|bool, daysAfterDueDateToRegistrationCancellation?: null|int, discount?: null|array{value: float|int, dueDateLimitDays: int, type: 'FIXED'|'PERCENTAGE'}, interest?: null|array{value: float|int}, fine?: null|array{value: float|int, type?: 'FIXED'|'PERCENTAGE'|null}, split?: null|array<int, array<string, mixed>>, creditCard?: null|array<string, mixed>, chargeback?: null|array<string, mixed>, escrow?: null|array<string, mixed>, refunds?: null|array<string, mixed>, callback?: null|array<string, mixed>}
 */
class PaymentService
{
    public function __construct(
        private readonly AsaasClientInterface $client,
    ) {}

    /**
     * List payments, optionally filtered by the given criteria.
     *
     * @see https://docs.asaas.com/reference/listar-cobrancas
     */
    public function list(?PaymentListFilters $filters = null): PaymentListResult
    {
        $query = $filters instanceof PaymentListFilters ? $filters->toArray() : [];
        /** @var array{data?: array<int, PaymentArray>, hasMore?: bool, totalCount?: int, limit?: int, offset?: int} $response */
        $response = $this->client->get('/v3/payments', $query);

        return PaymentListResult::fromArray($response);
    }

    /**
     * Retrieve a single payment by its ID.
     *
     * @see https://docs.asaas.com/reference/recuperar-uma-unica-cobranca
     */
    public function find(string $id): PaymentData
    {
        /** @var PaymentArray $response */
        $response = $this->client->get('/v3/payments/'.$id);

        return PaymentData::fromArray($response);
    }

    /**
     * Create a new payment.
     *
     * @see https://docs.asaas.com/reference/criar-nova-cobranca
     */
    public function create(CreatePaymentData $data): PaymentData
    {
        /** @var PaymentArray $response */
        $response = $this->client->post('/v3/payments', $data->toArray());

        return PaymentData::fromArray($response);
    }

    /**
     * Update an existing payment.
     *
     * @see https://docs.asaas.com/reference/atualizar-cobranca-existente
     */
    public function update(string $id, UpdatePaymentData $data): PaymentData
    {
        /** @var PaymentArray $response */
        $response = $this->client->put('/v3/payments/'.$id, $data->toArray());

        return PaymentData::fromArray($response);
    }

    /**
     * Delete (soft-delete) a payment.
     *
     * @see https://docs.asaas.com/reference/remover-cobranca
     */
    public function delete(string $id): bool
    {
        /** @var array{deleted?: bool} $response */
        $response = $this->client->delete('/v3/payments/'.$id);

        return $response['deleted'] ?? false;
    }

    /**
     * Restore a previously deleted payment.
     *
     * @see https://docs.asaas.com/reference/restaurar-cobranca-removida
     */
    public function restore(string $id): PaymentData
    {
        /** @var PaymentArray $response */
        $response = $this->client->post(sprintf('/v3/payments/%s/restore', $id));

        return PaymentData::fromArray($response);
    }

    /**
     * Refund a confirmed payment, optionally specifying a partial value.
     *
     * @see https://docs.asaas.com/reference/estornar-cobranca
     */
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

    /**
     * Mark a payment as received in cash outside of Asaas.
     *
     * @see https://docs.asaas.com/reference/confirmar-recebimento-em-dinheiro
     */
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

    /**
     * Retrieve the digitable bill line, nosso número and bar code for a bank slip payment.
     *
     * @see https://docs.asaas.com/reference/recuperar-linha-digitavel-de-cobranca
     */
    public function getIdentificationField(string $id): PaymentBillingInfoBankSlipData
    {
        /** @var array{identificationField?: null|string, nossoNumero?: null|string, barCode?: null|string} $response */
        $response = $this->client->get(sprintf('/v3/payments/%s/identificationField', $id));

        return PaymentBillingInfoBankSlipData::fromArray($response);
    }

    /**
     * Retrieve billing info for a payment (Pix QR code, credit card token, or bank slip data).
     *
     * @see https://docs.asaas.com/reference/recuperar-informacoes-de-cobranca
     */
    public function getBillingInfo(string $id): PaymentBillingInfoData
    {
        /** @var array{pix?: null|array{encodedImage?: null|string, payload?: null|string, expirationDate?: null|string, description?: null|string}, creditCard?: null|array{creditCardNumber?: null|string, creditCardBrand?: null|string, creditCardToken?: null|string}, bankSlip?: null|array{identificationField?: null|string, nossoNumero?: null|string, barCode?: null|string, bankSlipUrl?: null|string, daysAfterDueDateToRegistrationCancellation?: null|int}} $response */
        $response = $this->client->get(sprintf('/v3/payments/%s/billingInfo', $id));

        return PaymentBillingInfoData::fromArray($response);
    }

    /**
     * Retrieve the current status of a payment.
     *
     * @see https://docs.asaas.com/reference/recuperar-status-de-cobranca
     */
    public function getStatus(string $id): string
    {
        /** @var array{status: string} $response */
        $response = $this->client->get(sprintf('/v3/payments/%s/status', $id));

        return $response['status'];
    }

    /**
     * Retrieve the Pix QR code (encoded image and copy-paste payload) for a Pix payment.
     *
     * @see https://docs.asaas.com/reference/recuperar-qr-code-para-pagamento-via-pix
     */
    public function getPixQrCode(string $id): PaymentPixData
    {
        /** @var array{encodedImage?: null|string, payload?: null|string, expirationDate?: null|string, description?: null|string} $response */
        $response = $this->client->get(sprintf('/v3/payments/%s/pixQrCode', $id));

        return PaymentPixData::fromArray($response);
    }
}
