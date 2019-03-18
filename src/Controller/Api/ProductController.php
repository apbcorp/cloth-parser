<?php

namespace App\Controller\Api;

use App\Entity\Product;
use App\Entity\Project;
use App\Interfaces\ParserServiceInterface;
use App\Services\ParserManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ProductController
 * @package App\Controller\Api
 */
class ProductController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ParserManager
     */
    private $manager;

    /**
     * ProductController constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param ParserManager          $manager
     */
    public function __construct(EntityManagerInterface $entityManager, ParserManager $manager)
    {
        $this->entityManager = $entityManager;
        $this->manager = $manager;
    }

    /**
     * @return JsonResponse
     */
    public function listAction(int $projectId, Request $request): JsonResponse
    {
        $productsData = $this->getProducts($projectId, $request);

        return new JsonResponse(['success' => true, 'result' => $productsData]);
    }

    /**
     * @param int     $projectId
     * @param Request $request
     *
     * @return array
     */
    private function getProducts(int $projectId, Request $request): array
    {
        try {
            $filter = [
                'project' => $this->entityManager->getReference(Project::class, $projectId),
                'limit' => $request->get('limit', 10),
                'page' => $request->get('page', 1),
                'prevId' => $request->get('prevId', 0)
            ];
        } catch (ORMException $e) {
            return [];
        }

        /** @var QueryBuilder $query */
        $query = $this->entityManager
            ->getRepository(Product::class)
            ->getProductsQuery($filter['limit'], $filter['page'], $filter['prevId']);

        $query->andWhere('p.project = :project')
            ->setParameter('project', $filter['project']);

        /** @var Product[] $products */
        $products = $query->getQuery()->getResult();

        $result = [];

        try {
            $service = $this->manager->getProjectServiceById($projectId);
        } catch (\Exception $e) {
            return [];
        }

        foreach ($products as $product) {
            $result[$product->getId()] = [
                'link' => $product->getLink(),
                'params' => $service->parseProduct($product->getCode())
            ];
        }

        return $result;
    }
}