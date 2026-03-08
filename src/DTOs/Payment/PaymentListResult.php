<?php

declare(strict_types=1);

namespace LumenSistemas\Asaas\DTOs\Payment;

readonly class PaymentListResult
{
    /** @param PaymentData[] $data */
    public function __construct(
        public array $data,
        public bool $hasMore,
        public int $totalCount,
        public int $limit,
        public int $offset,
    ) {}

    /**
     * @param array{
     *     data?: array<int, array{
     *         id: string,
     *         customer: string,
     *         billingType: string,
     *         value: float|int,
     *         netValue: float|int,
     *         status: string,
     *         dueDate: string,
     *         deleted: bool,
     *         object?: null|string,
     *         dateCreated?: null|string,
     *         subscription?: null|string,
     *         installment?: null|string,
     *         checkoutSession?: null|string,
     *         paymentLink?: null|string,
     *         originalValue?: null|float|int,
     *         interestValue?: null|float|int,
     *         description?: null|string,
     *         originalDueDate?: null|string,
     *         paymentDate?: null|string,
     *         clientPaymentDate?: null|string,
     *         canBePaidAfterDueDate?: null|bool,
     *         externalReference?: null|string,
     *         invoiceUrl?: null|string,
     *         invoiceNumber?: null|string,
     *         nossoNumero?: null|string,
     *         bankSlipUrl?: null|string,
     *         transactionReceiptUrl?: null|string,
     *         creditDate?: null|string,
     *         estimatedCreditDate?: null|string,
     *         anticipated?: null|bool,
     *         anticipable?: null|bool,
     *         installmentNumber?: null|int,
     *         pixTransaction?: null|string,
     *         pixQrCodeId?: null|string,
     *         postalService?: null|bool,
     *         daysAfterDueDateToRegistrationCancellation?: null|int,
     *         discount?: null|array<string, mixed>,
     *         interest?: null|array<string, mixed>,
     *         fine?: null|array<string, mixed>,
     *         split?: null|array<string, mixed>,
     *         creditCard?: null|array<string, mixed>,
     *         chargeback?: null|array<string, mixed>,
     *         escrow?: null|array<string, mixed>,
     *         refunds?: null|array<string, mixed>,
     *         callback?: null|array<string, mixed>,
     *     }>,
     *     hasMore?: bool,
     *     totalCount?: int,
     *     limit?: int,
     *     offset?: int,
     * } $response
     */
    public static function fromArray(array $response): self
    {
        return new self(
            data: array_map(PaymentData::fromArray(...), $response['data'] ?? []),
            hasMore: $response['hasMore'] ?? false,
            totalCount: $response['totalCount'] ?? 0,
            limit: $response['limit'] ?? 10,
            offset: $response['offset'] ?? 0,
        );
    }
}
