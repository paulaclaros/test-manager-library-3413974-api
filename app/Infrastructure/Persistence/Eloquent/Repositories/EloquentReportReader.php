<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Application\Ports\Outbound\ReportReaderInterface;
use App\Domain\ValueObjects\DateRange;
use Illuminate\Support\Facades\DB;

final class EloquentReportReader implements ReportReaderInterface
{
    public function getMostBorrowed(DateRange $range): array
    {
        $fromStr = $range->from()->format('Y-m-d H:i:sP');
        $toStr = $range->to()->format('Y-m-d H:i:sP');

        $rows = DB::table('library.loan_item as li')
            ->join('library.loan as l', 'l.id', '=', 'li.loan_id')
            ->where('l.loaned_at', '>=', $fromStr)
            ->where('l.loaned_at', '<=', $toStr)
            ->select([
                'li.book_id',
                'li.book_title',
                'li.category_name',
                DB::raw('COUNT(*) as times_borrowed')
            ])
            ->groupBy(['li.book_id', 'li.book_title', 'li.category_name'])
            ->orderByDesc('times_borrowed')
            ->orderBy('li.book_title', 'asc')
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'book_id' => (string) $row->book_id,
                'book_title' => (string) $row->book_title,
                'category_name' => (string) $row->category_name,
                'times_borrowed' => (int) $row->times_borrowed,
            ];
        }

        return $result;
    }
}
