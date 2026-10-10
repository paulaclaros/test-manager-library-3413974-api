<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Mappers;

use App\Domain\Entities\Book;
use App\Domain\ValueObjects\Isbn;
use App\Infrastructure\Persistence\Eloquent\Models\BookModel;
use DateTimeImmutable;

final class BookMapper
{
    public static function toDomain(BookModel $model): Book
    {
        $deletedAt = null;
        if ($model->deleted_at !== null) {
            $deletedAt = DateTimeImmutable::createFromMutable($model->deleted_at);
        }

        return new Book(
            (string) $model->id,
            (string) $model->title,
            (string) $model->author,
            Isbn::fromString((string) $model->isbn),
            (string) $model->category_id,
            (int) $model->total_copies,
            (int) $model->available_copies,
            $model->cover_key ? (string) $model->cover_key : null,
            $deletedAt
        );
    }

    public static function toModel(Book $entity): BookModel
    {
        $model = BookModel::find($entity->id()) ?? new BookModel();
        $model->id = $entity->id();
        $model->title = $entity->title();
        $model->author = $entity->author();
        $model->isbn = $entity->isbn()->value();
        $model->category_id = $entity->categoryId();
        $model->total_copies = $entity->totalCopies();
        $model->available_copies = $entity->availableCopies();
        $model->cover_key = $entity->coverKey();
        $model->deleted_at = $entity->deletedAt()?->format('Y-m-d H:i:sP');

        return $model;
    }
}
