<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Application\Ports\Outbound\UserRepositoryInterface;
use App\Domain\Entities\User;
use App\Domain\ValueObjects\Username;
use App\Infrastructure\Persistence\Eloquent\Mappers\UserMapper;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;

final class EloquentUserRepository implements UserRepositoryInterface
{
    public function findById(string $id): ?User
    {
        $model = UserModel::find($id);
        return $model ? UserMapper::toDomain($model) : null;
    }

    public function findByUsername(Username $username): ?User
    {
        $model = UserModel::where('username', $username->value())->first();
        return $model ? UserMapper::toDomain($model) : null;
    }

    public function save(User $user): void
    {
        $model = UserMapper::toModel($user);
        $model->save();
    }

    /** @return User[] */
    public function findAll(): array
    {
        $models = UserModel::all();
        return $models->map(fn($m) => UserMapper::toDomain($m))->all();
    }

    public function delete(string $id): void
    {
        UserModel::destroy($id);
    }
}
