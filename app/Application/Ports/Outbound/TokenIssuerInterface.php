<?php

declare(strict_types=1);

namespace App\Application\Ports\Outbound;

use App\Domain\Entities\User;

interface TokenIssuerInterface
{
    /**
     * @return array{access_token: string, token_type: string, expires_in: int, role: string}
     */
    public function issue(User $user): array;

    /**
     * @return array{sub: string, role: string}|null
     */
    public function validate(string $token): ?array;
}
