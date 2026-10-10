<?php

declare(strict_types=1);

namespace App\Application\Services\Books;

use App\Application\Ports\Outbound\BookRepositoryInterface;
use App\Application\Ports\Outbound\ClockInterface;
use App\Domain\Exceptions\BookHasOpenLoansException;
use App\Domain\Exceptions\EntityNotFoundException;

final class DeleteBookService
{
    public function __construct(
        private readonly BookRepositoryInterface $bookRepository,
        private readonly ClockInterface $clock
    ) {}

    public function execute(string $bookId): void
    {
        $book = $this->bookRepository->findById($bookId);
        if ($book === null || !$book->isActive()) {
            throw new EntityNotFoundException('El libro solicitado no existe o ya fue dado de baja.');
        }

        if ($this->bookRepository->hasActiveLoans($bookId)) {
            throw new BookHasOpenLoansException('El libro tiene préstamos abiertos y no puede darse de baja.');
        }

        $book->deactivate($this->clock->now());
        $this->bookRepository->save($book);
    }
}
