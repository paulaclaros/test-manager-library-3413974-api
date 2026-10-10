<?php

declare(strict_types=1);

namespace App\Presentation\Middleware;

use App\Application\Ports\Outbound\TokenIssuerInterface;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class JwtAuthMiddleware
{
    public function __construct(
        private readonly TokenIssuerInterface $tokenIssuer
    ) {}

    public function handle(Request $request, Closure $next): mixed
    {
        $authHeader = $request->header('Authorization', '');
        if (!str_starts_with($authHeader, 'Bearer ')) {
            return new JsonResponse([
                'code' => 'INVALID_CREDENTIALS',
                'message' => 'Token de autenticación no proporcionado o inválido.',
                'details' => [],
            ], 401);
        }

        $token = substr($authHeader, 7);
        $payload = $this->tokenIssuer->validate($token);
        if ($payload === null) {
            return new JsonResponse([
                'code' => 'INVALID_CREDENTIALS',
                'message' => 'Token expirado o inválido.',
                'details' => [],
            ], 401);
        }

        $request->attributes->set('auth_user_id', $payload['sub']);
        $request->attributes->set('auth_user_role', $payload['role']);

        return $next($request);
    }
}
