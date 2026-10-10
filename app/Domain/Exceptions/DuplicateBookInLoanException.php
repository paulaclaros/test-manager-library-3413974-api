<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

final class DuplicateBookInLoanException extends DomainException
{
    public function __construct(string $message = 'No se puede repetir el mismo libro en un préstamo.')
    {
        parent::__construct($message, 'DUPLICATE_BOOK_IN_LOAN', 422);
    }
}
