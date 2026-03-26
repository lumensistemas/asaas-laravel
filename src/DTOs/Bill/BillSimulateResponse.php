<?php

declare(strict_types=1);

namespace LumenSistemas\Asaas\DTOs\Bill;

final readonly class BillSimulateResponse
{
    public function __construct(
        public string $minimumScheduleDate,
        public float $fee,
        public BillBankSlipInfo $bankSlipInfo,
    ) {}

    /**
     * @param array{
     *     minimumScheduleDate: string,
     *     fee: float|int,
     *     bankSlipInfo: array{
     *         identificationField: string,
     *         value: float|int,
     *         dueDate: string,
     *         bank: string,
     *         beneficiaryCpfCnpj: string,
     *         beneficiaryName: string,
     *         allowChangeValue: bool,
     *         minValue: float|int,
     *         maxValue: float|int,
     *         discountValue: float|int,
     *         interestValue: float|int,
     *         fineValue: float|int,
     *         originalValue: float|int,
     *         totalDiscountValue: float|int,
     *         totalAdditionalValue: float|int,
     *         isOverdue: bool,
     *         companyName?: null|string,
     *     },
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            minimumScheduleDate: $data['minimumScheduleDate'],
            fee: (float) $data['fee'],
            bankSlipInfo: BillBankSlipInfo::fromArray($data['bankSlipInfo']),
        );
    }
}
