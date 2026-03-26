<?php

declare(strict_types=1);

namespace LumenSistemas\Asaas\DTOs\Bill;

final readonly class BillListFilters
{
    public function __construct(
        public int $offset = 0,
        public int $limit = 10,
    ) {}

    /** @return array<string, int> */
    public function toArray(): array
    {
        return [
            'offset' => max(0, $this->offset),
            'limit' => min(100, max(1, $this->limit)),
        ];
    }
}
