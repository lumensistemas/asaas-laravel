<?php

namespace LumenSistemas\Asaas;

enum AsaasEnvironment: string
{
    case Production = 'production';
    case Sandbox    = 'sandbox';

    public function baseUrl(): string
    {
        return match ($this) {
            self::Production => 'https://api.asaas.com',
            self::Sandbox    => 'https://api-sandbox.asaas.com',
        };
    }

    public static function fromConfig(string $value): self
    {
        return self::from($value);
    }
}
