<?php

declare(strict_types=1);

namespace App\Application\Ports\Outbound;

interface CoverStorageInterface
{
    public function store(string $tempFilePath, string $mimeType): string;

    public function delete(string $coverKey): void;

    public function getAbsolutePath(string $coverKey): ?string;

    public function getUrl(string $coverKey): ?string;
}
