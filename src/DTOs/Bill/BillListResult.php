<?php

declare(strict_types=1);

namespace LumenSistemas\Asaas\DTOs\Bill;

final readonly class BillListResult
{
    /**
     * @param BillData[] $data
     */
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
     *         status: string,
     *         value: float|int,
     *         identificationField: string,
     *         dueDate: string,
     *         scheduleDate: string,
     *         canBeCancelled: bool,
     *         discount?: null|float|int,
     *         interest?: null|float|int,
     *         fine?: null|float|int,
     *         paymentDate?: null|string,
     *         fee?: null|float|int,
     *         description?: null|string,
     *         companyName?: null|string,
     *         transactionReceiptUrl?: null|string,
     *         externalReference?: null|string,
     *         failReasons?: list<string>,
     *     }>,
     *     hasMore?: bool,
     *     totalCount?: int,
     *     limit?: int,
     *     offset?: int,
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            data: array_map(BillData::fromArray(...), $data['data'] ?? []),
            hasMore: $data['hasMore'] ?? false,
            totalCount: $data['totalCount'] ?? 0,
            limit: $data['limit'] ?? 10,
            offset: $data['offset'] ?? 0,
        );
    }
}
