<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\Ports\Outbound\CategoryRepositoryInterface;
use Illuminate\Http\JsonResponse;

final class CategoryController
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository
    ) {}

    public function index(): JsonResponse
    {
        $categories = $this->categoryRepository->findAll();
        $payload = array_map(fn($cat) => [
            'id' => $cat->id(),
            'name' => $cat->name(),
        ], $categories);

        return new JsonResponse($payload, 200);
    }
}
