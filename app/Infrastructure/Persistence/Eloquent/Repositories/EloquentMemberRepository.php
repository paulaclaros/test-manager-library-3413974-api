<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Application\Ports\Outbound\MemberRepositoryInterface;
use App\Domain\Entities\Member;
use App\Infrastructure\Persistence\Eloquent\Mappers\MemberMapper;
use App\Infrastructure\Persistence\Eloquent\Models\LoanItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\MemberModel;
use DateTimeImmutable;

final class EloquentMemberRepository implements MemberRepositoryInterface
{
    public function findById(string $id): ?Member
    {
        $model = MemberModel::find($id);
        return $model ? MemberMapper::toDomain($model) : null;
    }

    public function findByDocumentNumber(string $documentNumber): ?Member
    {
        $model = MemberModel::where('document_number', trim($documentNumber))->first();
        return $model ? MemberMapper::toDomain($model) : null;
    }

    public function search(array $criteria): array
    {
        $query = MemberModel::query();

        if (!empty($criteria['q'])) {
            $term = '%' . trim((string) $criteria['q']) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('full_name', 'ilike', $term)
                  ->orWhere('document_number', 'ilike', $term);
            });
        }

        $page = max(1, (int) ($criteria['page'] ?? 1));
        $size = min(50, max(1, (int) ($criteria['size'] ?? 20)));

        return $query->orderBy('full_name', 'asc')
            ->forPage($page, $size)
            ->get()
            ->map(fn(MemberModel $m) => MemberMapper::toDomain($m))
            ->all();
    }

    public function count(array $criteria): int
    {
        $query = MemberModel::query();

        if (!empty($criteria['q'])) {
            $term = '%' . trim((string) $criteria['q']) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('full_name', 'ilike', $term)
                  ->orWhere('document_number', 'ilike', $term);
            });
        }

        return $query->count();
    }

    public function save(Member $member): void
    {
        $model = MemberMapper::toModel($member);
        $model->save();
    }

    public function countActiveLoanItems(string $memberId): int
    {
        return LoanItemModel::whereHas('loan', function ($q) use ($memberId) {
            $q->where('member_id', $memberId);
        })
        ->whereNull('returned_at')
        ->count();
    }

    public function hasOverdueLoanItems(string $memberId, DateTimeImmutable $now): bool
    {
        return LoanItemModel::whereHas('loan', function ($q) use ($memberId) {
            $q->where('member_id', $memberId);
        })
        ->whereNull('returned_at')
        ->where('due_at', '<', $now->format('Y-m-d H:i:sP'))
        ->exists();
    }
}
