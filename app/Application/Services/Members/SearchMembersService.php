<?php

declare(strict_types=1);

namespace App\Application\Services\Members;

use App\Application\Ports\Outbound\MemberRepositoryInterface;
use App\Domain\Entities\Member;

final class SearchMembersService
{
    public function __construct(
        private readonly MemberRepositoryInterface $memberRepository
    ) {}

    /**
     * @param array{q?: ?string, page?: int, size?: int} $criteria
     * @return array{items: array<int, array{member: Member, active_loans: int}>, page: int, size: int, total: int}
     */
    public function execute(array $criteria): array
    {
        $page = max(1, (int) ($criteria['page'] ?? 1));
        $size = min(50, max(1, (int) ($criteria['size'] ?? 20)));

        $searchCriteria = [
            'q' => $criteria['q'] ?? null,
            'page' => $page,
            'size' => $size,
        ];

        $members = $this->memberRepository->search($searchCriteria);
        $total = $this->memberRepository->count($searchCriteria);

        $items = [];
        foreach ($members as $m) {
            $items[] = [
                'member' => $m,
                'active_loans' => $this->memberRepository->countActiveLoanItems($m->id()),
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
