<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

final class MemberLimitExceededException extends DomainException
{
    public function __construct(string $message = 'El socio supera el límite máximo de 3 libros sin devolver.')
    {
        parent::__construct($message, 'MEMBER_LIMIT_EXCEEDED', 422);
    }
}
