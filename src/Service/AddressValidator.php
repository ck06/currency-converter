<?php

declare(strict_types=1);

namespace App\Service;

use InvalidArgumentException;

class AddressValidator
{
    public function validate(string $input): string
    {
        $input = trim($input);
        if (!$input) {
            return '';
        }

        $ip = explode('/', $input, 2)[0];
        $ipChunks = explode('.', $ip, 5);
        if (count($ipChunks) > 4) {
            throw new InvalidArgumentException("Invalid IPv4 address given.");
        }

        // allow entry of empty chunks by filling them with 0
        // i.e. "127...1" -> "127.0.0.1"
        $ipChunks = array_map(static fn(string $chunk) => $chunk === '' ? '0' : $chunk, $ipChunks);

        $filteredChunks = [];
        $cidr = $this->determineCidr($input);
        for ($i = 0; $i < $cidr; $i += 8) {
            $filteredChunks[] = $ipChunks[$i / 8];
        }

        return implode('.', $filteredChunks);
    }

    public function determineCidr(string $input): int
    {
        $cidrChunks = explode('/', $input, 2);
        if (count($cidrChunks) === 2) {
            $supportedCidrs = [8, 16, 24, 32];
            $cidr = (int)$cidrChunks[1];
            if (in_array($cidr, $supportedCidrs)) {
                return (int)$cidrChunks[1];
            }
        }

        return 8 * count(explode('.', $input, 4));
    }
}
