<?php

declare(strict_types=1);

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

    /**
     * @param array{
     *     data?: array<int, array{
     *         id: string,
     *         name: string,
     *         cpfCnpj: string,
     *         personType?: string,
     *         deleted?: bool,
     *         dateCreated?: null|string,
     *         email?: null|string,
     *         phone?: null|string,
     *         mobilePhone?: null|string,
     *         address?: null|string,
     *         addressNumber?: null|string,
     *         complement?: null|string,
     *         province?: null|string,
     *         city?: null|int,
     *         cityName?: null|string,
     *         state?: null|string,
     *         country?: null|string,
     *         postalCode?: null|string,
     *         additionalEmails?: null|string,
     *         externalReference?: null|string,
     *         notificationDisabled?: bool,
     *         observations?: null|string,
     *         foreignCustomer?: bool,
     *         groupName?: null|string,
     *         company?: null|string,
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
            data: array_map(CustomerData::fromArray(...), $response['data'] ?? []),
            hasMore: $response['hasMore'] ?? false,
            totalCount: $response['totalCount'] ?? 0,
            limit: $response['limit'] ?? 10,
            offset: $response['offset'] ?? 0,
        );
    }
}
