<?php

declare(strict_types=1);

namespace LumenSistemas\Asaas\DTOs\Payment;

final readonly class PaymentFine
{
    public function __construct(
        public float $value,
        /** @var null|'FIXED'|'PERCENTAGE' */
        public ?string $type = null,
    ) {}

    /**
     * @param array{value: float|int, type?: null|'FIXED'|'PERCENTAGE'} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            value: (float) $data['value'],
            type: $data['type'] ?? null,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'value' => $this->value,
            'type' => $this->type,
        ], fn (mixed $v): bool => $v !== null);
    }
}
