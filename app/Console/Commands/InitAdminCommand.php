<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Application\Ports\Outbound\PasswordHasherInterface;
use App\Application\Ports\Outbound\UserRepositoryInterface;
use App\Domain\Entities\User;
use App\Domain\ValueObjects\Role;
use App\Domain\ValueObjects\Username;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

final class InitAdminCommand extends Command
{
    protected $signature = 'library:init-admin';
    protected $description = 'Inicializa el administrador inicial desde variables de entorno';

    public function handle(
        UserRepositoryInterface $userRepository,
        PasswordHasherInterface $hasher
    ): int {
        $rawUsername = (string) env('ADMIN_USERNAME', 'admin');
        $rawPassword = (string) env('ADMIN_PASSWORD', 'Admin123*');

        $username = Username::fromString($rawUsername);
        $existing = $userRepository->findByUsername($username);

        if ($existing === null) {
            $admin = new User(
                (string) Str::uuid(),
                $username,
                $hasher->hash($rawPassword),
                Role::admin()
            );
            $userRepository->save($admin);
            $this->info("Administrador inicial «{$username->value()}» creado correctamente.");
        } else {
            $this->info("El administrador «{$username->value()}» ya existe.");
        }

        return 0;
    }
}
