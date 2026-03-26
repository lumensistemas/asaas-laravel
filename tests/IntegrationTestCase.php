<?php

declare(strict_types=1);

namespace LumenSistemas\Asaas\Tests;

use LumenSistemas\Asaas\AsaasServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

/**
 * Base test case for integration tests that hit the real Asaas API.
 *
 * Required environment variables:
 *   ASAAS_TEST_API_KEY   — A sandbox API key
 *   ASAAS_TEST_ENV       — "sandbox" (default)
 *
 * Run exclusively with:
 *   ./vendor/bin/pest --testsuite=Integration
 */
class IntegrationTestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [AsaasServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('asaas.api_key', (string) env('ASAAS_TEST_API_KEY', ''));
        $app['config']->set('asaas.environment', (string) env('ASAAS_TEST_ENV', 'sandbox'));
        $app['config']->set('asaas.webhook_token', (string) env('ASAAS_WEBHOOK_TOKEN', ''));
    }
}
