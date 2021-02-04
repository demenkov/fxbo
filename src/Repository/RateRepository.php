<?php

declare(strict_types=1);

namespace FXBO\Repository;

use DateTime;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Exchanger\Contract\CurrencyPair as CurrencyPairContract;
use FXBO\Entity\Rate;
use FXBO\DTO\RateListFilter;

final class RateRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry
    ) {
        parent::__construct($registry, Rate::class);
    }

    /**
     * @param array $rates
     * $rates = [
     *     [
     *         'date' => (string) Quote date,
     *         'base' => (string) Base currency,
     *         'quote' => (string) Quote currency,
     *         'price' => (string) Price,
     *         'provider' => (string) Provider,
     *     ]
     * ]
     */
    public function bulkUpsert(array $rates): int
    {
        $placeholders = [];
        $values = [];
        $types = [];

        foreach ($rates as $columnName => $value) {
            $placeholders[] = '(?)';
            $values[] = array_values($value);
            $types[] = Connection::PARAM_STR_ARRAY;
        }

        return $this->getEntityManager()->getConnection()->executeStatement(
            'INSERT INTO `rate` (`date`, `base`, `quote`, `price`, `provider`) 
VALUES ' . implode(', ', $placeholders) . ' ON DUPLICATE KEY UPDATE `provider`=`provider`',
            $values,
            $types
        );
    }

    public function getListQuery(
        RateListFilter $filter
    ): Query {
        $builder = $this->createQueryBuilder('r')
            ->orderBy("r.{$filter->getSort()}", $filter->getOrder());
        if (!is_null($filter->getFrom())) {
            $builder->andWhere('r.date >= :from');
            $builder->setParameter('from', $filter->getFrom());
        }
        if (!is_null($filter->getTo())) {
            $builder->andWhere('r.date <= :to');
            $builder->setParameter('to', $filter->getTo());
        }
        if (!is_null($filter->getProvider())) {
            $builder->andWhere('r.provider LIKE :provider');
            $builder->setParameter('provider', '%' . $filter->getProvider() . '%');
        }
        if (!is_null($filter->getBase())) {
            $builder->andWhere('r.base LIKE :base');
            $builder->setParameter('base', '%' . $filter->getBase() . '%');
        }
        if (!is_null($filter->getQuote())) {
            $builder->andWhere('r.quote LIKE :quote');
            $builder->setParameter('quote', '%' . $filter->getQuote() . '%');
        }
        return $builder->getQuery();
    }

    public function getRate(
        CurrencyPairContract $pair,
        DateTimeInterface $date = null
    ): ?Rate {
        if (is_null($date)) {
            $date = new DateTime('now');
        }
        $entityManager = $this->getEntityManager();

        $query = $entityManager
            ->createQuery(
                'SELECT r
                FROM ' . Rate::class . ' r
                WHERE r.date <= :date AND
                (r.base = :base AND r.quote = :quote) OR
                (r.base = :quote AND r.quote = :base)
                ORDER BY r.date DESC'
            )
            ->setParameter('date', $date->format('Y-m-d'))
            ->setParameter('base', $pair->getBaseCurrency())
            ->setParameter('quote', $pair->getQuoteCurrency());
        return $query->getResult() ? current($query->getResult()) : null;
    }
    /**
     * @return array
     * [
     *     ['USD', 'NZD'],
     *     ['BTC', 'USD'],
     * ]
     */
    public function getPairs(?string $date = null): array
    {
        $sql = <<<SQL
SELECT rate.base,
       rate.quote
FROM   rate,
       (SELECT base,
               quote,
               provider,
               MAX(date) AS date
        FROM   rate
        GROUP  BY base,
                  quote,
                  provider) latest
WHERE  rate.base = latest.base
       AND latest.quote = rate.quote
       AND rate.provider = latest.provider
       AND rate.date = latest.date
SQL;
        if (!is_null($date)) {
            $sql .= ' AND rate.date = :date';
        }
        $statement = $this->getEntityManager()->getConnection()->prepare($sql);
        if (!is_null($date)) {
            $statement->bindParam('date', $date);
        }
        $statement->execute();
        return $statement->fetchAllNumeric();
    }
}
