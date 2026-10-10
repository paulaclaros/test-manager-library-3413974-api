<?php

declare(strict_types=1);

namespace App\Application\Services\Auth;

use App\Application\Ports\Outbound\PasswordHasherInterface;
use App\Application\Ports\Outbound\UserRepositoryInterface;
use App\Domain\Entities\User;
use App\Domain\Exceptions\UsernameTakenException;
use App\Domain\Exceptions\ValidationException;
use App\Domain\ValueObjects\Role;
use App\Domain\ValueObjects\Username;
use Illuminate\Support\Str;

final class CreateLibrarianService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly PasswordHasherInterface $passwordHasher
    ) {}

    public function execute(string $rawUsername, string $rawPassword, string $rawRole): User
    {
        $roleClean = trim($rawRole);
        if ($roleClean !== Role::LIBRARIAN) {
            throw new ValidationException('Solo se permite la creación de usuarios con rol librarian.', [
                ['field' => 'role', 'issue' => 'solo admite el valor librarian']
            ]);
        }

        if (trim($rawPassword) === '') {
            throw new ValidationException('La contraseña no puede estar vacía.', [
                ['field' => 'password', 'issue' => 'no puede estar vacía']
            ]);
        }

        $username = Username::fromString($rawUsername);
        $existing = $this->userRepository->findByUsername($username);
        if ($existing !== null) {
            throw new UsernameTakenException("El nombre de usuario «{$username->value()}» ya está registrado.");
        }

        $hash = $this->passwordHasher->hash($rawPassword);
        $user = new User(
            (string) Str::uuid(),
            $username,
            $hash,
            Role::librarian()
        );

        $this->userRepository->save($user);

        return $user;
    }
}
