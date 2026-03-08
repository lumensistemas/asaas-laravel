<?php

namespace LumenSistemas\Asaas\Facades;

use LumenSistemas\Asaas\Services\CustomerService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \LumenSistemas\Asaas\Asaas withApiKey(string $apiKey)
 * @method static CustomerService customers()
 *
 * @see \LumenSistemas\Asaas\Asaas
 */
class Asaas extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \LumenSistemas\Asaas\Asaas::class;
    }
}
