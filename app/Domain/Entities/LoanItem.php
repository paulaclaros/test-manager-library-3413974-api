<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\Exceptions\ItemAlreadyReturnedException;
use DateTimeImmutable;

final class LoanItem
{
    public function __construct(
        private readonly string $id,
        private readonly string $loanId,
        private readonly string $bookId,
        private readonly string $bookTitle,
        private readonly string $isbn,
        private readonly string $categoryName,
        private readonly DateTimeImmutable $dueAt,
        private ?DateTimeImmutable $returnedAt = null
    ) {}

    public function id(): string
    {
        return $this->id;
    }

    public function loanId(): string
    {
        return $this->loanId;
    }

    public function bookId(): string
    {
        return $this->bookId;
    }

    public function bookTitle(): string
    {
        return $this->bookTitle;
    }

    public function isbn(): string
    {
        return $this->isbn;
    }

    public function categoryName(): string
    {
        return $this->categoryName;
    }

    public function dueAt(): DateTimeImmutable
    {
        return $this->dueAt;
    }

    public function returnedAt(): ?DateTimeImmutable
    {
        return $this->returnedAt;
    }

    public function isReturned(): bool
    {
        return $this->returnedAt !== null;
    }

    public function isOverdue(DateTimeImmutable $now): bool
    {
        return !$this->isReturned() && $this->dueAt < $now;
    }

    public function daysLate(DateTimeImmutable $now): int
    {
        if (!$this->isOverdue($now)) {
            return 0;
        }
        $diff = $this->dueAt->diff($now);
        return (int) $diff->days;
    }

    public function markAsReturned(DateTimeImmutable $returnedAt): void
    {
        if ($this->returnedAt !== null) {
            throw new ItemAlreadyReturnedException('La línea de préstamo ya fue devuelta previamente.');
        }
        $this->returnedAt = $returnedAt;
    }
}
