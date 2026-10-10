<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\ValueObjects\Role;
use App\Domain\ValueObjects\Username;

final class User
{
    public function __construct(
        private readonly string $id,
        private readonly Username $username,
        private readonly string $passwordHash,
        private readonly Role $role,
        private bool $isActive = true
    ) {}

    public function id(): string
    {
        return $this->id;
    }

    public function username(): Username
    {
        return $this->username;
    }

    public function passwordHash(): string
    {
        return $this->passwordHash;
    }

    public function role(): Role
    {
        return $this->role;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function disable(): void
    {
        $this->isActive = false;
    }

    public function enable(): void
    {
        $this->isActive = true;
    }
}
