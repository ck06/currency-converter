<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\CurrencyDto;
use App\Entity\Currency;
use App\Repository\CurrencyRepository;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;

class CurrencyConverter
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function convertOne(int|string|Currency $from, int|string|Currency $to, float $amount): CurrencyDto
    {
        $from = $this->getEntity($from);
        $to = $this->getEntity($to);

        $convertedAmount = $amount * $from->getInverseRate() * $to->getRate();

        return new CurrencyDto($to->getCode(), $convertedAmount);
    }

    /**
     * @return array<CurrencyDto>
     */
    public function convertAll(int|string|Currency $from, float $amount): array
    {
        $from = $this->getEntity($from);

        /** @var array<Currency> $currencies */
        $currencies = $this->em->getRepository(Currency::class)->findAll();

        return array_map(
            static function (Currency $to) use ($amount, $from) {
                $convertedAmount = $amount * $from->getInverseRate() * $to->getRate();

                return new CurrencyDto($to->getCode(), $convertedAmount);
            },
            $currencies,
        );
    }

    private function getEntity(int|string|Currency $id): Currency
    {
        if ($id instanceof Currency) {
            return $id;
        }

        /** @var CurrencyRepository $repository */
        $repository = $this->em->getRepository(Currency::class);
        $entity = $repository->findById($id);
        if (!$entity instanceof Currency) {
            throw new InvalidArgumentException(sprintf('No available data for currency with id %s', $from));
        }

        return $entity;
    }
}
