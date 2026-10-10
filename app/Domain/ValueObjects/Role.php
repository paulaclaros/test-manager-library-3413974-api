<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use App\Domain\Exceptions\ValidationException;

final class Role
{
    public const ADMIN = 'admin';
    public const LIBRARIAN = 'librarian';

    private readonly string $value;

    public function __construct(string $rawRole)
    {
        $role = trim($rawRole);
        if ($role !== self::ADMIN && $role !== self::LIBRARIAN) {
            throw new ValidationException('El rol debe ser admin o librarian.', [
                ['field' => 'role', 'issue' => 'debe ser admin o librarian']
            ]);
        }
        $this->value = $role;
    }

    public static function admin(): self
    {
        return new self(self::ADMIN);
    }

    public static function librarian(): self
    {
        return new self(self::LIBRARIAN);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isAdmin(): bool
    {
        return $this->value === self::ADMIN;
    }

    public function isLibrarian(): bool
    {
        return $this->value === self::LIBRARIAN;
    }

    public function equals(Role $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
