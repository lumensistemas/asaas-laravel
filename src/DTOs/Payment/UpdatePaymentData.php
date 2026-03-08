<?php

declare(strict_types=1);

namespace LumenSistemas\Asaas\DTOs\Payment;

readonly class UpdatePaymentData
{
    /**
     * @param null|array<string, mixed> $discount
     * @param null|array<string, mixed> $interest
     * @param null|array<string, mixed> $fine
     * @param null|array<string, mixed> $split
     * @param null|array<string, mixed> $callback
     */
    public function __construct(
        public ?string $billingType = null,
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
        public ?array $discount = null,
        public ?array $interest = null,
        public ?array $fine = null,
        public ?array $split = null,
        public ?array $callback = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'billingType' => $this->billingType,
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
            'discount' => $this->discount,
            'interest' => $this->interest,
            'fine' => $this->fine,
            'split' => $this->split,
            'callback' => $this->callback,
        ], fn (mixed $v): bool => $v !== null);
    }
}
