<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use App\Domain\Exceptions\ValidationException;

final class Quantity
{
    private readonly int $value;

    public function __construct(int $value)
    {
        if ($value < 0) {
            throw new ValidationException('La cantidad no puede ser negativa.', [
                ['field' => 'quantity', 'issue' => 'debe ser mayor o igual a 0']
            ]);
        }
        $this->value = $value;
    }

    public static function fromInt(int $value): self
    {
        return new self($value);
    }

    public function value(): int
    {
        return $this->value;
    }

    public function add(int $amount): self
    {
        return new self($this->value + $amount);
    }

    public function subtract(int $amount): self
    {
        if ($amount > $this->value) {
            throw new ValidationException('La cantidad no puede ser menor a cero tras restar.', [
                ['field' => 'quantity', 'issue' => 'cantidad insuficiente']
            ]);
        }
        return new self($this->value - $amount);
    }

    public function equals(Quantity $other): bool
    {
        return $this->value === $other->value;
    }
}
