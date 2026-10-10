<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\Services\Reports\MostBorrowedReportService;
use App\Domain\Exceptions\ValidationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ReportController
{
    public function __construct(
        private readonly MostBorrowedReportService $mostBorrowedReportService
    ) {}

    public function mostBorrowed(Request $request): JsonResponse
    {
        $from = (string) $request->query('from', '');
        $to = (string) $request->query('to', '');

        if ($from === '' || $to === '') {
            throw new ValidationException('Los parámetros from y to son obligatorios para generar el reporte.', [
                ['field' => 'from', 'issue' => 'requerido'],
                ['field' => 'to', 'issue' => 'requerido'],
            ]);
        }

        $report = $this->mostBorrowedReportService->execute($from, $to);

        return new JsonResponse($report, 200);
    }
}
