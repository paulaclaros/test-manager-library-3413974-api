<?php

declare(strict_types=1);

namespace App\Application\Services\Books;

use App\Application\Ports\Outbound\BookRepositoryInterface;
use App\Application\Ports\Outbound\CoverStorageInterface;
use App\Domain\Entities\Book;
use App\Domain\Exceptions\EntityNotFoundException;
use App\Domain\Exceptions\ValidationException;

final class UploadCoverService
{
    public function __construct(
        private readonly BookRepositoryInterface $bookRepository,
        private readonly CoverStorageInterface $coverStorage
    ) {}

    public function execute(string $bookId, string $tempFilePath, string $mimeType, int $fileSize): Book
    {
        $book = $this->bookRepository->findById($bookId);
        if ($book === null || !$book->isActive()) {
            throw new EntityNotFoundException('El libro solicitado no existe o fue dado de baja.');
        }

        // Máx 2 MB (2 * 1024 * 1024 = 2097152 bytes)
        if ($fileSize > 2097152) {
            throw new ValidationException('El archivo de portada supera el límite máximo de 2 MB.', [
                ['field' => 'file', 'issue' => 'tamaño máximo 2 MB']
            ]);
        }

        $validMimes = ['image/jpeg', 'image/png'];
        if (!in_array($mimeType, $validMimes, true)) {
            throw new ValidationException('El formato de la portada debe ser JPG o PNG.', [
                ['field' => 'file', 'issue' => 'solo se admite image/jpeg o image/png']
            ]);
        }

        // Si ya tenía portada, se borra la anterior
        if ($book->coverKey() !== null) {
            $this->coverStorage->delete($book->coverKey());
        }

        $coverKey = $this->coverStorage->store($tempFilePath, $mimeType);
        $book->setCoverKey($coverKey);
        $this->bookRepository->save($book);

        return $book;
    }
}
