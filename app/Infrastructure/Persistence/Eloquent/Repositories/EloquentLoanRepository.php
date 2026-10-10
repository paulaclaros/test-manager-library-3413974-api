<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Application\Ports\Outbound\LoanRepositoryInterface;
use App\Domain\Entities\Loan;
use App\Domain\Entities\LoanItem;
use App\Domain\ValueObjects\LoanStatus;
use App\Infrastructure\Persistence\Eloquent\Mappers\LoanMapper;
use App\Infrastructure\Persistence\Eloquent\Models\LoanItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\LoanModel;
use DateTimeImmutable;

final class EloquentLoanRepository implements LoanRepositoryInterface
{
    public function findById(string $id): ?Loan
    {
        $model = LoanModel::with(['items', 'member', 'loanedByUser'])->find($id);
        return $model ? LoanMapper::toDomain($model) : null;
    }

    public function save(Loan $loan): void
    {
        $model = LoanMapper::toModel($loan);
        $model->save();

        foreach ($loan->items() as $item) {
            $itemModel = LoanMapper::itemToModel($item);
            $itemModel->save();
        }
    }

    public function search(array $criteria, DateTimeImmutable $now): array
    {
        $query = LoanModel::with(['items', 'member', 'loanedByUser']);

        if (!empty($criteria['member_id'])) {
            $query->where('member_id', $criteria['member_id']);
        }

        if (!empty($criteria['from'])) {
            $query->where('loaned_at', '>=', $criteria['from'] . ' 00:00:00');
        }

        if (!empty($criteria['to'])) {
            $query->where('loaned_at', '<=', $criteria['to'] . ' 23:59:59');
        }

        $nowStr = $now->format('Y-m-d H:i:sP');

        if (!empty($criteria['status'])) {
            $status = strtolower((string) $criteria['status']);
            if ($status === LoanStatus::CLOSED) {
                // Todas las líneas devueltas
                $query->whereDoesntHave('items', function ($q) {
                    $q->whereNull('returned_at');
                });
            } elseif ($status === LoanStatus::OVERDUE) {
                // Al menos una línea sin devolver y vencida
                $query->whereHas('items', function ($q) use ($nowStr) {
                    $q->whereNull('returned_at')
                      ->where('due_at', '<', $nowStr);
                });
            } elseif ($status === LoanStatus::OPEN) {
                // Al menos una sin devolver y ninguna vencida
                $query->whereHas('items', function ($q) {
                    $q->whereNull('returned_at');
                })->whereDoesntHave('items', function ($q) use ($nowStr) {
                    $q->whereNull('returned_at')
                      ->where('due_at', '<', $nowStr);
                });
            }
        }

        $page = max(1, (int) ($criteria['page'] ?? 1));
        $size = min(50, max(1, (int) ($criteria['size'] ?? 20)));

        return $query->orderBy('loaned_at', 'desc')
            ->forPage($page, $size)
            ->get()
            ->map(fn(LoanModel $m) => LoanMapper::toDomain($m))
            ->all();
    }

    public function count(array $criteria, DateTimeImmutable $now): int
    {
        $query = LoanModel::query();

        if (!empty($criteria['member_id'])) {
            $query->where('member_id', $criteria['member_id']);
        }

        if (!empty($criteria['from'])) {
            $query->where('loaned_at', '>=', $criteria['from'] . ' 00:00:00');
        }

        if (!empty($criteria['to'])) {
            $query->where('loaned_at', '<=', $criteria['to'] . ' 23:59:59');
        }

        $nowStr = $now->format('Y-m-d H:i:sP');

        if (!empty($criteria['status'])) {
            $status = strtolower((string) $criteria['status']);
            if ($status === LoanStatus::CLOSED) {
                $query->whereDoesntHave('items', function ($q) {
                    $q->whereNull('returned_at');
                });
            } elseif ($status === LoanStatus::OVERDUE) {
                $query->whereHas('items', function ($q) use ($nowStr) {
                    $q->whereNull('returned_at')
                      ->where('due_at', '<', $nowStr);
                });
            } elseif ($status === LoanStatus::OPEN) {
                $query->whereHas('items', function ($q) {
                    $q->whereNull('returned_at');
                })->whereDoesntHave('items', function ($q) use ($nowStr) {
                    $q->whereNull('returned_at')
                      ->where('due_at', '<', $nowStr);
                });
            }
        }

        return $query->count();
    }

    public function findItemById(string $loanId, string $itemId): ?LoanItem
    {
        $model = LoanItemModel::where('loan_id', $loanId)->where('id', $itemId)->first();
        return $model ? LoanMapper::itemToDomain($model) : null;
    }

    public function saveItem(LoanItem $item): void
    {
        $model = LoanMapper::itemToModel($item);
        $model->save();
    }

    public function getOverdueItems(DateTimeImmutable $now): array
    {
        $nowStr = $now->format('Y-m-d H:i:sP');

        $items = LoanItemModel::with('loan.member')
            ->whereNull('returned_at')
            ->where('due_at', '<', $nowStr)
            ->get();

        $rows = [];
        foreach ($items as $item) {
            $dueAtImmutable = DateTimeImmutable::createFromMutable($item->due_at);
            $diff = $dueAtImmutable->diff($now);
            $daysLate = (int) $diff->days;

            $rows[] = [
                'loan_id' => (string) $item->loan_id,
                'item_id' => (string) $item->id,
                'book_title' => (string) $item->book_title,
                'member_name' => (string) ($item->loan?->member?->full_name ?? 'Desconocido'),
                'due_at' => $dueAtImmutable->format('Y-m-d\TH:i:s\Z'),
                'days_late' => $daysLate,
            ];
        }

        // Ordenadas por más días de retraso primero (CA-09.2)
        usort($rows, fn($a, $b) => $b['days_late'] <=> $a['days_late']);

        return $rows;
    }
}
