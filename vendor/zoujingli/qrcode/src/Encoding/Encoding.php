<?php

declare(strict_types=1);

namespace Endroid\QrCode\Encoding;

use Exception;

final class Encoding implements EncodingInterface
{
    private $value;

    public function __construct(string $value)
    {
        $this->value = $value;
        if (!in_array($value, mb_list_encodings())) {
            throw new Exception(sprintf('Invalid encoding "%s"', $value));
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
