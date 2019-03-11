<?php

namespace App\Repository;

use App\Entity\ProductParam;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

/**
 * Class ProductRepository
 * @package App\Repository
 */
class ProductRepository extends EntityRepository
{
    /**
     * @param string $shop
     * @param int $limit
     * @param int $offset
     *
     * @return array
     */
    public function getProducts(string $shop, int $limit, int $offset): array
    {
        $qb = $this->createQueryBuilder('p');
        $qb->where('p.project = :shop')
            ->orderBy('p.id')
            ->setParameter('shop', $shop)
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }
}