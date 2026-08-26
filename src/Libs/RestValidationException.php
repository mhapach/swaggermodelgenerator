<?php

namespace mhapach\SwaggerModelGenerator\Libs;

use Exception;
use Throwable;

class RestValidationException extends Exception
{
    private string $body;

    private int $statusCode;

    public function __construct(string $body, int $statusCode, ?Throwable $previous = null)
    {
        $this->body = $body;
        $this->statusCode = $statusCode;

        parent::__construct($body, $statusCode, $previous);
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
