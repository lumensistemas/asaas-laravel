<?php

namespace LumenSistemas\Asaas\DTOs\Customer;

class CustomerListResult
{
    /** @param CustomerData[] $data */
    public function __construct(
        public readonly array $data,
        public readonly bool $hasMore,
        public readonly int $totalCount,
        public readonly int $limit,
        public readonly int $offset,
    ) {}

    /** @param array<string, mixed> $response */
    public static function fromArray(array $response): self
    {
        return new self(
            data: array_map(fn (array $item) => CustomerData::fromArray($item), $response['data'] ?? []),
            hasMore: $response['hasMore'] ?? false,
            totalCount: $response['totalCount'] ?? 0,
            limit: $response['limit'] ?? 10,
            offset: $response['offset'] ?? 0,
        );
    }
}
