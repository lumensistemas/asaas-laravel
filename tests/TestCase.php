<?php

namespace LumenSistemas\Asaas\Tests;

use Illuminate\Support\Facades\Http;
use LumenSistemas\Asaas\AsaasServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
    }

    protected function getPackageProviders($app): array
    {
        return [AsaasServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('asaas.api_key', 'test_api_key');
        $app['config']->set('asaas.environment', 'sandbox');
    }
}
