<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use App\Domain\Exceptions\InvalidDateRangeException;
use App\Domain\Exceptions\ValidationException;
use DateTimeImmutable;

final class DateRange
{
    private readonly DateTimeImmutable $from;
    private readonly DateTimeImmutable $to;

    public function __construct(string $fromString, string $toString)
    {
        $fromDate = DateTimeImmutable::createFromFormat('Y-m-d', trim($fromString));
        $toDate = DateTimeImmutable::createFromFormat('Y-m-d', trim($toString));

        if (!$fromDate || !$toDate) {
            throw new ValidationException('El formato de fecha debe ser YYYY-MM-DD.', [
                ['field' => 'date_range', 'issue' => 'formato de fecha inválido']
            ]);
        }

        $fromMidnight = $fromDate->setTime(0, 0, 0);
        $toMidnight = $toDate->setTime(23, 59, 59);

        if ($toMidnight < $fromMidnight) {
            throw new InvalidDateRangeException('La fecha final no puede ser anterior a la inicial.');
        }

        $this->from = $fromMidnight;
        $this->to = $toMidnight;
    }

    public function from(): DateTimeImmutable
    {
        return $this->from;
    }

    public function to(): DateTimeImmutable
    {
        return $this->to;
    }

    public function fromString(): string
    {
        return $this->from->format('Y-m-d');
    }

    public function toString(): string
    {
        return $this->to->format('Y-m-d');
    }
}
