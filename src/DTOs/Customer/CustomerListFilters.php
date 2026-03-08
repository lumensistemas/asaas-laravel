<?php

declare(strict_types=1);

namespace LumenSistemas\Asaas\DTOs\Customer;

readonly class CustomerListFilters
{
    public function __construct(
        public int $offset = 0,
        public int $limit = 10,
        public ?string $name = null,
        public ?string $email = null,
        public ?string $cpfCnpj = null,
        public ?string $groupName = null,
        public ?string $externalReference = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $params = [
            'offset' => $this->offset,
            'limit' => min($this->limit, 100),
        ];

        if ($this->name !== null) {
            $params['name'] = $this->name;
        }

        if ($this->email !== null) {
            $params['email'] = $this->email;
        }

        if ($this->cpfCnpj !== null) {
            $params['cpfCnpj'] = $this->cpfCnpj;
        }

        if ($this->groupName !== null) {
            $params['groupName'] = $this->groupName;
        }

        if ($this->externalReference !== null) {
            $params['externalReference'] = $this->externalReference;
        }

        return $params;
    }
}
