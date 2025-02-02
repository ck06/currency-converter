<?php declare(strict_types=1);

namespace App\Service;

use RuntimeException;

class Inverter
{
    public static function invert(float $number): float
    {
        if ($number === 0.0) {
            throw new RuntimeException("It is impossible to determine an inverse for zero.");
        }

        if ($number === 1.0 || $number === -1.0) {
            // Without having to do any math, we know that a multiplication of 1 can only be inversed by itself.
            return $number;
        }

        return 1 / $number;
    }
}
