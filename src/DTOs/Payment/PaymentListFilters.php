<?php

declare(strict_types=1);

namespace LumenSistemas\Asaas\DTOs\Payment;

use LumenSistemas\Asaas\Enums\Payment\PaymentBillingType;
use LumenSistemas\Asaas\Enums\Payment\PaymentStatus;

final readonly class PaymentListFilters
{
    public function __construct(
        public int $offset = 0,
        public int $limit = 10,
        public ?string $customer = null,
        public ?PaymentBillingType $billingType = null,
        public ?PaymentStatus $status = null,
        public ?string $subscription = null,
        public ?string $installment = null,
        public ?string $externalReference = null,
        public ?string $invoiceStatus = null,
        public ?string $pixQrCodeId = null,
        public ?string $checkoutSession = null,
        public ?string $customerGroupName = null,
        public ?string $user = null,
        public ?bool $anticipated = null,
        public ?bool $anticipable = null,
        public ?string $dateCreatedGe = null,
        public ?string $dateCreatedLe = null,
        public ?string $paymentDateGe = null,
        public ?string $paymentDateLe = null,
        public ?string $estimatedCreditDateGe = null,
        public ?string $estimatedCreditDateLe = null,
        public ?string $dueDateGe = null,
        public ?string $dueDateLe = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $params = [
            'offset' => $this->offset,
            'limit' => min($this->limit, 100),
        ];

        if ($this->customer !== null) {
            $params['customer'] = $this->customer;
        }

        if ($this->billingType instanceof PaymentBillingType) {
            $params['billingType'] = $this->billingType->value;
        }

        if ($this->status instanceof PaymentStatus) {
            $params['status'] = $this->status->value;
        }

        if ($this->subscription !== null) {
            $params['subscription'] = $this->subscription;
        }

        if ($this->installment !== null) {
            $params['installment'] = $this->installment;
        }

        if ($this->externalReference !== null) {
            $params['externalReference'] = $this->externalReference;
        }

        if ($this->invoiceStatus !== null) {
            $params['invoiceStatus'] = $this->invoiceStatus;
        }

        if ($this->pixQrCodeId !== null) {
            $params['pixQrCodeId'] = $this->pixQrCodeId;
        }

        if ($this->checkoutSession !== null) {
            $params['checkoutSession'] = $this->checkoutSession;
        }

        if ($this->customerGroupName !== null) {
            $params['customerGroupName'] = $this->customerGroupName;
        }

        if ($this->user !== null) {
            $params['user'] = $this->user;
        }

        if ($this->anticipated !== null) {
            $params['anticipated'] = $this->anticipated;
        }

        if ($this->anticipable !== null) {
            $params['anticipable'] = $this->anticipable;
        }

        if ($this->dateCreatedGe !== null) {
            $params['dateCreated[ge]'] = $this->dateCreatedGe;
        }

        if ($this->dateCreatedLe !== null) {
            $params['dateCreated[le]'] = $this->dateCreatedLe;
        }

        if ($this->paymentDateGe !== null) {
            $params['paymentDate[ge]'] = $this->paymentDateGe;
        }

        if ($this->paymentDateLe !== null) {
            $params['paymentDate[le]'] = $this->paymentDateLe;
        }

        if ($this->estimatedCreditDateGe !== null) {
            $params['estimatedCreditDate[ge]'] = $this->estimatedCreditDateGe;
        }

        if ($this->estimatedCreditDateLe !== null) {
            $params['estimatedCreditDate[le]'] = $this->estimatedCreditDateLe;
        }

        if ($this->dueDateGe !== null) {
            $params['dueDate[ge]'] = $this->dueDateGe;
        }

        if ($this->dueDateLe !== null) {
            $params['dueDate[le]'] = $this->dueDateLe;
        }

        return $params;
    }
}
