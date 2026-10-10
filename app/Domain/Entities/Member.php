<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\Exceptions\ValidationException;

final class Member
{
    private string $fullName;
    private string $documentNumber;

    public function __construct(
        private readonly string $id,
        string $fullName,
        string $documentNumber
    ) {
        $this->fullName = trim($fullName);
        $this->documentNumber = trim($documentNumber);

        if ($this->fullName === '') {
            throw new ValidationException('El nombre del socio no puede estar vacío.', [
                ['field' => 'full_name', 'issue' => 'no puede estar vacío']
            ]);
        }
        if ($this->documentNumber === '') {
            throw new ValidationException('El documento del socio no puede estar vacío.', [
                ['field' => 'document_number', 'issue' => 'no puede estar vacío']
            ]);
        }
    }

    public function id(): string
    {
        return $this->id;
    }

    public function fullName(): string
    {
        return $this->fullName;
    }

    public function documentNumber(): string
    {
        return $this->documentNumber;
    }
}
