<?php

declare(strict_types=1);

namespace App\Application\Services\Loans;

use App\Application\Ports\Outbound\ClockInterface;
use App\Application\Ports\Outbound\LoanRepositoryInterface;
use App\Domain\Entities\Loan;
use App\Domain\Exceptions\EntityNotFoundException;
use App\Domain\ValueObjects\LoanStatus;

final class GetLoanService
{
    public function __construct(
        private readonly LoanRepositoryInterface $loanRepository,
        private readonly ClockInterface $clock
    ) {}

    /**
     * @return array{loan: Loan, status: LoanStatus}
     */
    public function execute(string $loanId): array
    {
        $loan = $this->loanRepository->findById($loanId);
        if ($loan === null) {
            throw new EntityNotFoundException('El préstamo solicitado no existe.');
        }

        $status = $loan->status($this->clock->now());

        return [
            'loan' => $loan,
            'status' => $status,
        ];
    }
}
