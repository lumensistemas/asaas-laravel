<?php

declare(strict_types=1);

namespace LumenSistemas\Asaas\DTOs\Webhook;

final readonly class WebhookListResult
{
    /** @param WebhookData[] $data */
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
     *         name: string,
     *         url: string,
     *         enabled: bool,
     *         interrupted: bool,
     *         hasAuthToken: bool,
     *         sendType: string,
     *         apiVersion: int,
     *         penalizedRequestsCount: int,
     *         events: list<string>,
     *         email?: null|string,
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
            data: array_map(WebhookData::fromArray(...), $response['data'] ?? []),
            hasMore: $response['hasMore'] ?? false,
            totalCount: $response['totalCount'] ?? 0,
            limit: $response['limit'] ?? 10,
            offset: $response['offset'] ?? 0,
        );
    }
}
