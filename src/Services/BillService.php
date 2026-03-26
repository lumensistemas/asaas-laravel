<?php

declare(strict_types=1);

namespace LumenSistemas\Asaas\Services;

use LumenSistemas\Asaas\Contracts\AsaasClientInterface;
use LumenSistemas\Asaas\DTOs\Bill\BillData;
use LumenSistemas\Asaas\DTOs\Bill\BillListFilters;
use LumenSistemas\Asaas\DTOs\Bill\BillListResult;
use LumenSistemas\Asaas\DTOs\Bill\BillSimulateRequest;
use LumenSistemas\Asaas\DTOs\Bill\BillSimulateResponse;
use LumenSistemas\Asaas\DTOs\Bill\CreateBillData;

/**
 * @phpstan-type BillArray array{id: string, status: string, value: float|int, identificationField: string, dueDate: string, scheduleDate: string, canBeCancelled: bool, discount?: null|float|int, interest?: null|float|int, fine?: null|float|int, paymentDate?: null|string, fee?: null|float|int, description?: null|string, companyName?: null|string, transactionReceiptUrl?: null|string, externalReference?: null|string, failReasons?: list<string>}
 */
class BillService
{
    public function __construct(
        private readonly AsaasClientInterface $client,
    ) {}

    /**
     * List all bill payments.
     *
     * @see https://docs.asaas.com/reference/listar-pagamentos-de-contas
     */
    public function list(?BillListFilters $filters = null): BillListResult
    {
        /** @var array{data?: array<int, BillArray>, hasMore?: bool, totalCount?: int, limit?: int, offset?: int} $response */
        $response = $this->client->get('/v3/bill', ($filters ?? new BillListFilters())->toArray());

        return BillListResult::fromArray($response);
    }

    /**
     * Retrieve a single bill payment by its ID.
     *
     * @see https://docs.asaas.com/reference/recuperar-um-unico-pagamento-de-conta
     */
    public function find(string $id): BillData
    {
        /** @var BillArray $response */
        $response = $this->client->get('/v3/bill/'.$id);

        return BillData::fromArray($response);
    }

    /**
     * Create a new bill payment.
     *
     * @see https://docs.asaas.com/reference/criar-pagamento-de-conta
     */
    public function create(CreateBillData $data): BillData
    {
        /** @var BillArray $response */
        $response = $this->client->post('/v3/bill', $data->toArray());

        return BillData::fromArray($response);
    }

    /**
     * Cancel a bill payment.
     *
     * @see https://docs.asaas.com/reference/cancelar-pagamento-de-conta
     */
    public function cancel(string $id): BillData
    {
        /** @var BillArray $response */
        $response = $this->client->post(sprintf('/v3/bill/%s/cancel', $id));

        return BillData::fromArray($response);
    }

    /**
     * Simulate a bill payment to retrieve fee and bank slip details before committing.
     *
     * @see https://docs.asaas.com/reference/simular-pagamento-de-conta
     */
    public function simulate(BillSimulateRequest $data): BillSimulateResponse
    {
        /** @var array{minimumScheduleDate: string, fee: float|int, bankSlipInfo: array{identificationField: string, value: float|int, dueDate: string, bank: string, beneficiaryCpfCnpj: string, beneficiaryName: string, allowChangeValue: bool, minValue: float|int, maxValue: float|int, discountValue: float|int, interestValue: float|int, fineValue: float|int, originalValue: float|int, totalDiscountValue: float|int, totalAdditionalValue: float|int, isOverdue: bool, companyName?: null|string}} $response */
        $response = $this->client->post('/v3/bill/simulate', $data->toArray());

        return BillSimulateResponse::fromArray($response);
    }
}
