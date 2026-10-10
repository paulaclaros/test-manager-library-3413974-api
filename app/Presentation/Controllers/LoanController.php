<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\Services\Loans\GetLoanService;
use App\Application\Services\Loans\GetOverdueLoansService;
use App\Application\Services\Loans\LendBooksService;
use App\Application\Services\Loans\ListLoansService;
use App\Application\Services\Loans\ReturnItemService;
use App\Domain\Entities\Loan;
use App\Domain\Entities\LoanItem;
use App\Domain\ValueObjects\LoanStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class LoanController
{
    public function __construct(
        private readonly LendBooksService $lendBooksService,
        private readonly ReturnItemService $returnItemService,
        private readonly GetLoanService $getLoanService,
        private readonly ListLoansService $listLoansService,
        private readonly GetOverdueLoansService $getOverdueLoansService
    ) {}

    public function store(Request $request): JsonResponse
    {
        $memberId = (string) $request->input('member_id', '');
        $userId = (string) $request->attributes->get('auth_user_id', '');

        $rawItems = (array) $request->input('items', []);
        $bookIds = [];
        foreach ($rawItems as $item) {
            if (isset($item['book_id'])) {
                $bookIds[] = (string) $item['book_id'];
            }
        }

        $loan = $this->lendBooksService->execute($memberId, $userId, $bookIds);

        return new JsonResponse($this->formatLoan($loan, LoanStatus::open()), 201);
    }

    public function index(Request $request): JsonResponse
    {
        $result = $this->listLoansService->execute([
            'from' => $request->query('from'),
            'to' => $request->query('to'),
            'status' => $request->query('status'),
            'member_id' => $request->query('member_id'),
            'page' => (int) $request->query('page', 1),
            'size' => (int) $request->query('size', 20),
        ]);

        $items = array_map(function ($row) {
            /** @var Loan $loan */
            $loan = $row['loan'];
            /** @var LoanStatus $status */
            $status = $row['status'];
            return $this->formatLoan($loan, $status);
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
        $result = $this->getLoanService->execute($id);
        /** @var Loan $loan */
        $loan = $result['loan'];
        /** @var LoanStatus $status */
        $status = $result['status'];

        return new JsonResponse($this->formatLoan($loan, $status), 200);
    }

    public function returnItem(string $loanId, string $itemId): JsonResponse
    {
        $item = $this->returnItemService->execute($loanId, $itemId);

        return new JsonResponse([
            'id' => $item->id(),
            'loan_id' => $item->loanId(),
            'book_id' => $item->bookId(),
            'book_title' => $item->bookTitle(),
            'isbn' => $item->isbn(),
            'category_name' => $item->categoryName(),
            'due_at' => $item->dueAt()->format('Y-m-d\TH:i:s\Z'),
            'returned_at' => $item->returnedAt()?->format('Y-m-d\TH:i:s\Z'),
        ], 200);
    }

    public function overdue(): JsonResponse
    {
        $items = $this->getOverdueLoansService->execute();

        return new JsonResponse([
            'items' => $items,
        ], 200);
    }

    private function formatLoan(Loan $loan, LoanStatus $status): array
    {
        $items = array_map(function (LoanItem $item) {
            return [
                'id' => $item->id(),
                'book_id' => $item->bookId(),
                'book_title' => $item->bookTitle(),
                'isbn' => $item->isbn(),
                'category_name' => $item->categoryName(),
                'due_at' => $item->dueAt()->format('Y-m-d\TH:i:s\Z'),
                'returned_at' => $item->returnedAt()?->format('Y-m-d\TH:i:s\Z'),
            ];
        }, $loan->items());

        return [
            'id' => $loan->id(),
            'member_id' => $loan->memberId(),
            'loaned_at' => $loan->loanedAt()->format('Y-m-d\TH:i:s\Z'),
            'status' => $status->value(),
            'items' => $items,
        ];
    }
}
