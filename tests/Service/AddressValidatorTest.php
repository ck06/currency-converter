<?php

namespace Service;

use App\Dto\CurrencyDto;
use App\Entity\Currency;
use App\Repository\CurrencyRepository;
use App\Service\AddressValidator;
use App\Service\CurrencyConverter;
use App\Service\Inverter;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AddressValidatorTest extends TestCase
{
    /** @dataProvider ipAddresses */
    public function testValidator(string $address, int $expectedCidr, ?string $expectedAddress = null): void
    {
        if ($expectedAddress === null) {
            $expectedAddress = $address;
        }

        $validator = new AddressValidator();

        $this->assertSame($expectedCidr, $validator->determineCidr($address));
        $this->assertSame($expectedAddress, $validator->validate($address));
    }

    public function ipAddresses(): array
    {
        return [
            ['127.0.0.1', 32],
            ['127.0.0', 24],
            ['127.0', 16],
            ['127', 8],
            ['127.0.0.1/24', 24, '127.0.0'],
            ['127.0.0.1/16', 16, '127.0'],
            ['127.0.0.1/8', 8, '127'],
        ];
    }
}
