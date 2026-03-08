<?php

declare(strict_types=1);

namespace LumenSistemas\Asaas\Http\Middleware;

use Closure;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Http\Request;
use LumenSistemas\Asaas\Webhook\WebhookSignatureVerifier;
use Symfony\Component\HttpFoundation\Response;

final readonly class VerifyAsaasWebhook
{
    public function __construct(
        private WebhookSignatureVerifier $verifier,
        private ConfigRepository $config,
    ) {}

    /**
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var string $expectedToken */
        $expectedToken = $this->config->get('asaas.webhook_token', '');

        if (!$this->verifier->verify($request->headers->all(), $expectedToken)) {
            abort(401, 'Asaas webhook signature mismatch.');
        }

        return $next($request);
    }
}
