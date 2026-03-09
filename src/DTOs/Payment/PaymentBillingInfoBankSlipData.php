<?php

declare(strict_types=1);

namespace LumenSistemas\Asaas\DTOs\Payment;

final readonly class PaymentBillingInfoBankSlipData
{
    public function __construct(
        public ?string $identificationField = null,
        public ?string $nossoNumero = null,
        public ?string $barCode = null,
        public ?string $bankSlipUrl = null,
        public ?int $daysAfterDueDateToRegistrationCancellation = null,
    ) {}

    /**
     * @param array{
     *     identificationField?: null|string,
     *     nossoNumero?: null|string,
     *     barCode?: null|string,
     *     bankSlipUrl?: null|string,
     *     daysAfterDueDateToRegistrationCancellation?: null|int,
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            identificationField: $data['identificationField'] ?? null,
            nossoNumero: $data['nossoNumero'] ?? null,
            barCode: $data['barCode'] ?? null,
            bankSlipUrl: $data['bankSlipUrl'] ?? null,
            daysAfterDueDateToRegistrationCancellation: $data['daysAfterDueDateToRegistrationCancellation'] ?? null,
        );
    }
}
