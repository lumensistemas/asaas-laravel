<?php

declare(strict_types=1);

namespace LumenSistemas\Asaas;

use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use LumenSistemas\Asaas\Contracts\AsaasClientInterface;
use LumenSistemas\Asaas\Http\Middleware\VerifyAsaasWebhook;
use Override;

class AsaasServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/asaas.php', 'asaas');

        $this->app->singleton(AsaasClientInterface::class, function (Container $app): AsaasClient {
            /** @var ConfigRepository $configRepo */
            $configRepo = $app->make(ConfigRepository::class);
            /** @var array{environment?: string, api_key?: string, timeout?: int, connect_timeout?: int} $config */
            $config = $configRepo->get('asaas', []);

            return new AsaasClient(
                environment: AsaasEnvironment::from($config['environment'] ?? 'sandbox'),
                defaultApiKey: $config['api_key'] ?? '',
                timeout: $config['timeout'] ?? 30,
                connectTimeout: $config['connect_timeout'] ?? 10,
            );
        });

        $this->app->singleton(Asaas::class, fn (Container $app): Asaas => new Asaas($app->make(AsaasClientInterface::class)));
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/asaas.php' => config_path('asaas.php'),
            ], 'asaas-config');
        }

        /** @var Router $router */
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('asaas.webhook', VerifyAsaasWebhook::class);
    }
}
