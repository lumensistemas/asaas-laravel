<?php

namespace LumenSistemas\Asaas;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use LumenSistemas\Asaas\Contracts\AsaasClientInterface;

class AsaasServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/asaas.php', 'asaas');

        $this->app->singleton(AsaasClientInterface::class, function (Container $app) {
            $config = $app['config']['asaas'];

            return new AsaasClient(
                environment: AsaasEnvironment::from($config['environment'] ?? 'sandbox'),
                defaultApiKey: $config['api_key'] ?? '',
                timeout: $config['timeout'] ?? 30,
                connectTimeout: $config['connect_timeout'] ?? 10,
            );
        });

        $this->app->singleton(Asaas::class, function (Container $app) {
            return new Asaas($app->make(AsaasClientInterface::class));
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/asaas.php' => config_path('asaas.php'),
            ], 'asaas-config');
        }
    }
}
