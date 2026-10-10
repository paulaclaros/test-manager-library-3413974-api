<?php

declare(strict_types=1);

namespace App\Console;

use App\Console\Commands\InitAdminCommand;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        InitAdminCommand::class,
    ];

    protected function commands(): void
    {
    }
}
