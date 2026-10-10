<?php

declare(strict_types=1);

namespace App\Application\Services\Books;

use App\Application\Ports\Outbound\BookRepositoryInterface;
use App\Application\Ports\Outbound\CategoryRepositoryInterface;
use App\Domain\Entities\Book;
use App\Domain\Exceptions\EntityNotFoundException;

final class UpdateBookService
{
    public function __construct(
        private readonly BookRepositoryInterface $bookRepository,
        private readonly CategoryRepositoryInterface $categoryRepository
    ) {}

    public function execute(string $bookId, string $title, string $author, string $categoryId, int $totalCopies): Book
    {
        $book = $this->bookRepository->findById($bookId);
        if ($book === null || !$book->isActive()) {
            throw new EntityNotFoundException('El libro solicitado no existe o fue dado de baja.');
        }

        $category = $this->categoryRepository->findById($categoryId);
        if ($category === null) {
            throw new EntityNotFoundException('La categoría especificada no existe.');
        }

        $book->update($title, $author, $categoryId, $totalCopies);
        $this->bookRepository->save($book);

        return $book;
    }
}
