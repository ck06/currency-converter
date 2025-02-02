<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Currency;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CurrencyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Currency::class);
    }

    public function findById(string|int $id): ?Currency
    {
        return $this
            ->createQueryBuilder('c')
            ->andWhere('c.numericCode = :id')
            ->setParameter('id', (string)$id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByCode(string $code, bool $isAlphaCode = false): ?Currency
    {
        return $this
            ->createQueryBuilder('c')
            ->andWhere(sprintf('c.%s = :code', $isAlphaCode ? 'alphaCode' : 'code'))
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
