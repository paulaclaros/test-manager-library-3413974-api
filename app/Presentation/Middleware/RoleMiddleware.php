<?php

declare(strict_types=1);

namespace App\Presentation\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        $userRole = (string) $request->attributes->get('auth_user_role', '');

        if (!in_array($userRole, $roles, true)) {
            return new JsonResponse([
                'code' => 'FORBIDDEN_ROLE',
                'message' => 'No tiene permisos para acceder a este recurso.',
                'details' => [],
            ], 403);
        }

        return $next($request);
    }
}
