<?php

declare(strict_types=1);

namespace App\Application\Services\Books;

use App\Application\Ports\Outbound\BookRepositoryInterface;
use App\Application\Ports\Outbound\CategoryRepositoryInterface;
use App\Domain\Entities\Book;

final class SearchBooksService
{
    public function __construct(
        private readonly BookRepositoryInterface $bookRepository,
        private readonly CategoryRepositoryInterface $categoryRepository
    ) {}

    /**
     * @param array{q?: ?string, category_id?: ?string, page?: int, size?: int} $criteria
     * @return array{items: array<int, array{book: Book, category_name: string}>, page: int, size: int, total: int}
     */
    public function execute(array $criteria): array
    {
        $page = max(1, (int) ($criteria['page'] ?? 1));
        $size = min(50, max(1, (int) ($criteria['size'] ?? 20)));

        $searchCriteria = [
            'q' => $criteria['q'] ?? null,
            'category_id' => $criteria['category_id'] ?? null,
            'page' => $page,
            'size' => $size,
        ];

        $books = $this->bookRepository->search($searchCriteria);
        $total = $this->bookRepository->count($searchCriteria);

        $categories = [];
        foreach ($this->categoryRepository->findAll() as $cat) {
            $categories[$cat->id()] = $cat->name();
        }

        $items = [];
        foreach ($books as $b) {
            $items[] = [
                'book' => $b,
                'category_name' => $categories[$b->categoryId()] ?? 'General',
            ];
        }

        return [
            'items' => $items,
            'page' => $page,
            'size' => $size,
            'total' => $total,
        ];
    }
}
