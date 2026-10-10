<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\Ports\Outbound\CategoryRepositoryInterface;
use App\Application\Ports\Outbound\CoverStorageInterface;
use App\Application\Services\Books\CreateBookService;
use App\Application\Services\Books\DeleteBookService;
use App\Application\Services\Books\DeleteCoverService;
use App\Application\Services\Books\GetBookService;
use App\Application\Services\Books\SearchBooksService;
use App\Application\Services\Books\UpdateBookService;
use App\Application\Services\Books\UploadCoverService;
use App\Domain\Entities\Book;
use App\Domain\Exceptions\EntityNotFoundException;
use App\Domain\Exceptions\ValidationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class BookController
{
    public function __construct(
        private readonly SearchBooksService $searchBooksService,
        private readonly GetBookService $getBookService,
        private readonly CreateBookService $createBookService,
        private readonly UpdateBookService $updateBookService,
        private readonly DeleteBookService $deleteBookService,
        private readonly UploadCoverService $uploadCoverService,
        private readonly DeleteCoverService $deleteCoverService,
        private readonly CategoryRepositoryInterface $categoryRepository,
        private readonly CoverStorageInterface $coverStorage
    ) {}

    public function index(Request $request): JsonResponse
    {
        $result = $this->searchBooksService->execute([
            'q' => $request->query('q'),
            'category_id' => $request->query('category_id'),
            'page' => (int) $request->query('page', 1),
            'size' => (int) $request->query('size', 20),
        ]);

        $items = array_map(function ($row) {
            /** @var Book $book */
            $book = $row['book'];
            return [
                'id' => $book->id(),
                'title' => $book->title(),
                'author' => $book->author(),
                'isbn' => $book->isbn()->value(),
                'category' => [
                    'id' => $book->categoryId(),
                    'name' => $row['category_name'],
                ],
                'total_copies' => $book->totalCopies(),
                'available_copies' => $book->availableCopies(),
                'cover_key' => $book->coverKey(),
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
        $result = $this->getBookService->execute($id);
        $book = $result['book'];
        $cat = $result['category'];

        return new JsonResponse($this->formatBook($book, $cat->name()), 200);
    }

    public function store(Request $request): JsonResponse
    {
        $title = (string) $request->input('title', '');
        $author = (string) $request->input('author', '');
        $isbn = (string) $request->input('isbn', '');
        $categoryId = (string) $request->input('category_id', '');
        $totalCopies = (int) $request->input('total_copies', 1);

        $book = $this->createBookService->execute($title, $author, $isbn, $categoryId, $totalCopies);
        $cat = $this->categoryRepository->findById($book->categoryId());

        return new JsonResponse($this->formatBook($book, $cat?->name() ?? 'General'), 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $title = (string) $request->input('title', '');
        $author = (string) $request->input('author', '');
        $categoryId = (string) $request->input('category_id', '');
        $totalCopies = (int) $request->input('total_copies', 1);

        $book = $this->updateBookService->execute($id, $title, $author, $categoryId, $totalCopies);
        $cat = $this->categoryRepository->findById($book->categoryId());

        return new JsonResponse($this->formatBook($book, $cat?->name() ?? 'General'), 200);
    }

    public function destroy(string $id): JsonResponse
    {
        $this->deleteBookService->execute($id);
        return new JsonResponse(null, 204);
    }

    public function uploadCover(Request $request, string $id): JsonResponse
    {
        $file = $request->file('file');
        if ($file === null || !$file->isValid()) {
            throw new ValidationException('No se envió un archivo de portada válido.', [
                ['field' => 'file', 'issue' => 'archivo requerido']
            ]);
        }

        $book = $this->uploadCoverService->execute(
            $id,
            $file->getRealPath(),
            $file->getMimeType() ?: 'image/jpeg',
            $file->getSize()
        );

        $cat = $this->categoryRepository->findById($book->categoryId());

        return new JsonResponse($this->formatBook($book, $cat?->name() ?? 'General'), 200);
    }

    public function deleteCover(string $id): JsonResponse
    {
        $this->deleteCoverService->execute($id);
        return new JsonResponse(null, 204);
    }

    public function getCover(string $id): BinaryFileResponse
    {
        $result = $this->getBookService->execute($id);
        $book = $result['book'];
        if ($book->coverKey() === null) {
            throw new EntityNotFoundException('El libro no tiene portada.');
        }

        $path = $this->coverStorage->getAbsolutePath($book->coverKey());
        if ($path === null || !file_exists($path)) {
            throw new EntityNotFoundException('Archivo de portada no encontrado.');
        }

        $mime = str_ends_with($path, '.png') ? 'image/png' : 'image/jpeg';
        return response()->file($path, ['Content-Type' => $mime]);
    }

    private function formatBook(Book $book, string $categoryName): array
    {
        return [
            'id' => $book->id(),
            'title' => $book->title(),
            'author' => $book->author(),
            'isbn' => $book->isbn()->value(),
            'category' => [
                'id' => $book->categoryId(),
                'name' => $categoryName,
            ],
            'total_copies' => $book->totalCopies(),
            'available_copies' => $book->availableCopies(),
            'cover_key' => $book->coverKey(),
        ];
    }
}
