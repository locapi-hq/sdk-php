<?php

declare(strict_types=1);

namespace LocApi\Exceptions;

use Exception;
use Throwable;

class LocApiException extends Exception
{
    private int $statusCode;
    private ?string $errorType;
    private array $errors;
    private array $meta;

    public function __construct(
        string $message,
        int $statusCode,
        ?string $errorType = null,
        array $errors = [],
        array $meta = [],
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $statusCode, $previous);
        $this->statusCode = $statusCode;
        $this->errorType = $errorType;
        $this->errors = $errors;
        $this->meta = $meta;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getErrorType(): ?string
    {
        return $this->errorType;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getMeta(): array
    {
        return $this->meta;
    }
}
