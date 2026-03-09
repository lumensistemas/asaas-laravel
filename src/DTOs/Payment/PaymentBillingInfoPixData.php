<?php

declare(strict_types=1);

namespace LumenSistemas\Asaas\DTOs\Payment;

final readonly class PaymentBillingInfoPixData
{
    public function __construct(
        public ?string $encodedImage = null,
        public ?string $payload = null,
        public ?string $expirationDate = null,
        public ?string $description = null,
    ) {}

    /**
     * @param array{
     *     encodedImage?: null|string,
     *     payload?: null|string,
     *     expirationDate?: null|string,
     *     description?: null|string,
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            encodedImage: $data['encodedImage'] ?? null,
            payload: $data['payload'] ?? null,
            expirationDate: $data['expirationDate'] ?? null,
            description: $data['description'] ?? null,
        );
    }
}
