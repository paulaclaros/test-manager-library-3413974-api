<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\Exceptions\BookNotAvailableException;
use App\Domain\Exceptions\ValidationException;
use App\Domain\ValueObjects\Isbn;
use DateTimeImmutable;

final class Book
{
    public function __construct(
        private readonly string $id,
        private string $title,
        private string $author,
        private Isbn $isbn,
        private string $categoryId,
        private int $totalCopies,
        private int $availableCopies,
        private ?string $coverKey = null,
        private ?DateTimeImmutable $deletedAt = null
    ) {
        $this->title = trim($title);
        $this->author = trim($author);
        if ($this->title === '') {
            throw new ValidationException('El título del libro no puede estar vacío.', [
                ['field' => 'title', 'issue' => 'no puede estar vacío']
            ]);
        }
        if ($this->author === '') {
            throw new ValidationException('El autor del libro no puede estar vacío.', [
                ['field' => 'author', 'issue' => 'no puede estar vacío']
            ]);
        }
        if ($this->totalCopies < 1) {
            throw new ValidationException('El total de ejemplares debe ser al menos 1.', [
                ['field' => 'total_copies', 'issue' => 'debe ser mayor o igual a 1']
            ]);
        }
        if ($this->availableCopies < 0 || $this->availableCopies > $this->totalCopies) {
            throw new ValidationException('Los ejemplares disponibles deben estar entre 0 y el total.', [
                ['field' => 'available_copies', 'issue' => 'rango inválido']
            ]);
        }
    }

    public function id(): string
    {
        return $this->id;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function author(): string
    {
        return $this->author;
    }

    public function isbn(): Isbn
    {
        return $this->isbn;
    }

    public function categoryId(): string
    {
        return $this->categoryId;
    }

    public function totalCopies(): int
    {
        return $this->totalCopies;
    }

    public function availableCopies(): int
    {
        return $this->availableCopies;
    }

    public function coverKey(): ?string
    {
        return $this->coverKey;
    }

    public function deletedAt(): ?DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function isActive(): bool
    {
        return $this->deletedAt === null;
    }

    public function withdraw(int $count = 1): void
    {
        if ($count <= 0) {
            throw new ValidationException('La cantidad a descontar debe ser mayor que cero.');
        }
        if ($this->availableCopies < $count) {
            throw new BookNotAvailableException(
                "No quedan ejemplares disponibles de «{$this->title}».",
                [['field' => 'items', 'issue' => 'sin ejemplares suficientes']]
            );
        }
        $this->availableCopies -= $count;
    }

    public function restock(int $count = 1): void
    {
        if ($count <= 0) {
            throw new ValidationException('La cantidad a devolver debe ser mayor que cero.');
        }
        if ($this->availableCopies + $count > $this->totalCopies) {
            throw new ValidationException('Los ejemplares disponibles no pueden superar el total de copias.');
        }
        $this->availableCopies += $count;
    }

    public function update(string $title, string $author, string $categoryId, int $newTotalCopies): void
    {
        $cleanTitle = trim($title);
        $cleanAuthor = trim($author);
        if ($cleanTitle === '') {
            throw new ValidationException('El título no puede estar vacío.');
        }
        if ($cleanAuthor === '') {
            throw new ValidationException('El autor no puede estar vacío.');
        }
        if ($newTotalCopies < 1) {
            throw new ValidationException('El total de ejemplares debe ser mayor o igual a 1.');
        }

        $difference = $newTotalCopies - $this->totalCopies;
        $newAvailable = $this->availableCopies + $difference;
        if ($newAvailable < 0) {
            throw new ValidationException('Cambiar el total de copias dejaría los disponibles en negativo (hay ejemplares prestados).');
        }

        $this->title = $cleanTitle;
        $this->author = $cleanAuthor;
        $this->categoryId = $categoryId;
        $this->totalCopies = $newTotalCopies;
        $this->availableCopies = $newAvailable;
    }

    public function setCoverKey(?string $coverKey): void
    {
        $this->coverKey = $coverKey !== null && trim($coverKey) !== '' ? trim($coverKey) : null;
    }

    public function deactivate(DateTimeImmutable $now): void
    {
        $this->deletedAt = $now;
    }
}
