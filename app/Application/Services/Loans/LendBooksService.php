<?php

declare(strict_types=1);

namespace App\Application\Services\Loans;

use App\Application\Ports\Outbound\BookRepositoryInterface;
use App\Application\Ports\Outbound\CategoryRepositoryInterface;
use App\Application\Ports\Outbound\ClockInterface;
use App\Application\Ports\Outbound\LoanRepositoryInterface;
use App\Application\Ports\Outbound\MemberRepositoryInterface;
use App\Application\Ports\Outbound\UnitOfWorkInterface;
use App\Domain\Entities\Loan;
use App\Domain\Entities\LoanItem;
use App\Domain\Exceptions\DuplicateBookInLoanException;
use App\Domain\Exceptions\EntityNotFoundException;
use App\Domain\Exceptions\MemberHasOverdueException;
use App\Domain\Exceptions\MemberLimitExceededException;
use App\Domain\Exceptions\ValidationException;
use DateInterval;
use Illuminate\Support\Str;

final class LendBooksService
{
    public function __construct(
        private readonly MemberRepositoryInterface $memberRepository,
        private readonly BookRepositoryInterface $bookRepository,
        private readonly CategoryRepositoryInterface $categoryRepository,
        private readonly LoanRepositoryInterface $loanRepository,
        private readonly UnitOfWorkInterface $unitOfWork,
        private readonly ClockInterface $clock
    ) {}

    /**
     * @param array<int, string> $bookIds
     */
    public function execute(string $memberId, string $userId, array $bookIds): Loan
    {
        $bookCount = count($bookIds);
        if ($bookCount < 1 || $bookCount > 3) {
            throw new ValidationException('Un préstamo debe contener entre 1 y 3 libros.', [
                ['field' => 'items', 'issue' => 'debe contener entre 1 y 3 libros']
            ]);
        }

        // Verificar duplicados en la petición (CA-06.2)
        if (count(array_unique($bookIds)) !== $bookCount) {
            throw new DuplicateBookInLoanException('No se puede repetir el mismo libro en un préstamo.');
        }

        $member = $this->memberRepository->findById($memberId);
        if ($member === null) {
            throw new EntityNotFoundException('El socio especificado no existe.');
        }

        $now = $this->clock->now();

        // CA-06.5: Socio con algún libro vencido no puede pedir otro (409)
        if ($this->memberRepository->hasOverdueLoanItems($memberId, $now)) {
            throw new MemberHasOverdueException('El socio tiene libros vencidos sin devolver y no puede solicitar préstamos.');
        }

        // CA-06.4: Máximo 3 libros sin devolver en total (422)
        $currentActive = $this->memberRepository->countActiveLoanItems($memberId);
        if (($currentActive + $bookCount) > 3) {
            throw new MemberLimitExceededException(
                "El socio ya tiene {$currentActive} libro(s) sin devolver. No puede superar el límite máximo de 3 libros."
            );
        }

        // Obtener libros por lote (Q3)
        $books = $this->bookRepository->findByIds($bookIds);
        foreach ($bookIds as $bId) {
            if (!isset($books[$bId]) || !$books[$bId]->isActive()) {
                throw new EntityNotFoundException("El libro con id «{$bId}» no existe o fue dado de baja.");
            }
        }

        // Categorías para congelar nombre
        $categories = [];
        foreach ($this->categoryRepository->findAll() as $cat) {
            $categories[$cat->id()] = $cat->name();
        }

        $loanId = (string) Str::uuid();
        $dueAt = $now->add(new DateInterval('P14D')); // loaned_at + 14 días

        // Descontar disponible en cada libro (CA-06.3)
        $loanItems = [];
        foreach ($bookIds as $bId) {
            $book = $books[$bId];
            $book->withdraw(1);

            $categoryName = $categories[$book->categoryId()] ?? 'General';
            $loanItems[] = new LoanItem(
                (string) Str::uuid(),
                $loanId,
                $book->id(),
                $book->title(),
                $book->isbn()->value(),
                $categoryName,
                $dueAt,
                null
            );
        }

        $loan = new Loan(
            $loanId,
            $memberId,
            $userId,
            $now,
            $loanItems
        );

        // Operación atómica en una sola transacción
        return $this->unitOfWork->transaction(function () use ($books, $loan) {
            foreach ($books as $b) {
                $this->bookRepository->save($b);
            }
            $this->loanRepository->save($loan);
            return $loan;
        });
    }
}
