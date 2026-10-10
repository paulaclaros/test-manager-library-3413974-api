<?php

declare(strict_types=1);

namespace App\Application\Services\Members;

use App\Application\Ports\Outbound\MemberRepositoryInterface;
use App\Domain\Entities\Member;
use App\Domain\Exceptions\DocumentTakenException;
use Illuminate\Support\Str;

final class CreateMemberService
{
    public function __construct(
        private readonly MemberRepositoryInterface $memberRepository
    ) {}

    public function execute(string $fullName, string $documentNumber): Member
    {
        $cleanDoc = trim($documentNumber);
        $existing = $this->memberRepository->findByDocumentNumber($cleanDoc);
        if ($existing !== null) {
            throw new DocumentTakenException("El documento «{$cleanDoc}» ya está registrado para otro socio.");
        }

        $member = new Member(
            (string) Str::uuid(),
            $fullName,
            $cleanDoc
        );

        $this->memberRepository->save($member);

        return $member;
    }
}
