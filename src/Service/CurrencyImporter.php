<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Currency;
use App\Repository\CurrencyRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;

class CurrencyImporter
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function importAll(array $data): void
    {
        /** @var CurrencyRepository $currencyRepository */
        $currencyRepository = $this->em->getRepository(Currency::class);
        foreach (array_chunk($data, 50) as $chunk) {
            foreach ($chunk as $currencyData) {
                $entity = $currencyRepository->findById($currencyData['numericCode']);
                if ($entity !== null) {
                    $this->updateEntity($entity, $currencyData);
                } else {
                    $entity = $this->createEntity($currencyData);
                }

                $this->em->persist($entity);
            }

            // finalize changes in batches for performance reasons
            $this->em->flush();
            $this->em->clear();
        }
    }

    public function importOne(array $data, string $target): void
    {
        if (!($currencyData = $data[strtolower($target)] ?? null)) {
            throw new InvalidArgumentException(sprintf('Unable to find target currency %s in feed', $target));
        }

        /** @var CurrencyRepository $currencyRepository */
        $currencyRepository = $this->em->getRepository(Currency::class);
        $entity = $currencyRepository->findByCode($target);
        if ($entity === null) {
            $entity = $this->createEntity($currencyData);
        } else {
            $this->updateEntity($entity, $currencyData);
        }

        $this->em->persist($entity);
        $this->em->flush();
        $this->em->clear();
    }

    public function importDefault(array $data, string $codeForDefault): void
    {
        $this->importOne($data, $codeForDefault);

        /** @var CurrencyRepository $currencyRepository */
        $currencyRepository = $this->em->getRepository(Currency::class);

        /**
         * we would've gotten an exception earlier if it did not exist, so no need to re-check
         * @var Currency $entity
         */
        $entity = $currencyRepository->findByCode($codeForDefault);
        $entity
            ->setRate(1)
            ->setInverseRate(1)
            ->setDate(new DateTimeImmutable());

        $this->em->persist($entity);
        $this->em->flush();
    }

    private function createEntity(array $currencyData): Currency
    {
        $entity = (new Currency())
            ->setName($currencyData['name'])
            ->setCode($currencyData['code'])
            ->setAlphaCode($currencyData['alphaCode'])
            ->setNumericCode($currencyData['numericCode']);

        $this->updateEntity($entity, $currencyData);

        return $entity;
    }

    private function updateEntity(Currency $entity, array $currencyData): void
    {
        $date = new DateTimeImmutable($currencyData['date']);
        $entity
            ->setInverseRate($currencyData['inverseRate'])
            ->setRate($currencyData['rate'])
            ->setDate($date);
    }
}
