<?php

namespace App\Repository;

use App\Entity\ApprovedProductParams;
use App\Entity\Product;
use App\Entity\Project;
use App\Services\ParserManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ProductRepository
 * @package App\Repository
 */
class ProductRepository extends EntityRepository
{
    private const DEFAULT_LIMIT = 3;

    /**
     * @param int           $projectId
     * @param Request       $request
     * @param ParserManager $manager
     *
     * @return array
     */
    public function getProducts(int $projectId, Request $request, ParserManager $manager): array
    {
        $filter = $this->getFilter($projectId, $request);

        if (!$filter) {
            return [];
        }

        $qb = $this->getProductsQuery($filter);

        if (!$qb) {
            return [];
        }

        /** @var Product[] $products */
        $products = $qb->getQuery()->getResult();

        $result = [];

        try {
            $service = $manager->getProjectServiceById($projectId);
        } catch (\Exception $e) {
            return [];
        }

        foreach ($products as $product) {
            $result[$product->getId()] = [
                'id' => $product->getId(),
                'link' => $product->getLink(),
                'params' => $filter['status'] == Product::STATUS_APPROVE
                    ? $service->getProduct($product->getId())
                    : $service->parseProduct($product->getCode())
            ];
        }

        return $result;
    }

    /**
     * @param int     $projectId
     * @param Request $request
     *
     * @return int
     */
    public function getFirstId(int $projectId, Request $request): int
    {
        $filter = $this->getFilter($projectId, $request);

        if (!$filter) {
            return 0;
        }

        $qb = $this->getProductsQuery($filter);

        if (!$qb) {
            return 0;
        }

        $qb->setParameter('prevId', 0)
            ->setMaxResults(1);
        /** @var Product[] $products */
        $products = $qb->getQuery()->getResult();

        return $products ? $products[0]->getId() : 0;
    }

    /**
     * @param int     $projectId
     * @param Request $request
     *
     * @return int
     */
    public function getLastId(int $projectId, Request $request): int
    {
        $filter = $this->getFilter($projectId, $request);

        if (!$filter) {
            return 0;
        }

        $qb = $this->getProductsQuery($filter);

        if (!$qb) {
            return 0;
        }

        $qb->setParameter('prevId', 0)
            ->orderBy('p.id', 'desc')
            ->setMaxResults(1);
        /** @var Product[] $products */
        $products = $qb->getQuery()->getResult();

        return $products ? $products[0]->getId() : 0;
    }

    /**
     * @param int     $projectId
     * @param Request $request
     * @param int     $maxId
     *
     * @return int|null
     */
    public function getPrevPageProductId(int $projectId, Request $request, int $maxId): ?int
    {
        $filter = $this->getFilter($projectId, $request);
        $limit = $filter['limit'] + 1;
        $filter['limit'] = $limit;

        if (!$filter) {
            return 0;
        }

        $qb = $this->getProductsQuery($filter);

        if (!$qb) {
            return 0;
        }

        $qb->andWhere('p.id < :maxId')
            ->setParameter('prevId', 0)
            ->setParameter('maxId', $maxId)
            ->orderBy('p.id', 'desc');

        /** @var Product[] $products */
        $products = $qb->getQuery()->getResult();

        return count($products) < $limit
            ? 0
            : $products[count($products) - 1]->getId();
    }

    /**
     * @param array $filter
     *
     * @return QueryBuilder
     */
    private function getProductsQuery(array $filter): QueryBuilder
    {
        $qb = $this->createQueryBuilder('p');

        $qb->where('p.id > :prevId')
            ->andWhere('p.project = :project AND p.status = :status')
            ->setParameter('prevId', $filter['prevId'])
            ->setParameter('project', $filter['project'])
            ->setParameter('status', $filter['status'])
            ->setMaxResults($filter['limit'])
            ->setFirstResult(($filter['page'] - 1) * $filter['limit']);

        return $qb;
    }

    /**
     * @param int     $projectId
     * @param Request $request
     *
     * @return array
     */
    private function getFilter(int $projectId, Request $request): array
    {
        try {
            return [
                'project' => $this->getEntityManager()->getReference(Project::class, $projectId),
                'limit'   => $request->get('limit', self::DEFAULT_LIMIT),
                'page'    => $request->get('page', 1),
                'prevId'  => $request->get('prevId', 0),
                'status'  => $request->get('status', Product::STATUS_NEW),
            ];
        } catch (ORMException $e) {
            return [];
        }
    }
}