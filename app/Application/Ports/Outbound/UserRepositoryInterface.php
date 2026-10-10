<?php

declare(strict_types=1);

namespace App\Application\Ports\Outbound;

use App\Domain\Entities\User;
use App\Domain\ValueObjects\Username;

interface UserRepositoryInterface
{
    public function findById(string $id): ?User;

    public function findByUsername(Username $username): ?User;

    public function save(User $user): void;

    /** @return User[] */
    public function findAll(): array;

    public function delete(string $id): void;
}
