<?php

declare(strict_types=1);

namespace App\Application\Services\Loans;

use App\Application\Ports\Outbound\ClockInterface;
use App\Application\Ports\Outbound\LoanRepositoryInterface;

final class GetOverdueLoansService
{
    public function __construct(
        private readonly LoanRepositoryInterface $loanRepository,
        private readonly ClockInterface $clock
    ) {}

    /**
     * @return array<int, array{loan_id: string, item_id: string, book_title: string, member_name: string, due_at: string, days_late: int}>
     */
    public function execute(): array
    {
        $now = $this->clock->now();
        return $this->loanRepository->getOverdueItems($now);
    }
}
