<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Application\Ports\Outbound\BookRepositoryInterface;
use App\Domain\Entities\Book;
use App\Domain\ValueObjects\Isbn;
use App\Infrastructure\Persistence\Eloquent\Mappers\BookMapper;
use App\Infrastructure\Persistence\Eloquent\Models\BookModel;
use App\Infrastructure\Persistence\Eloquent\Models\LoanItemModel;

final class EloquentBookRepository implements BookRepositoryInterface
{
    public function findById(string $id): ?Book
    {
        $model = BookModel::find($id);
        return $model ? BookMapper::toDomain($model) : null;
    }

    public function findByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $models = BookModel::whereIn('id', $ids)->get();
        $result = [];
        foreach ($models as $m) {
            $result[(string) $m->id] = BookMapper::toDomain($m);
        }
        return $result;
    }

    public function findByIsbn(Isbn $isbn): ?Book
    {
        $model = BookModel::where('isbn', $isbn->value())
            ->whereNull('deleted_at')
            ->first();
        return $model ? BookMapper::toDomain($model) : null;
    }

    public function search(array $criteria): array
    {
        $query = BookModel::whereNull('deleted_at');

        if (!empty($criteria['q'])) {
            $term = '%' . trim((string) $criteria['q']) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('title', 'ilike', $term)
                  ->orWhere('author', 'ilike', $term);
            });
        }

        if (!empty($criteria['category_id'])) {
            $query->where('category_id', $criteria['category_id']);
        }

        $page = max(1, (int) ($criteria['page'] ?? 1));
        $size = min(50, max(1, (int) ($criteria['size'] ?? 20)));

        return $query->orderBy('title', 'asc')
            ->forPage($page, $size)
            ->get()
            ->map(fn(BookModel $m) => BookMapper::toDomain($m))
            ->all();
    }

    public function count(array $criteria): int
    {
        $query = BookModel::whereNull('deleted_at');

        if (!empty($criteria['q'])) {
            $term = '%' . trim((string) $criteria['q']) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('title', 'ilike', $term)
                  ->orWhere('author', 'ilike', $term);
            });
        }

        if (!empty($criteria['category_id'])) {
            $query->where('category_id', $criteria['category_id']);
        }

        return $query->count();
    }

    public function save(Book $book): void
    {
        $model = BookMapper::toModel($book);
        $model->save();
    }

    public function hasActiveLoans(string $bookId): bool
    {
        return LoanItemModel::where('book_id', $bookId)
            ->whereNull('returned_at')
            ->exists();
    }
}
