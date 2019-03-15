<?php

namespace App\Repository;

use App\Entity\ProductParam;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

/**
 * Class ProductRepository
 * @package App\Repository
 */
class OldProductRepository extends EntityRepository
{
    /**
     * @param int $limit
     * @param int $offset
     *
     * @return array
     */
    public function getProducts(int $limit, int $offset): array
    {var_dump($limit, $offset);
        $qb = $this->createQueryBuilder('p');
        $qb->leftJoin(ProductParam::class, 'pp', Join::WITH, 'p.id = pp.product')
            ->orderBy('p.id')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }
}