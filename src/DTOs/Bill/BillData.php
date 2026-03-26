<?php

declare(strict_types=1);

namespace LumenSistemas\Asaas\DTOs\Bill;

final readonly class BillData
{
    /**
     * @param list<string> $failReasons
     */
    public function __construct(
        public string $id,
        public string $status,
        public float $value,
        public string $identificationField,
        public string $dueDate,
        public string $scheduleDate,
        public bool $canBeCancelled,
        public ?float $discount = null,
        public ?float $interest = null,
        public ?float $fine = null,
        public ?string $paymentDate = null,
        public ?float $fee = null,
        public ?string $description = null,
        public ?string $companyName = null,
        public ?string $transactionReceiptUrl = null,
        public ?string $externalReference = null,
        public array $failReasons = [],
    ) {}

    /**
     * @param array{
     *     id: string,
     *     status: string,
     *     value: float|int,
     *     identificationField: string,
     *     dueDate: string,
     *     scheduleDate: string,
     *     canBeCancelled: bool,
     *     discount?: null|float|int,
     *     interest?: null|float|int,
     *     fine?: null|float|int,
     *     paymentDate?: null|string,
     *     fee?: null|float|int,
     *     description?: null|string,
     *     companyName?: null|string,
     *     transactionReceiptUrl?: null|string,
     *     externalReference?: null|string,
     *     failReasons?: list<string>,
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            status: $data['status'],
            value: (float) $data['value'],
            identificationField: $data['identificationField'],
            dueDate: $data['dueDate'],
            scheduleDate: $data['scheduleDate'],
            canBeCancelled: $data['canBeCancelled'],
            discount: isset($data['discount']) ? (float) $data['discount'] : null,
            interest: isset($data['interest']) ? (float) $data['interest'] : null,
            fine: isset($data['fine']) ? (float) $data['fine'] : null,
            paymentDate: $data['paymentDate'] ?? null,
            fee: isset($data['fee']) ? (float) $data['fee'] : null,
            description: $data['description'] ?? null,
            companyName: $data['companyName'] ?? null,
            transactionReceiptUrl: $data['transactionReceiptUrl'] ?? null,
            externalReference: $data['externalReference'] ?? null,
            failReasons: $data['failReasons'] ?? [],
        );
    }
}
