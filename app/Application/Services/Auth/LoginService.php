<?php

declare(strict_types=1);

namespace App\Application\Services\Auth;

use App\Application\Ports\Outbound\PasswordHasherInterface;
use App\Application\Ports\Outbound\TokenIssuerInterface;
use App\Application\Ports\Outbound\UserRepositoryInterface;
use App\Domain\Exceptions\UnauthorizedException;
use App\Domain\ValueObjects\Username;

final class LoginService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly PasswordHasherInterface $passwordHasher,
        private readonly TokenIssuerInterface $tokenIssuer
    ) {}

    /**
     * @return array{access_token: string, token_type: string, expires_in: int, role: string}
     */
    public function execute(string $rawUsername, string $rawPassword): array
    {
        try {
            $username = Username::fromString($rawUsername);
        } catch (\Throwable) {
            throw new UnauthorizedException('Credenciales inválidas.');
        }

        $user = $this->userRepository->findByUsername($username);
        if ($user === null) {
            throw new UnauthorizedException('Credenciales inválidas.');
        }

        if (!$user->isActive()) {
            throw new UnauthorizedException('El usuario se encuentra deshabilitado.');
        }

        if (!$this->passwordHasher->verify($rawPassword, $user->passwordHash())) {
            throw new UnauthorizedException('Credenciales inválidas.');
        }

        return $this->tokenIssuer->issue($user);
    }
}
