<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\Exceptions\DuplicateBookInLoanException;
use App\Domain\Exceptions\ValidationException;
use App\Domain\ValueObjects\LoanStatus;
use DateTimeImmutable;

final class Loan
{
    /**
     * @var array<string, LoanItem>
     */
    private array $items = [];

    /**
     * @param array<int, LoanItem> $initialItems
     */
    public function __construct(
        private readonly string $id,
        private readonly string $memberId,
        private readonly string $loanedByUserId,
        private readonly DateTimeImmutable $loanedAt,
        array $initialItems = []
    ) {
        foreach ($initialItems as $item) {
            $this->addItem($item);
        }
    }

    public function id(): string
    {
        return $this->id;
    }

    public function memberId(): string
    {
        return $this->memberId;
    }

    public function loanedByUserId(): string
    {
        return $this->loanedByUserId;
    }

    public function loanedAt(): DateTimeImmutable
    {
        return $this->loanedAt;
    }

    /**
     * @return array<int, LoanItem>
     */
    public function items(): array
    {
        return array_values($this->items);
    }

    public function addItem(LoanItem $item): void
    {
        if (count($this->items) >= 3) {
            throw new ValidationException('Un préstamo no puede contener más de 3 libros.', [
                ['field' => 'items', 'issue' => 'máximo 3 libros']
            ]);
        }

        foreach ($this->items as $existing) {
            if ($existing->bookId() === $item->bookId()) {
                throw new DuplicateBookInLoanException('No se puede repetir el mismo libro en un préstamo.');
            }
        }

        $this->items[$item->id()] = $item;
    }

    public function status(DateTimeImmutable $now): LoanStatus
    {
        if (empty($this->items)) {
            return LoanStatus::closed();
        }

        $allReturned = true;
        $hasOverdue = false;

        foreach ($this->items as $item) {
            if (!$item->isReturned()) {
                $allReturned = false;
                if ($item->isOverdue($now)) {
                    $hasOverdue = true;
                }
            }
        }

        if ($allReturned) {
            return LoanStatus::closed();
        }

        if ($hasOverdue) {
            return LoanStatus::overdue();
        }

        return LoanStatus::open();
    }
}
