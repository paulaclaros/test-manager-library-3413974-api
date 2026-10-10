<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Domain\Exceptions\DomainException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException as LaravelValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

final class Handler extends ExceptionHandler
{
    protected $dontReport = [
        DomainException::class,
    ];

    public function register(): void
    {
        $this->renderable(function (DomainException $e): JsonResponse {
            return new JsonResponse([
                'code' => $e->getErrorCode(),
                'message' => $e->getMessage(),
                'details' => $e->getDetails(),
            ], $e->getHttpStatus());
        });

        $this->renderable(function (LaravelValidationException $e): JsonResponse {
            $details = [];
            foreach ($e->errors() as $field => $messages) {
                foreach ($messages as $msg) {
                    $details[] = ['field' => (string) $field, 'issue' => (string) $msg];
                }
            }
            return new JsonResponse([
                'code' => 'VALIDATION_ERROR',
                'message' => 'Error de validación en los datos enviados.',
                'details' => $details,
            ], 422);
        });

        $this->renderable(function (HttpException $e): JsonResponse {
            $code = match ($e->getStatusCode()) {
                401 => 'INVALID_CREDENTIALS',
                403 => 'FORBIDDEN_ROLE',
                404 => 'NOT_FOUND',
                default => 'HTTP_ERROR',
            };
            return new JsonResponse([
                'code' => $code,
                'message' => $e->getMessage() ?: 'Error en la petición.',
                'details' => [],
            ], $e->getStatusCode());
        });
    }
}
