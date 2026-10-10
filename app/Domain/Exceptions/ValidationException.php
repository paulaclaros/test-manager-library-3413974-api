<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

final class ValidationException extends DomainException
{
    /**
     * @param array<int, array{field: string, issue: string}> $details
     */
    public function __construct(string $message = 'Error de validación en los datos enviados.', array $details = [])
    {
        parent::__construct($message, 'VALIDATION_ERROR', 422, $details);
    }
}
