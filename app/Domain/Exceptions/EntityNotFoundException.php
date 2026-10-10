<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

final class EntityNotFoundException extends DomainException
{
    public function __construct(string $message = 'El recurso solicitado no fue encontrado.')
    {
        parent::__construct($message, 'NOT_FOUND', 404);
    }
}
