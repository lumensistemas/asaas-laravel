<?php

namespace LumenSistemas\Asaas;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use LumenSistemas\Asaas\Contracts\AsaasClientInterface;
use LumenSistemas\Asaas\Exceptions\AsaasApiException;
use SensitiveParameter;

class AsaasClient implements AsaasClientInterface
{
    private ?string $apiKey = null;

    /**
     * @param array<string, string> $extraHeaders
     */
    public function __construct(
        private readonly AsaasEnvironment $environment,
        #[SensitiveParameter] private readonly string $defaultApiKey,
        private readonly int $timeout = 30,
        private readonly int $connectTimeout = 10,
        private readonly string $userAgent = 'lumensistemas/asaas-laravel',
        private array $extraHeaders = []
    ) {}

    public function withApiKey(#[SensitiveParameter] string $apiKey): static
    {
        $clone = clone $this;
        $clone->apiKey = $apiKey;

        return $clone;
    }

    /** @param array<string, string> $headers */
    public function withHeaders(array $headers): static
    {
        $clone = clone $this;
        $clone->extraHeaders = array_merge($this->extraHeaders, $headers);

        return $clone;
    }

    public function get(string $path, array $query = []): mixed
    {
        return $this->decode($this->pending()->get($path, $query));
    }

    public function post(string $path, array $data = []): mixed
    {
        return $this->decode($this->pending()->post($path, $data));
    }

    public function put(string $path, array $data = []): mixed
    {
        return $this->decode($this->pending()->put($path, $data));
    }

    public function delete(string $path): mixed
    {
        return $this->decode($this->pending()->delete($path));
    }

    private function pending(): PendingRequest
    {
        return Http::baseUrl($this->environment->baseUrl())
            ->timeout($this->timeout)
            ->connectTimeout($this->connectTimeout)
            ->acceptJson()
            ->asJson()
            ->withUserAgent($this->userAgent)
            ->withHeader('access_token', $this->apiKey ?? $this->defaultApiKey)
            ->withHeaders($this->extraHeaders);
    }

    private function decode(Response $response): mixed
    {
        if ($response->failed()) {
            throw new AsaasApiException($response, $response->json('errors') ?? []);
        }

        return $response->body() !== '' ? $response->json() : null;
    }
}
