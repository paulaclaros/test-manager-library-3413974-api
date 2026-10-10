<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\Ports\Outbound\UserRepositoryInterface;
use App\Application\Services\Auth\CreateLibrarianService;
use App\Application\Services\Auth\LoginService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AuthController
{
    public function __construct(
        private readonly LoginService $loginService,
        private readonly CreateLibrarianService $createLibrarianService,
        private readonly UserRepositoryInterface $userRepository
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

    public function listUsers(): JsonResponse
    {
        $users = $this->userRepository->findAll();
        $items = array_map(fn($u) => [
            'id' => $u->id(),
            'username' => $u->username()->value(),
            'role' => $u->role()->value(),
        ], $users);

        return new JsonResponse(['items' => $items, 'total' => count($items)], 200);
    }

    public function deleteUser(string $id): JsonResponse
    {
        $user = $this->userRepository->findById($id);
        if ($user === null) {
            return new JsonResponse(['code' => 'NOT_FOUND', 'message' => 'Usuario no encontrado.'], 404);
        }

        if (strtolower($user->username()->value()) === 'admin') {
            return new JsonResponse([
                'code' => 'CANNOT_DELETE_ADMIN',
                'message' => 'No es posible eliminar ni deshabilitar el usuario administrador principal.',
            ], 422);
        }

        $this->userRepository->delete($id);

        return new JsonResponse(null, 204);
    }
}
