<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Entities\Book;
use App\Domain\Exceptions\BookNotAvailableException;
use App\Domain\Exceptions\ValidationException;
use App\Domain\ValueObjects\Isbn;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class BookTest extends TestCase
{
    private function createSampleBook(int $total = 5, int $available = 5): Book
    {
        return new Book(
            'book-123',
            'Cien años de soledad',
            'Gabriel García Márquez',
            new Isbn('9780307474728'),
            'cat-novela',
            $total,
            $available
        );
    }

    public function test_book_creation_and_properties(): void
    {
        $book = $this->createSampleBook();
        $this->assertSame('Cien años de soledad', $book->title());
        $this->assertSame(5, $book->availableCopies());
        $this->assertTrue($book->isActive());
    }

    public function test_withdraw_decreases_available_copies(): void
    {
        $book = $this->createSampleBook(5, 5);
        $book->withdraw(2);
        $this->assertSame(3, $book->availableCopies());
    }

    public function test_withdraw_more_than_available_throws_book_not_available_exception(): void
    {
        $book = $this->createSampleBook(5, 1);
        $this->expectException(BookNotAvailableException::class);
        $book->withdraw(2);
    }

    public function test_restock_increases_available_copies(): void
    {
        $book = $this->createSampleBook(5, 2);
        $book->restock(2);
        $this->assertSame(4, $book->availableCopies());
    }

    public function test_restock_exceeding_total_throws_validation_exception(): void
    {
        $book = $this->createSampleBook(5, 5);
        $this->expectException(ValidationException::class);
        $book->restock(1);
    }

    public function test_update_adjusts_available_copies_and_total(): void
    {
        $book = $this->createSampleBook(5, 3); // 2 prestados
        $book->update('Nuevo Título', 'Nuevo Autor', 'cat-novela', 7);
        $this->assertSame(7, $book->totalCopies());
        $this->assertSame(5, $book->availableCopies());
    }

    public function test_update_cannot_leave_available_copies_negative(): void
    {
        $book = $this->createSampleBook(5, 1); // 4 prestados
        $this->expectException(ValidationException::class);
        $book->update('Título', 'Autor', 'cat-novela', 3); // Reducir a 3 cuando hay 4 prestados
    }

    public function test_deactivate_sets_deleted_at(): void
    {
        $book = $this->createSampleBook();
        $now = new DateTimeImmutable('2026-10-10 12:00:00');
        $book->deactivate($now);
        $this->assertFalse($book->isActive());
        $this->assertSame($now, $book->deletedAt());
    }
}
