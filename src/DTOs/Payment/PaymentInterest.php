<?php

declare(strict_types=1);

namespace LumenSistemas\Asaas\DTOs\Payment;

final readonly class PaymentInterest
{
    public function __construct(
        public float $value,
    ) {}

    /**
     * @param array{value: float|int} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(value: (float) $data['value']);
    }

    /** @return array{value: float} */
    public function toArray(): array
    {
        return ['value' => $this->value];
    }
}
