<?php

declare(strict_types=1);

namespace App\Application\Ports\Outbound;

use App\Domain\Entities\Member;
use DateTimeImmutable;

interface MemberRepositoryInterface
{
    public function findById(string $id): ?Member;

    public function findByDocumentNumber(string $documentNumber): ?Member;

    /**
     * @param array{q?: ?string, page?: int, size?: int} $criteria
     * @return array<int, Member>
     */
    public function search(array $criteria): array;

    /**
     * @param array{q?: ?string} $criteria
     */
    public function count(array $criteria): int;

    public function save(Member $member): void;

    public function countActiveLoanItems(string $memberId): int;

    public function hasOverdueLoanItems(string $memberId, DateTimeImmutable $now): bool;
}
