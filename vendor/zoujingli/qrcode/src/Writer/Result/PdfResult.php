<?php

declare(strict_types=1);

namespace Endroid\QrCode\Writer\Result;

use Endroid\QrCode\Matrix\MatrixInterface;
use FPDF;

final class PdfResult extends AbstractResult
{
    private $fpdf;

    public function __construct(MatrixInterface $matrix, FPDF $fpdf)
    {
        $this->fpdf = $fpdf;
        parent::__construct($matrix);
    }

    public function getPdf(): FPDF
    {
        return $this->fpdf;
    }

    public function getString(): string
    {
        return $this->fpdf->Output('S');
    }

    public function getMimeType(): string
    {
        return 'application/pdf';
    }
}
