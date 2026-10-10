<?php

declare(strict_types=1);

namespace App\Infrastructure\Storage;

use App\Application\Ports\Outbound\CoverStorageInterface;
use Illuminate\Support\Str;

final class LocalCoverStorage implements CoverStorageInterface
{
    private readonly string $storageDir;

    public function __construct()
    {
        $this->storageDir = storage_path('app/public/covers');
        if (!is_dir($this->storageDir)) {
            @mkdir($this->storageDir, 0777, true);
        }
    }

    public function store(string $tempFilePath, string $mimeType): string
    {
        $ext = $mimeType === 'image/png' ? 'png' : 'jpg';
        $key = 'cov_' . Str::random(32) . '.' . $ext;
        $target = $this->storageDir . DIRECTORY_SEPARATOR . $key;

        copy($tempFilePath, $target);

        return $key;
    }

    public function delete(string $coverKey): void
    {
        $target = $this->storageDir . DIRECTORY_SEPARATOR . $coverKey;
        if (file_exists($target)) {
            @unlink($target);
        }
    }

    public function getAbsolutePath(string $coverKey): ?string
    {
        $target = $this->storageDir . DIRECTORY_SEPARATOR . $coverKey;
        return file_exists($target) ? $target : null;
    }

    public function getUrl(string $coverKey): ?string
    {
        return '/api/v1/covers/' . $coverKey;
    }
}
