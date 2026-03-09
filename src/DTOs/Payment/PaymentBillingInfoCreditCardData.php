<?php

declare(strict_types=1);

namespace LumenSistemas\Asaas\DTOs\Payment;

final readonly class PaymentBillingInfoCreditCardData
{
    public function __construct(
        public ?string $creditCardNumber = null,
        public ?string $creditCardBrand = null,
        public ?string $creditCardToken = null,
    ) {}

    /**
     * @param array{
     *     creditCardNumber?: null|string,
     *     creditCardBrand?: null|string,
     *     creditCardToken?: null|string,
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            creditCardNumber: $data['creditCardNumber'] ?? null,
            creditCardBrand: $data['creditCardBrand'] ?? null,
            creditCardToken: $data['creditCardToken'] ?? null,
        );
    }
}
