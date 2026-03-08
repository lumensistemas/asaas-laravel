<?php

declare(strict_types=1);

namespace LumenSistemas\Asaas\DTOs\Customer;

final readonly class UpdateCustomerData
{
    public function __construct(
        public ?string $name = null,
        public ?string $cpfCnpj = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $mobilePhone = null,
        public ?string $address = null,
        public ?string $addressNumber = null,
        public ?string $complement = null,
        public ?string $province = null,
        public ?string $postalCode = null,
        public ?string $externalReference = null,
        public ?bool $notificationDisabled = null,
        public ?string $additionalEmails = null,
        public ?string $municipalInscription = null,
        public ?string $stateInscription = null,
        public ?string $observations = null,
        public ?string $groupName = null,
        public ?string $company = null,
        public ?bool $foreignCustomer = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'cpfCnpj' => $this->cpfCnpj,
            'email' => $this->email,
            'phone' => $this->phone,
            'mobilePhone' => $this->mobilePhone,
            'address' => $this->address,
            'addressNumber' => $this->addressNumber,
            'complement' => $this->complement,
            'province' => $this->province,
            'postalCode' => $this->postalCode,
            'externalReference' => $this->externalReference,
            'notificationDisabled' => $this->notificationDisabled,
            'additionalEmails' => $this->additionalEmails,
            'municipalInscription' => $this->municipalInscription,
            'stateInscription' => $this->stateInscription,
            'observations' => $this->observations,
            'groupName' => $this->groupName,
            'company' => $this->company,
            'foreignCustomer' => $this->foreignCustomer,
        ], fn (null|bool|string $v): bool => $v !== null);
    }
}
