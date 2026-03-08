<?php

declare(strict_types=1);

namespace LumenSistemas\Asaas\Exceptions;

use Illuminate\Http\Client\Response;
use Throwable;

class AsaasApiException extends AsaasException
{
    public function __construct(
        public readonly Response $response,
        array $errors = [],
        ?Throwable $previous = null,
    ) {
        $statusCode = $response->status();
        $message = sprintf('Asaas API error [%d]', $statusCode);

        if ($errors !== []) {
            $descriptions = array_column($errors, 'description');
            $message .= ': '.implode(', ', $descriptions);
        }

        parent::__construct($message, $errors, $statusCode, $previous);
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

    public function getStatusCode(): int
    {
        return $this->response->status();
    }
}
