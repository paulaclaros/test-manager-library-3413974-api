<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Mappers;

use App\Domain\Entities\Loan;
use App\Domain\Entities\LoanItem;
use App\Infrastructure\Persistence\Eloquent\Models\LoanItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\LoanModel;
use DateTimeImmutable;

final class LoanMapper
{
    public static function toDomain(LoanModel $model): Loan
    {
        $items = [];
        foreach ($model->items as $itemModel) {
            $returnedAt = null;
            if ($itemModel->returned_at !== null) {
                $returnedAt = DateTimeImmutable::createFromMutable($itemModel->returned_at);
            }

            $items[] = new LoanItem(
                (string) $itemModel->id,
                (string) $itemModel->loan_id,
                (string) $itemModel->book_id,
                (string) $itemModel->book_title,
                (string) $itemModel->isbn,
                (string) $itemModel->category_name,
                DateTimeImmutable::createFromMutable($itemModel->due_at),
                $returnedAt
            );
        }

        return new Loan(
            (string) $model->id,
            (string) $model->member_id,
            (string) $model->loaned_by_user_id,
            DateTimeImmutable::createFromMutable($model->loaned_at),
            $items
        );
    }

    public static function itemToDomain(LoanItemModel $itemModel): LoanItem
    {
        $returnedAt = null;
        if ($itemModel->returned_at !== null) {
            $returnedAt = DateTimeImmutable::createFromMutable($itemModel->returned_at);
        }

        return new LoanItem(
            (string) $itemModel->id,
            (string) $itemModel->loan_id,
            (string) $itemModel->book_id,
            (string) $itemModel->book_title,
            (string) $itemModel->isbn,
            (string) $itemModel->category_name,
            DateTimeImmutable::createFromMutable($itemModel->due_at),
            $returnedAt
        );
    }

    public static function toModel(Loan $entity): LoanModel
    {
        $model = LoanModel::find($entity->id()) ?? new LoanModel();
        $model->id = $entity->id();
        $model->member_id = $entity->memberId();
        $model->loaned_by_user_id = $entity->loanedByUserId();
        $model->loaned_at = $entity->loanedAt()->format('Y-m-d H:i:sP');

        return $model;
    }

    public static function itemToModel(LoanItem $item): LoanItemModel
    {
        $model = LoanItemModel::find($item->id()) ?? new LoanItemModel();
        $model->id = $item->id();
        $model->loan_id = $item->loanId();
        $model->book_id = $item->bookId();
        $model->book_title = $item->bookTitle();
        $model->isbn = $item->isbn();
        $model->category_name = $item->categoryName();
        $model->due_at = $item->dueAt()->format('Y-m-d H:i:sP');
        $model->returned_at = $item->returnedAt()?->format('Y-m-d H:i:sP');

        return $model;
    }
}
