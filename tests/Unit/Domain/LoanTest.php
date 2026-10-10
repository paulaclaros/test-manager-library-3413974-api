<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Entities\Loan;
use App\Domain\Entities\LoanItem;
use App\Domain\Exceptions\DuplicateBookInLoanException;
use App\Domain\Exceptions\ItemAlreadyReturnedException;
use App\Domain\Exceptions\ValidationException;
use App\Domain\ValueObjects\LoanStatus;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class LoanTest extends TestCase
{
    private function createItem(string $id, string $bookId, DateTimeImmutable $dueAt, ?DateTimeImmutable $returnedAt = null): LoanItem
    {
        return new LoanItem(
            $id,
            'loan-1',
            $bookId,
            'Libro ' . $bookId,
            '9780307474728',
            'Novela',
            $dueAt,
            $returnedAt
        );
    }

    public function test_loan_cannot_have_more_than_three_items(): void
    {
        $due = new DateTimeImmutable('2026-10-24 10:00:00');
        $loan = new Loan('loan-1', 'member-1', 'user-1', new DateTimeImmutable('2026-10-10 10:00:00'));

        $loan->addItem($this->createItem('i1', 'b1', $due));
        $loan->addItem($this->createItem('i2', 'b2', $due));
        $loan->addItem($this->createItem('i3', 'b3', $due));

        $this->expectException(ValidationException::class);
        $loan->addItem($this->createItem('i4', 'b4', $due));
    }

    public function test_loan_cannot_repeat_same_book(): void
    {
        $due = new DateTimeImmutable('2026-10-24 10:00:00');
        $loan = new Loan('loan-1', 'member-1', 'user-1', new DateTimeImmutable('2026-10-10 10:00:00'));

        $loan->addItem($this->createItem('i1', 'b1', $due));

        $this->expectException(DuplicateBookInLoanException::class);
        $loan->addItem($this->createItem('i2', 'b1', $due));
    }

    public function test_loan_status_calculation(): void
    {
        $loanDate = new DateTimeImmutable('2026-10-01 10:00:00');
        $dueDate = new DateTimeImmutable('2026-10-15 10:00:00');
        $loan = new Loan('loan-1', 'member-1', 'user-1', $loanDate);

        $item1 = $this->createItem('i1', 'b1', $dueDate);
        $loan->addItem($item1);

        // Día 10: abierto
        $nowOpen = new DateTimeImmutable('2026-10-10 10:00:00');
        $this->assertSame(LoanStatus::OPEN, $loan->status($nowOpen)->value());

        // Día 20: vencido (due_at era el 15)
        $nowOverdue = new DateTimeImmutable('2026-10-20 10:00:00');
        $this->assertSame(LoanStatus::OVERDUE, $loan->status($nowOverdue)->value());

        // Se devuelve el ítem: cerrado
        $item1->markAsReturned(new DateTimeImmutable('2026-10-18 10:00:00'));
        $this->assertSame(LoanStatus::CLOSED, $loan->status($nowOverdue)->value());
    }

    public function test_returning_already_returned_item_throws_exception(): void
    {
        $dueDate = new DateTimeImmutable('2026-10-15 10:00:00');
        $item = $this->createItem('i1', 'b1', $dueDate);

        $item->markAsReturned(new DateTimeImmutable('2026-10-10 10:00:00'));

        $this->expectException(ItemAlreadyReturnedException::class);
        $item->markAsReturned(new DateTimeImmutable('2026-10-12 10:00:00'));
    }
}
