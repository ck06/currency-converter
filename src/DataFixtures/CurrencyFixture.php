<?php

namespace App\DataFixtures;

use App\Entity\Currency;
use App\Service\Inverter;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CurrencyFixture extends Fixture
{
    private ObjectManager $manager;

    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;
        $this->createCurrencies();

        $manager->flush();
    }

    private function createCurrencies(): void
    {
        $data = self::currencyData();
        $max = count($data);
        for ($i = 1; $i <= $max; $i++) {
            $current = $data[$i - 1];
            $currency = $this->createCurrency(
                id: $i,
                code: $current['code'],
                name: $current['name'],
                rate: $current['rate'],
            );

            $this->manager->persist($currency);
        }
    }

    private function createCurrency(int $id, string $code, string $name, float $rate): Currency
    {
        return (new Currency())
            ->setNumericCode((string)$id)
            ->setAlphaCode($code)
            ->setCode($code)
            ->setName($name)
            ->setRate($rate)
            ->setInverseRate(Inverter::invert($rate))
            ->setDate(new DateTimeImmutable());
    }

    private static function currencyData(): array
    {
        return [
            [
                'code' => 'EUR',
                'name' => 'European Euro',
                'rate' => 1.0,
            ],
            [
                'code' => 'USD',
                'name' => 'American Dollar',
                'rate' => 2.0,
            ],
            [
                'code' => 'CAD',
                'name' => 'Canadian Dollar',
                'rate' => 4.5,
            ],
            [
                'code' => 'JPY',
                'name' => 'Japanese Yen',
                'rate' => 100,
            ],
            [
                'code' => 'KRW',
                'name' => 'Korean Won',
                'rate' => 1000,
            ],
            [
                'code' => 'PHP',
                'name' => 'PHP: Hypertext Preprocessor',
                'rate' => 0.333333333333,
            ],
        ];
    }
}
