<?php

declare(strict_types=1);

namespace Endroid\QrCode\Writer\Result;

use Endroid\QrCode\Matrix\MatrixInterface;

final class EpsResult extends AbstractResult
{
    private $lines;

    public function __construct(MatrixInterface $matrix, array $lines)
    {
        $this->lines = $lines;
        parent::__construct($matrix);
    }

    public function getString(): string
    {
        return implode("\n", $this->lines);
    }

    public function getMimeType(): string
    {
        return 'image/eps';
    }
}
