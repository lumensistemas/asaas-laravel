<?php

declare(strict_types=1);

namespace LumenSistemas\Asaas\Exceptions;

use RuntimeException;
use Throwable;

class AsaasException extends RuntimeException
{
    /** @param array<array{code: string, description: string}> $errors */
    public function __construct(
        string $message,
        public readonly array $errors = [],
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    /** @return array<array{code: string, description: string}> */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
