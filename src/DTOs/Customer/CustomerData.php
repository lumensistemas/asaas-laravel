<?php

namespace LumenSistemas\Asaas\DTOs\Customer;

class CustomerData
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $cpfCnpj,
        public readonly string $personType,
        public readonly bool $deleted,
        public readonly ?string $dateCreated = null,
        public readonly ?string $email = null,
        public readonly ?string $phone = null,
        public readonly ?string $mobilePhone = null,
        public readonly ?string $address = null,
        public readonly ?string $addressNumber = null,
        public readonly ?string $complement = null,
        public readonly ?string $province = null,
        public readonly ?int $city = null,
        public readonly ?string $cityName = null,
        public readonly ?string $state = null,
        public readonly ?string $country = null,
        public readonly ?string $postalCode = null,
        public readonly ?string $additionalEmails = null,
        public readonly ?string $externalReference = null,
        public readonly bool $notificationDisabled = false,
        public readonly ?string $observations = null,
        public readonly bool $foreignCustomer = false,
        public readonly ?string $groupName = null,
        public readonly ?string $company = null,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            cpfCnpj: $data['cpfCnpj'],
            personType: $data['personType'] ?? 'FISICA',
            deleted: $data['deleted'] ?? false,
            dateCreated: $data['dateCreated'] ?? null,
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            mobilePhone: $data['mobilePhone'] ?? null,
            address: $data['address'] ?? null,
            addressNumber: $data['addressNumber'] ?? null,
            complement: $data['complement'] ?? null,
            province: $data['province'] ?? null,
            city: $data['city'] ?? null,
            cityName: $data['cityName'] ?? null,
            state: $data['state'] ?? null,
            country: $data['country'] ?? null,
            postalCode: $data['postalCode'] ?? null,
            additionalEmails: $data['additionalEmails'] ?? null,
            externalReference: $data['externalReference'] ?? null,
            notificationDisabled: $data['notificationDisabled'] ?? false,
            observations: $data['observations'] ?? null,
            foreignCustomer: $data['foreignCustomer'] ?? false,
            groupName: $data['groupName'] ?? null,
            company: $data['company'] ?? null,
        );
    }
}
