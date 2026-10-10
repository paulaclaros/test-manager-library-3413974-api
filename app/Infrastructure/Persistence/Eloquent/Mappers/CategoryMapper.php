<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Mappers;

use App\Domain\Entities\Category;
use App\Infrastructure\Persistence\Eloquent\Models\CategoryModel;

final class CategoryMapper
{
    public static function toDomain(CategoryModel $model): Category
    {
        return new Category(
            (string) $model->id,
            (string) $model->name
        );
    }
}
