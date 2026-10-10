<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\Services\Auth\CreateLibrarianService;
use App\Application\Services\Auth\LoginService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AuthController
{
    public function __construct(
        private readonly LoginService $loginService,
        private readonly CreateLibrarianService $createLibrarianService
    ) {}

    public function login(Request $request): JsonResponse
    {
        $username = (string) $request->input('username', '');
        $password = (string) $request->input('password', '');

        $result = $this->loginService->execute($username, $password);

        return new JsonResponse($result, 200);
    }

    public function registerUser(Request $request): JsonResponse
    {
        $username = (string) $request->input('username', '');
        $password = (string) $request->input('password', '');
        $role = (string) $request->input('role', '');

        $user = $this->createLibrarianService->execute($username, $password, $role);

        return new JsonResponse([
            'id' => $user->id(),
            'username' => $user->username()->value(),
            'role' => $user->role()->value(),
        ], 201);
    }
}
