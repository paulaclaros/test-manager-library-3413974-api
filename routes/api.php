<?php

declare(strict_types=1);

use App\Presentation\Controllers\AuthController;
use App\Presentation\Controllers\BookController;
use App\Presentation\Controllers\CategoryController;
use App\Presentation\Controllers\HealthController;
use App\Presentation\Controllers\LoanController;
use App\Presentation\Controllers\MemberController;
use App\Presentation\Controllers\ReportController;
use App\Presentation\Middleware\JwtAuthMiddleware;
use App\Presentation\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

// 1. Salud del servicio (público)
Route::get('/health', [HealthController::class, 'health']);

// 2. Rutas con prefijo /api/v1
Route::prefix('api/v1')->group(function () {

    // Autenticación pública
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Descarga de portadas
    Route::get('/books/{id}/cover', [BookController::class, 'getCover']);

    // Rutas protegidas (admin y librarian)
    Route::middleware([JwtAuthMiddleware::class])->group(function () {

        // Categorías
        Route::get('/categories', [CategoryController::class, 'index']);

        // Libros (Lectura)
        Route::get('/books', [BookController::class, 'index']);
        Route::get('/books/{id}', [BookController::class, 'show']);

        // Socios
        Route::get('/members', [MemberController::class, 'index']);
        Route::get('/members/{id}', [MemberController::class, 'show']);
        Route::post('/members', [MemberController::class, 'store']);

        // Préstamos (IMPORTANTE: overdue antes de {id})
        Route::post('/loans', [LoanController::class, 'store']);
        Route::get('/loans', [LoanController::class, 'index']);
        Route::get('/loans/overdue', [LoanController::class, 'overdue']);
        Route::get('/loans/{id}', [LoanController::class, 'show']);
        Route::post('/loans/{loanId}/items/{itemId}/return', [LoanController::class, 'returnItem']);

        // Rutas exclusivas para admin
        Route::middleware([RoleMiddleware::class . ':admin'])->group(function () {
            // Usuarios / Bibliotecarios
            Route::post('/users', [AuthController::class, 'registerUser']);

            // Gestión de libros
            Route::post('/books', [BookController::class, 'store']);
            Route::put('/books/{id}', [BookController::class, 'update']);
            Route::delete('/books/{id}', [BookController::class, 'destroy']);
            Route::put('/books/{id}/cover', [BookController::class, 'uploadCover']);
            Route::delete('/books/{id}/cover', [BookController::class, 'deleteCover']);

            // Reportes
            Route::get('/reports/most-borrowed', [ReportController::class, 'mostBorrowed']);
        });
    });
});
