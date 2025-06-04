<?php

declare(strict_types=1);

namespace Endroid\QrCode\Label\Font;

use Exception;

final class Font implements FontInterface
{
    private $size;
    private $path;

    public function __construct(string $path, int $size = 16)
    {
        $this->path = $path;
        $this->size = $size;
        $this->assertValidPath($path);
    }

    private function assertValidPath(string $path): void
    {
        if (!file_exists($path)) {
            throw new Exception(sprintf('Invalid font path "%s"', $path));
        }
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getSize(): int
    {
        return $this->size;
    }
}
