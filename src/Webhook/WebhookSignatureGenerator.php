<?php

declare(strict_types=1);

namespace LumenSistemas\Asaas\Webhook;

final class WebhookSignatureGenerator
{
    /**
     * Generate a cryptographically secure random token suitable for use as
     * the authToken field when creating or updating an Asaas webhook.
     *
     * @param int<1, max> $bytes Number of random bytes (default: 32 → 64 hex chars)
     */
    public function generate(int $bytes = 32): string
    {
        return bin2hex(random_bytes($bytes));
    }
}
