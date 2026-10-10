<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

final class ForbiddenException extends DomainException
{
    public function __construct(string $message = 'Rol insuficiente para realizar esta operación.')
    {
        parent::__construct($message, 'FORBIDDEN_ROLE', 403);
    }
}
