<?php

declare(strict_types=1);

namespace App\Dto;

class CurrencyDto
{
    public function __construct(
        private string $code,
        private float $value,
    ) {
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getValue(): float
    {
        return $this->value;
    }
}
