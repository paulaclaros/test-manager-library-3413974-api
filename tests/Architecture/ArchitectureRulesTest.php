<?php

declare(strict_types=1);

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ArchitectureRulesTest extends TestCase
{
    public function test_article_1_domain_does_not_depend_on_anything_outside(): void
    {
        $domainPath = __DIR__ . '/../../app/Domain';
        $files = $this->getAllPhpFiles($domainPath);

        foreach ($files as $file) {
            $content = file_get_contents($file);
            $this->assertStringNotContainsString('Illuminate\\', $content, "El archivo [{$file}] viola el Artículo I (depende de Illuminate).");
            $this->assertStringNotContainsString('App\\Application\\', $content, "El archivo [{$file}] viola el Artículo I (depende de Application).");
            $this->assertStringNotContainsString('App\\Infrastructure\\', $content, "El archivo [{$file}] viola el Artículo I (depende de Infrastructure).");
            $this->assertStringNotContainsString('App\\Presentation\\', $content, "El archivo [{$file}] viola el Artículo I (depende de Presentation).");
            $this->assertStringNotContainsString('App\\Bootstrap\\', $content, "El archivo [{$file}] viola el Artículo I (depende de Bootstrap).");
        }
    }

    public function test_article_2_application_does_not_depend_on_adapters_or_infrastructure(): void
    {
        $appPath = __DIR__ . '/../../app/Application';
        $files = $this->getAllPhpFiles($appPath);

        foreach ($files as $file) {
            $content = file_get_contents($file);
            $this->assertStringNotContainsString('App\\Infrastructure\\', $content, "El archivo [{$file}] viola el Artículo II (depende de Infrastructure).");
            $this->assertStringNotContainsString('App\\Presentation\\', $content, "El archivo [{$file}] viola el Artículo II (depende de Presentation).");
            $this->assertStringNotContainsString('App\\Bootstrap\\', $content, "El archivo [{$file}] viola el Artículo II (depende de Bootstrap).");
        }
    }

    /**
     * @return array<int, string>
     */
    private function getAllPhpFiles(string $dir): array
    {
        $results = [];
        $files = scandir($dir);
        if ($files === false) {
            return [];
        }

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $fullPath = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($fullPath)) {
                $results = array_merge($results, $this->getAllPhpFiles($fullPath));
            } elseif (str_ends_with($file, '.php')) {
                $results[] = $fullPath;
            }
        }

        return $results;
    }
}
