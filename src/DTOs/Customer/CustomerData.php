<?php

declare(strict_types=1);

namespace LumenSistemas\Asaas\DTOs\Customer;

final readonly class CustomerData
{
    public function __construct(
        public string $id,
        public string $name,
        public string $cpfCnpj,
        public string $personType,
        public bool $deleted,
        public ?string $dateCreated = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $mobilePhone = null,
        public ?string $address = null,
        public ?string $addressNumber = null,
        public ?string $complement = null,
        public ?string $province = null,
        public ?int $city = null,
        public ?string $cityName = null,
        public ?string $state = null,
        public ?string $country = null,
        public ?string $postalCode = null,
        public ?string $additionalEmails = null,
        public ?string $externalReference = null,
        public bool $notificationDisabled = false,
        public ?string $observations = null,
        public bool $foreignCustomer = false,
        public ?string $groupName = null,
        public ?string $company = null,
    ) {}

    /**
     * @param array{
     *     id: string,
     *     name: string,
     *     cpfCnpj: string,
     *     personType?: string,
     *     deleted?: bool,
     *     dateCreated?: null|string,
     *     email?: null|string,
     *     phone?: null|string,
     *     mobilePhone?: null|string,
     *     address?: null|string,
     *     addressNumber?: null|string,
     *     complement?: null|string,
     *     province?: null|string,
     *     city?: null|int,
     *     cityName?: null|string,
     *     state?: null|string,
     *     country?: null|string,
     *     postalCode?: null|string,
     *     additionalEmails?: null|string,
     *     externalReference?: null|string,
     *     notificationDisabled?: bool,
     *     observations?: null|string,
     *     foreignCustomer?: bool,
     *     groupName?: null|string,
     *     company?: null|string,
     * } $data
     */
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
