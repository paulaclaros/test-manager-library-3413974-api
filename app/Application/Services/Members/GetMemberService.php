<?php

declare(strict_types=1);

namespace App\Application\Services\Members;

use App\Application\Ports\Outbound\MemberRepositoryInterface;
use App\Domain\Entities\Member;
use App\Domain\Exceptions\EntityNotFoundException;

final class GetMemberService
{
    public function __construct(
        private readonly MemberRepositoryInterface $memberRepository
    ) {}

    /**
     * @return array{member: Member, active_loans: int}
     */
    public function execute(string $memberId): array
    {
        $member = $this->memberRepository->findById($memberId);
        if ($member === null) {
            throw new EntityNotFoundException('El socio solicitado no existe.');
        }

        $activeLoans = $this->memberRepository->countActiveLoanItems($memberId);

        return [
            'member' => $member,
            'active_loans' => $activeLoans,
        ];
    }
}
