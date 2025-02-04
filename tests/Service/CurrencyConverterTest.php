<?php

namespace Service;

use App\Dto\CurrencyDto;
use App\Entity\Currency;
use App\Repository\CurrencyRepository;
use App\Service\CurrencyConverter;
use App\Service\Inverter;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CurrencyConverterTest extends TestCase
{
    private MockObject&EntityManagerInterface $em;
    private MockObject&CurrencyRepository $currencyRepository;

    public function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->currencyRepository = $this->createMock(CurrencyRepository::class);

        $this->em->method('getRepository')->willReturn($this->currencyRepository);
    }

    public function testFetchesEntityIfIdIsPassed(): void
    {
        $this
            ->em
            ->expects($this->once())
            ->method('getRepository')
            ->with(Currency::class)
            ->willReturn($this->currencyRepository);

        $entity = $this->createCurrency(id: 1, rate: 1);
        $this->currencyRepository->method('findById')->with(1)->willReturn($entity);

        $converter = new CurrencyConverter($this->em);

        $expected = new CurrencyDto($entity->getCode(), 12);
        $actual = $converter->convertOne(1, $entity, 12);

        $this->assertEquals($expected, $actual);
    }

    public function testConvertOne(): void
    {
        $amount = 12;
        $from = $this->createCurrency(id: 1, rate: 1);
        $to = $this->createCurrency(id: 2, rate: 2);

        $expected = new CurrencyDto($to->getCode(), $amount * $to->getRate());

        $converter = new CurrencyConverter($this->em);
        $actual = $converter->convertOne($from, $to, $amount);

        $this->assertEquals($expected, $actual);
    }

    public function testConvertAll(): void
    {
        $from = $this->createCurrency(id: 1, code: 'test1', name: 'test1', rate: 1);
        $to = [
            $this->createCurrency(id: 1, code: 'test1', name: 'test1', rate: 1),
            $this->createCurrency(id: 2, code: 'test2', name: 'test2', rate: 2),
            $this->createCurrency(id: 3, code: 'test3', name: 'test3', rate: 3),
        ];

        $this->currencyRepository->method('findAll')->willReturn($to);

        $amount = 12;
        $expected = array_map(static function ($t) use ($amount) {
            return new CurrencyDto($t->getCode(), $amount * $t->getRate());
        }, $to);

        $converter = new CurrencyConverter($this->em);
        $actual = $converter->convertAll($from, $amount);

        $this->assertEquals($expected, $actual);
    }

    private function createCurrency(
        string $id,
        ?string $code = 'TEST',
        ?string $name = 'test currency',
        ?float $rate = 1.0,
    ): Currency {
        return new Currency()
            ->setNumericCode($id)
            ->setAlphaCode($code)
            ->setCode($code)
            ->setName($name)
            ->setRate($rate)
            ->setInverseRate(Inverter::invert($rate))
            ->setDate(new DateTimeImmutable());
    }
}
