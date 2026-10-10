<?php

declare(strict_types=1);

namespace App\Application\Services\Books;

use App\Application\Ports\Outbound\BookRepositoryInterface;
use App\Application\Ports\Outbound\CategoryRepositoryInterface;
use App\Domain\Entities\Book;
use App\Domain\Exceptions\EntityNotFoundException;
use App\Domain\Exceptions\IsbnTakenException;
use App\Domain\ValueObjects\Isbn;
use Illuminate\Support\Str;

final class CreateBookService
{
    public function __construct(
        private readonly BookRepositoryInterface $bookRepository,
        private readonly CategoryRepositoryInterface $categoryRepository
    ) {}

    public function execute(string $title, string $author, string $rawIsbn, string $categoryId, int $totalCopies): Book
    {
        $category = $this->categoryRepository->findById($categoryId);
        if ($category === null) {
            throw new EntityNotFoundException('La categoría especificada no existe.');
        }

        $isbn = Isbn::fromString($rawIsbn);
        $existing = $this->bookRepository->findByIsbn($isbn);
        if ($existing !== null && $existing->isActive()) {
            throw new IsbnTakenException("El ISBN {$isbn->value()} ya está registrado para otro libro activo.");
        }

        $book = new Book(
            (string) Str::uuid(),
            $title,
            $author,
            $isbn,
            $categoryId,
            $totalCopies,
            $totalCopies, // CA-03.1: disponibles arrancan iguales al total
            null,
            null
        );

        $this->bookRepository->save($book);

        return $book;
    }
}
