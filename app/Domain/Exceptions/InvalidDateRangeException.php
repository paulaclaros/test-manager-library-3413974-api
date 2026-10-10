<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

final class InvalidDateRangeException extends DomainException
{
    public function __construct(string $message = 'El rango de fechas es inválido: la fecha final no puede ser anterior a la inicial.')
    {
        parent::__construct($message, 'INVALID_DATE_RANGE', 422);
    }
}
