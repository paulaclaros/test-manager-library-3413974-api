<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Mappers;

use App\Domain\Entities\User;
use App\Domain\ValueObjects\Role;
use App\Domain\ValueObjects\Username;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;

final class UserMapper
{
    public static function toDomain(UserModel $model): User
    {
        return new User(
            (string) $model->id,
            Username::fromString((string) $model->username),
            (string) $model->password_hash,
            new Role((string) $model->role)
        );
    }

    public static function toModel(User $entity): UserModel
    {
        $model = UserModel::find($entity->id()) ?? new UserModel();
        $model->id = $entity->id();
        $model->username = $entity->username()->value();
        $model->password_hash = $entity->passwordHash();
        $model->role = $entity->role()->value();

        return $model;
    }
}
