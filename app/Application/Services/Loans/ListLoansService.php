<?php

declare(strict_types=1);

namespace App\Application\Services\Loans;

use App\Application\Ports\Outbound\ClockInterface;
use App\Application\Ports\Outbound\LoanRepositoryInterface;
use App\Domain\Entities\Loan;
use App\Domain\ValueObjects\LoanStatus;

final class ListLoansService
{
    public function __construct(
        private readonly LoanRepositoryInterface $loanRepository,
        private readonly ClockInterface $clock
    ) {}

    /**
     * @param array{from?: ?string, to?: ?string, status?: ?string, member_id?: ?string, page?: int, size?: int} $criteria
     * @return array{items: array<int, array{loan: Loan, status: LoanStatus}>, page: int, size: int, total: int}
     */
    public function execute(array $criteria): array
    {
        $page = max(1, (int) ($criteria['page'] ?? 1));
        $size = min(50, max(1, (int) ($criteria['size'] ?? 20)));
        $now = $this->clock->now();

        $searchCriteria = [
            'from' => $criteria['from'] ?? null,
            'to' => $criteria['to'] ?? null,
            'status' => $criteria['status'] ?? null,
            'member_id' => $criteria['member_id'] ?? null,
            'page' => $page,
            'size' => $size,
        ];

        $loans = $this->loanRepository->search($searchCriteria, $now);
        $total = $this->loanRepository->count($searchCriteria, $now);

        $items = [];
        foreach ($loans as $loan) {
            $items[] = [
                'loan' => $loan,
                'status' => $loan->status($now),
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
