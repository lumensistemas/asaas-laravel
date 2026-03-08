<?php

declare(strict_types=1);

namespace LumenSistemas\Asaas\Facades;

use Illuminate\Support\Facades\Facade;
use LumenSistemas\Asaas\Services\CustomerService;

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
