<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

final class MemberHasOverdueException extends DomainException
{
    public function __construct(string $message = 'El socio tiene libros vencidos sin devolver y no puede solicitar préstamos.')
    {
        parent::__construct($message, 'MEMBER_HAS_OVERDUE', 409);
    }
}
