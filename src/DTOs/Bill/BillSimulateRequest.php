<?php

declare(strict_types=1);

namespace LumenSistemas\Asaas\DTOs\Bill;

final readonly class BillSimulateRequest
{
    public function __construct(
        public ?string $identificationField = null,
        public ?string $barCode = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'identificationField' => $this->identificationField,
            'barCode' => $this->barCode,
        ], fn (mixed $v): bool => $v !== null);
    }
}
