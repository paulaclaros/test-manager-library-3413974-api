<?php

declare(strict_types=1);

namespace App\Application\Services\Books;

use App\Application\Ports\Outbound\BookRepositoryInterface;
use App\Application\Ports\Outbound\CoverStorageInterface;
use App\Domain\Exceptions\EntityNotFoundException;

final class DeleteCoverService
{
    public function __construct(
        private readonly BookRepositoryInterface $bookRepository,
        private readonly CoverStorageInterface $coverStorage
    ) {}

    public function execute(string $bookId): void
    {
        $book = $this->bookRepository->findById($bookId);
        if ($book === null || !$book->isActive()) {
            throw new EntityNotFoundException('El libro solicitado no existe o fue dado de baja.');
        }

        $oldKey = $book->coverKey();
        if ($oldKey !== null) {
            $book->setCoverKey(null);
            $this->bookRepository->save($book);
            $this->coverStorage->delete($oldKey);
        }
    }
}
