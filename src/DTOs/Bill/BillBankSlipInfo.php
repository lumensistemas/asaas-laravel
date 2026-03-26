<?php

declare(strict_types=1);

namespace LumenSistemas\Asaas\DTOs\Bill;

final readonly class BillBankSlipInfo
{
    public function __construct(
        public string $identificationField,
        public float $value,
        public string $dueDate,
        public string $bank,
        public string $beneficiaryCpfCnpj,
        public string $beneficiaryName,
        public bool $allowChangeValue,
        public float $minValue,
        public float $maxValue,
        public float $discountValue,
        public float $interestValue,
        public float $fineValue,
        public float $originalValue,
        public float $totalDiscountValue,
        public float $totalAdditionalValue,
        public bool $isOverdue,
        public ?string $companyName = null,
    ) {}

    /**
     * @param array{
     *     identificationField: string,
     *     value: float|int,
     *     dueDate: string,
     *     bank: string,
     *     beneficiaryCpfCnpj: string,
     *     beneficiaryName: string,
     *     allowChangeValue: bool,
     *     minValue: float|int,
     *     maxValue: float|int,
     *     discountValue: float|int,
     *     interestValue: float|int,
     *     fineValue: float|int,
     *     originalValue: float|int,
     *     totalDiscountValue: float|int,
     *     totalAdditionalValue: float|int,
     *     isOverdue: bool,
     *     companyName?: null|string,
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            identificationField: $data['identificationField'],
            value: (float) $data['value'],
            dueDate: $data['dueDate'],
            bank: $data['bank'],
            beneficiaryCpfCnpj: $data['beneficiaryCpfCnpj'],
            beneficiaryName: $data['beneficiaryName'],
            allowChangeValue: $data['allowChangeValue'],
            minValue: (float) $data['minValue'],
            maxValue: (float) $data['maxValue'],
            discountValue: (float) $data['discountValue'],
            interestValue: (float) $data['interestValue'],
            fineValue: (float) $data['fineValue'],
            originalValue: (float) $data['originalValue'],
            totalDiscountValue: (float) $data['totalDiscountValue'],
            totalAdditionalValue: (float) $data['totalAdditionalValue'],
            isOverdue: $data['isOverdue'],
            companyName: $data['companyName'] ?? null,
        );
    }
}
