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
        foreach (self::currencyData() as $current) {
            $currency = $this->createCurrency(
                id: $current['id'],
                code: $current['code'],
                name: $current['name'],
                rate: $current['rate'],
            );

            $this->manager->persist($currency);
        }
    }

    private function createCurrency(string $id, string $code, string $name, float $rate): Currency
    {
        return (new Currency())
            ->setNumericCode($id)
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
                'id' => '978',
                'code' => 'EUR',
                'name' => 'European Euro',
                'rate' => 1.0,
            ],
            [
                'id' => '840',
                'code' => 'USD',
                'name' => 'American Dollar',
                'rate' => 2.0,
            ],
            [
                'id' => '124',
                'code' => 'CAD',
                'name' => 'Canadian Dollar',
                'rate' => 4.5,
            ],
            [
                'id' => '392',
                'code' => 'JPY',
                'name' => 'Japanese Yen',
                'rate' => 100,
            ],
            [
                'id' => '410',
                'code' => 'KRW',
                'name' => 'Korean Won',
                'rate' => 1000,
            ],
            [
                'id' => '608',
                'code' => 'PHP',
                'name' => 'PHP: Hypertext Preprocessor',
                'rate' => 0.333333333333,
            ],
        ];
    }
}
