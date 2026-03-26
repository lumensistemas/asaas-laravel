<?php

declare(strict_types=1);

namespace LumenSistemas\Asaas\DTOs\Bill;

use InvalidArgumentException;

final readonly class CreateBillData
{
    public function __construct(
        public string $identificationField,
        public ?string $scheduleDate = null,
        public ?string $description = null,
        public ?float $discount = null,
        public ?float $interest = null,
        public ?float $fine = null,
        public ?string $dueDate = null,
        public ?float $value = null,
        public ?string $externalReference = null,
    ) {
        if (mb_trim($this->identificationField) === '') {
            throw new InvalidArgumentException('Bill identificationField cannot be empty.');
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'identificationField' => $this->identificationField,
            'scheduleDate' => $this->scheduleDate,
            'description' => $this->description,
            'discount' => $this->discount,
            'interest' => $this->interest,
            'fine' => $this->fine,
            'dueDate' => $this->dueDate,
            'value' => $this->value,
            'externalReference' => $this->externalReference,
        ], fn (mixed $v): bool => $v !== null);
    }
}
