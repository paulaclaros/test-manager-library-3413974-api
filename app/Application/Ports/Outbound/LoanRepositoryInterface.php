<?php

declare(strict_types=1);

namespace App\Application\Ports\Outbound;

use App\Domain\Entities\Loan;
use App\Domain\Entities\LoanItem;
use DateTimeImmutable;

interface LoanRepositoryInterface
{
    public function findById(string $id): ?Loan;

    public function save(Loan $loan): void;

    /**
     * @param array{from?: ?string, to?: ?string, status?: ?string, member_id?: ?string, page?: int, size?: int} $criteria
     * @return array<int, Loan>
     */
    public function search(array $criteria, DateTimeImmutable $now): array;

    /**
     * @param array{from?: ?string, to?: ?string, status?: ?string, member_id?: ?string} $criteria
     */
    public function count(array $criteria, DateTimeImmutable $now): int;

    public function findItemById(string $loanId, string $itemId): ?LoanItem;

    public function saveItem(LoanItem $item): void;

    /**
     * @return array<int, array{loan_id: string, item_id: string, book_title: string, member_name: string, due_at: string, days_late: int}>
     */
    public function getOverdueItems(DateTimeImmutable $now): array;
}
