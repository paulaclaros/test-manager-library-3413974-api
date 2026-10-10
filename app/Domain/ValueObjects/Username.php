<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use App\Domain\Exceptions\ValidationException;

final class Username
{
    private readonly string $value;

    public function __construct(string $rawUsername)
    {
        $normalized = mb_strtolower(trim($rawUsername));
        if ($normalized === '') {
            throw new ValidationException('El nombre de usuario no puede estar vacío.', [
                ['field' => 'username', 'issue' => 'no puede estar vacío']
            ]);
        }
        if (mb_strlen($normalized) > 120) {
            throw new ValidationException('El nombre de usuario no puede superar 120 caracteres.', [
                ['field' => 'username', 'issue' => 'máximo 120 caracteres']
            ]);
        }
        $this->value = $normalized;
    }

    public static function fromString(string $username): self
    {
        return new self($username);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(Username $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
