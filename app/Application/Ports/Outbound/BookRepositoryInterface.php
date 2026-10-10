<?php

declare(strict_types=1);

namespace App\Application\Ports\Outbound;

use App\Domain\Entities\Book;
use App\Domain\ValueObjects\Isbn;

interface BookRepositoryInterface
{
    public function findById(string $id): ?Book;

    /**
     * @param array<int, string> $ids
     * @return array<string, Book>
     */
    public function findByIds(array $ids): array;

    public function findByIsbn(Isbn $isbn): ?Book;

    /**
     * @param array{q?: ?string, category_id?: ?string, page?: int, size?: int} $criteria
     * @return array<int, Book>
     */
    public function search(array $criteria): array;

    /**
     * @param array{q?: ?string, category_id?: ?string} $criteria
     */
    public function count(array $criteria): int;

    public function save(Book $book): void;

    public function hasActiveLoans(string $bookId): bool;
}
