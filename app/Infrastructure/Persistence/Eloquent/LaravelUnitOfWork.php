<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Application\Ports\Outbound\UnitOfWorkInterface;
use Illuminate\Support\Facades\DB;

final class LaravelUnitOfWork implements UnitOfWorkInterface
{
    public function transaction(callable $operation): mixed
    {
        return DB::transaction($operation);
    }
}
