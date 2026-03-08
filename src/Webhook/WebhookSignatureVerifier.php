<?php

declare(strict_types=1);

namespace LumenSistemas\Asaas\Webhook;

final class WebhookSignatureVerifier
{
    public const string HEADER = 'asaas-access-token';

    /**
     * Verify an incoming webhook request by extracting the token from the
     * given headers array (e.g. from $request->headers->all()).
     *
     * @param array<string, list<null|string>|string> $headers
     */
    public function verify(array $headers, string $expectedToken): bool
    {
        $received = $this->extractToken($headers);

        if ($received === null) {
            return false;
        }

        return hash_equals($expectedToken, $received);
    }

    /**
     * @param array<string, list<null|string>|string> $headers
     */
    private function extractToken(array $headers): ?string
    {
        $key = mb_strtolower(self::HEADER);

        foreach ($headers as $name => $value) {
            if (mb_strtolower($name) === $key) {
                return is_array($value) ? ($value[0] ?? null) : $value;
            }
        }

        return null;
    }
}
