<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

final class ItemAlreadyReturnedException extends DomainException
{
    public function __construct(string $message = 'La línea de préstamo ya fue devuelta previamente.')
    {
        parent::__construct($message, 'ITEM_ALREADY_RETURNED', 409);
    }
}
