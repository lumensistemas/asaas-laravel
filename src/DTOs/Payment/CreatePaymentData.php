<?php

declare(strict_types=1);

namespace LumenSistemas\Asaas\DTOs\Payment;

use InvalidArgumentException;

readonly class CreatePaymentData
{
    /**
     * @param null|array<string, mixed> $discount
     * @param null|array<string, mixed> $interest
     * @param null|array<string, mixed> $fine
     * @param null|array<string, mixed> $split
     * @param null|array<string, mixed> $callback
     */
    public function __construct(
        public string $customer,
        public string $billingType,
        public float $value,
        public string $dueDate,
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
    ) {
        if (mb_trim($this->customer) === '') {
            throw new InvalidArgumentException('Payment customer cannot be empty.');
        }

        if (mb_trim($this->billingType) === '') {
            throw new InvalidArgumentException('Payment billingType cannot be empty.');
        }

        if (mb_trim($this->dueDate) === '') {
            throw new InvalidArgumentException('Payment dueDate cannot be empty.');
        }

        if ($this->value <= 0) {
            throw new InvalidArgumentException('Payment value must be greater than zero.');
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'customer' => $this->customer,
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
