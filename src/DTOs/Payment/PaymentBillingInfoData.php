<?php

declare(strict_types=1);

namespace LumenSistemas\Asaas\DTOs\Payment;

final readonly class PaymentBillingInfoData
{
    public function __construct(
        public ?PaymentBillingInfoPixData $pix = null,
        public ?PaymentBillingInfoCreditCardData $creditCard = null,
        public ?PaymentBillingInfoBankSlipData $bankSlip = null,
    ) {}

    /**
     * @param array{
     *     pix?: null|array{encodedImage?: null|string, payload?: null|string, expirationDate?: null|string, description?: null|string},
     *     creditCard?: null|array{creditCardNumber?: null|string, creditCardBrand?: null|string, creditCardToken?: null|string},
     *     bankSlip?: null|array{identificationField?: null|string, nossoNumero?: null|string, barCode?: null|string, bankSlipUrl?: null|string, daysAfterDueDateToRegistrationCancellation?: null|int},
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            pix: isset($data['pix']) ? PaymentBillingInfoPixData::fromArray($data['pix']) : null,
            creditCard: isset($data['creditCard']) ? PaymentBillingInfoCreditCardData::fromArray($data['creditCard']) : null,
            bankSlip: isset($data['bankSlip']) ? PaymentBillingInfoBankSlipData::fromArray($data['bankSlip']) : null,
        );
    }
}
