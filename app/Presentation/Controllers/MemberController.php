<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\Services\Members\CreateMemberService;
use App\Application\Services\Members\GetMemberService;
use App\Application\Services\Members\SearchMembersService;
use App\Domain\Entities\Member;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MemberController
{
    public function __construct(
        private readonly SearchMembersService $searchMembersService,
        private readonly GetMemberService $getMemberService,
        private readonly CreateMemberService $createMemberService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $result = $this->searchMembersService->execute([
            'q' => $request->query('q'),
            'page' => (int) $request->query('page', 1),
            'size' => (int) $request->query('size', 20),
        ]);

        $items = array_map(function ($row) {
            /** @var Member $m */
            $m = $row['member'];
            return [
                'id' => $m->id(),
                'full_name' => $m->fullName(),
                'document_number' => $m->documentNumber(),
                'active_loans' => $row['active_loans'],
            ];
        }, $result['items']);

        return new JsonResponse([
            'items' => $items,
            'page' => $result['page'],
            'size' => $result['size'],
            'total' => $result['total'],
        ], 200);
    }

    public function show(string $id): JsonResponse
    {
        $result = $this->getMemberService->execute($id);
        /** @var Member $m */
        $m = $result['member'];

        return new JsonResponse([
            'id' => $m->id(),
            'full_name' => $m->fullName(),
            'document_number' => $m->documentNumber(),
            'active_loans' => $result['active_loans'],
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $fullName = (string) $request->input('full_name', '');
        $documentNumber = (string) $request->input('document_number', '');

        $member = $this->createMemberService->execute($fullName, $documentNumber);

        return new JsonResponse([
            'id' => $member->id(),
            'full_name' => $member->fullName(),
            'document_number' => $member->documentNumber(),
            'active_loans' => 0,
        ], 201);
    }
}
