<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ProductRepository
 * @package App\Repository
 */
class ProductRepository extends EntityRepository
{
    /**
     * @param int $limit
     * @param int $page
     * @param int $prevId
     *
     * @return QueryBuilder
     */
    public function getProductsQuery(int $limit, int $page, int $prevId = 0): QueryBuilder
    {
        $qb = $this->createQueryBuilder('p');

        $qb->where('p.id > :prevId')
            ->setParameter('prevId', $prevId)
            ->setMaxResults($limit)
            ->setFirstResult(($page - 1) * $limit);

        return $qb;
    }
}