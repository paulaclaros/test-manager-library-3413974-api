<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

final class LoanStatus
{
    public const OPEN = 'open';
    public const OVERDUE = 'overdue';
    public const CLOSED = 'closed';

    private readonly string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function open(): self
    {
        return new self(self::OPEN);
    }

    public static function overdue(): self
    {
        return new self(self::OVERDUE);
    }

    public static function closed(): self
    {
        return new self(self::CLOSED);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
