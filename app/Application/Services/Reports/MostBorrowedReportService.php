<?php

declare(strict_types=1);

namespace App\Application\Services\Reports;

use App\Application\Ports\Outbound\ReportReaderInterface;
use App\Domain\ValueObjects\DateRange;

final class MostBorrowedReportService
{
    public function __construct(
        private readonly ReportReaderInterface $reportReader
    ) {}

    /**
     * @return array{from: string, to: string, rows: array<int, array{book_id: string, book_title: string, category_name: string, times_borrowed: int}>}
     */
    public function execute(string $from, string $to): array
    {
        $range = new DateRange($from, $to);
        $rows = $this->reportReader->getMostBorrowed($range);

        return [
            'from' => $range->fromString(),
            'to' => $range->toString(),
            'rows' => $rows,
        ];
    }
}
