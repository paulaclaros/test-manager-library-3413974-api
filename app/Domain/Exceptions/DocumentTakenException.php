<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

final class DocumentTakenException extends DomainException
{
    public function __construct(string $message = 'El documento ya está registrado para otro socio.')
    {
        parent::__construct($message, 'DOCUMENT_TAKEN', 409);
    }
}
