<?php

declare(strict_types=1);

namespace App\Application\Services\Loans;

use App\Application\Ports\Outbound\BookRepositoryInterface;
use App\Application\Ports\Outbound\ClockInterface;
use App\Application\Ports\Outbound\LoanRepositoryInterface;
use App\Application\Ports\Outbound\UnitOfWorkInterface;
use App\Domain\Entities\LoanItem;
use App\Domain\Exceptions\EntityNotFoundException;

final class ReturnItemService
{
    public function __construct(
        private readonly LoanRepositoryInterface $loanRepository,
        private readonly BookRepositoryInterface $bookRepository,
        private readonly UnitOfWorkInterface $unitOfWork,
        private readonly ClockInterface $clock
    ) {}

    public function execute(string $loanId, string $itemId): LoanItem
    {
        $item = $this->loanRepository->findItemById($loanId, $itemId);
        if ($item === null) {
            throw new EntityNotFoundException('La línea de préstamo solicitada no existe.');
        }

        $now = $this->clock->now();
        $item->markAsReturned($now); // Lanza ItemAlreadyReturnedException si ya estaba devuelto

        $book = $this->bookRepository->findById($item->bookId());
        if ($book !== null) {
            $book->restock(1);
        }

        return $this->unitOfWork->transaction(function () use ($item, $book) {
            $this->loanRepository->saveItem($item);
            if ($book !== null) {
                $this->bookRepository->save($book);
            }
            return $item;
        });
    }
}
