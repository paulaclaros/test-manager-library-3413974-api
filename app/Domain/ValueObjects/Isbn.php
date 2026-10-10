<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use App\Domain\Exceptions\ValidationException;

final class Isbn
{
    private readonly string $value;

    public function __construct(string $rawIsbn)
    {
        $cleaned = trim($rawIsbn);
        if (!preg_match('/^\d{13}$/', $cleaned)) {
            throw new ValidationException('El ISBN debe contener exactamente 13 dígitos numéricos.', [
                ['field' => 'isbn', 'issue' => 'debe contener exactamente 13 dígitos numéricos']
            ]);
        }
        $this->value = $cleaned;
    }

    public static function fromString(string $isbn): self
    {
        return new self($isbn);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(Isbn $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
