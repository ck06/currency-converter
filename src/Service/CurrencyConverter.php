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

    /**
     * @return array<CurrencyDto>
     */
    public function convert(string|Currency $from, float $amount): array
    {
        /** @var CurrencyRepository $repository */
        $repository = $this->em->getRepository(Currency::class);
        $fromEntity = $from instanceof Currency ? $from : $repository->findByCode($from);
        if (!$fromEntity instanceof Currency) {
            throw new InvalidArgumentException(sprintf('No available data for currency with code %s', $from));
        }

        /** @var array<Currency> $currencies */
        $currencies = $repository->findAll();

        return array_map(
            static function (Currency $toEntity) use ($amount, $fromEntity) {
                $convertedAmount = $amount * $fromEntity->getInverseRate() * $toEntity->getRate();

                return new CurrencyDto($toEntity->getCode(), $convertedAmount);
            },
            $currencies
        );
    }
}
