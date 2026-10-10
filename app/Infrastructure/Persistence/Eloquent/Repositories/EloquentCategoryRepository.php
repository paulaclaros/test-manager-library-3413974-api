<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Application\Ports\Outbound\CategoryRepositoryInterface;
use App\Domain\Entities\Category;
use App\Infrastructure\Persistence\Eloquent\Mappers\CategoryMapper;
use App\Infrastructure\Persistence\Eloquent\Models\CategoryModel;

final class EloquentCategoryRepository implements CategoryRepositoryInterface
{
    public function findById(string $id): ?Category
    {
        $model = CategoryModel::find($id);
        return $model ? CategoryMapper::toDomain($model) : null;
    }

    public function findAll(): array
    {
        return CategoryModel::orderBy('name', 'asc')
            ->get()
            ->map(fn(CategoryModel $m) => CategoryMapper::toDomain($m))
            ->all();
    }
}
