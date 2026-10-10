<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

final class UnauthorizedException extends DomainException
{
    public function __construct(string $message = 'Credenciales inválidas o token no proporcionado.')
    {
        parent::__construct($message, 'INVALID_CREDENTIALS', 401);
    }
}
