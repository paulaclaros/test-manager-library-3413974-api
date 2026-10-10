<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use Exception;

abstract class DomainException extends Exception
{
    /**
     * @param array<int, array{field: string, issue: string}> $details
     */
    public function __construct(
        string $message,
        private readonly string $errorCode,
        private readonly int $httpStatus,
        private readonly array $details = []
    ) {
        parent::__construct($message, $httpStatus);
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getHttpStatus(): int
    {
        return $this->httpStatus;
    }

    /**
     * @return array<int, array{field: string, issue: string}>
     */
    public function getDetails(): array
    {
        return $this->details;
    }
}
