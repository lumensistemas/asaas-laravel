<?php

declare(strict_types=1);

namespace LumenSistemas\Asaas\DTOs\Payment;

use LumenSistemas\Asaas\Enums\Payment\PaymentBillingType;

final readonly class UpdatePaymentData
{
    /**
     * @param null|array<int, array{walletId: string, fixedValue?: null|float, percentualValue?: null|float, totalFixedValue?: null|float, externalReference?: null|string, description?: null|string}> $split
     * @param null|array{successUrl: string, autoRedirect?: null|bool} $callback
     */
    public function __construct(
        public ?PaymentBillingType $billingType = null,
        public ?float $value = null,
        public ?string $dueDate = null,
        public ?string $description = null,
        public ?string $externalReference = null,
        public ?int $daysAfterDueDateToRegistrationCancellation = null,
        public ?int $installmentCount = null,
        public ?float $totalValue = null,
        public ?float $installmentValue = null,
        public ?bool $postalService = null,
        public ?string $pixAutomaticAuthorizationId = null,
        public ?PaymentDiscount $discount = null,
        public ?PaymentInterest $interest = null,
        public ?PaymentFine $fine = null,
        public ?array $split = null,
        public ?array $callback = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'billingType' => $this->billingType?->value,
            'value' => $this->value,
            'dueDate' => $this->dueDate,
            'description' => $this->description,
            'externalReference' => $this->externalReference,
            'daysAfterDueDateToRegistrationCancellation' => $this->daysAfterDueDateToRegistrationCancellation,
            'installmentCount' => $this->installmentCount,
            'totalValue' => $this->totalValue,
            'installmentValue' => $this->installmentValue,
            'postalService' => $this->postalService,
            'pixAutomaticAuthorizationId' => $this->pixAutomaticAuthorizationId,
            'discount' => $this->discount?->toArray(),
            'interest' => $this->interest?->toArray(),
            'fine' => $this->fine?->toArray(),
            'split' => $this->split,
            'callback' => $this->callback,
        ], fn (mixed $v): bool => $v !== null);
    }
}
