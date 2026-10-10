<?php

declare(strict_types=1);

namespace App\Application\Services\Books;

use App\Application\Ports\Outbound\BookRepositoryInterface;
use App\Application\Ports\Outbound\CategoryRepositoryInterface;
use App\Domain\Entities\Book;
use App\Domain\Entities\Category;
use App\Domain\Exceptions\EntityNotFoundException;

final class GetBookService
{
    public function __construct(
        private readonly BookRepositoryInterface $bookRepository,
        private readonly CategoryRepositoryInterface $categoryRepository
    ) {}

    /**
     * @return array{book: Book, category: Category}
     */
    public function execute(string $bookId): array
    {
        $book = $this->bookRepository->findById($bookId);
        if ($book === null || !$book->isActive()) {
            throw new EntityNotFoundException('El libro solicitado no existe o fue dado de baja.');
        }

        $category = $this->categoryRepository->findById($book->categoryId());
        if ($category === null) {
            $category = new Category($book->categoryId(), 'General');
        }

        return [
            'book' => $book,
            'category' => $category,
        ];
    }
}
