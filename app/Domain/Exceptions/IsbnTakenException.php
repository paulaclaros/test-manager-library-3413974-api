<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

final class IsbnTakenException extends DomainException
{
    public function __construct(string $message = 'El ISBN ya está registrado para otro libro activo.')
    {
        parent::__construct($message, 'ISBN_TAKEN', 409);
    }
}
