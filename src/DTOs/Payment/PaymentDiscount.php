<?php

declare(strict_types=1);

namespace LumenSistemas\Asaas\DTOs\Payment;

final readonly class PaymentDiscount
{
    public function __construct(
        public float $value,
        public int $dueDateLimitDays,
        /** @var 'FIXED'|'PERCENTAGE' */
        public string $type,
    ) {}

    /**
     * @param array{value: float|int, dueDateLimitDays: int, type: 'FIXED'|'PERCENTAGE'} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            value: (float) $data['value'],
            dueDateLimitDays: $data['dueDateLimitDays'],
            type: $data['type'],
        );
    }

    /** @return array{value: float, dueDateLimitDays: int, type: string} */
    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'dueDateLimitDays' => $this->dueDateLimitDays,
            'type' => $this->type,
        ];
    }
}
