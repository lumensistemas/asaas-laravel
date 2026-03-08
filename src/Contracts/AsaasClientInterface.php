<?php

declare(strict_types=1);

namespace LumenSistemas\Asaas\Contracts;

interface AsaasClientInterface
{
    public function withApiKey(string $apiKey): static;

    /** @param array<string, string> $headers */
    public function withHeaders(array $headers): static;

    /** @param array<string, mixed> $query */
    public function get(string $path, array $query = []): mixed;

    /** @param array<string, mixed> $data */
    public function post(string $path, array $data = []): mixed;

    /** @param array<string, mixed> $data */
    public function put(string $path, array $data = []): mixed;

    public function delete(string $path): mixed;
}
