<?php

namespace App\Controller\Api;

use App\Entity\Product;
use App\Entity\Project;
use App\Services\ParserManager;
use App\Services\Processor\ProductProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
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
     * @var ProductProcessor
     */
    private $processor;

    /**
     * ProductController constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param ParserManager          $manager
     * @param ProductProcessor       $processor
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ParserManager $manager,
        ProductProcessor $processor
    ) {
        $this->entityManager = $entityManager;
        $this->manager = $manager;
        $this->processor = $processor;
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
     * @param int $productId
     * @param int $status
     *
     * @return JsonResponse
     */
    public function changeStatusAction(int $productId, int $status): JsonResponse
    {
        $this->processor->updateProduct($productId, ['status' => $status]);

        return new JsonResponse(['success' => true]);
    }

    /**
     * @param int     $productId
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function approveAction(int $productId, Request $request): JsonResponse
    {
        $this->processor->updateProduct($productId, $request->request->all());

        return new JsonResponse(['success' => true]);
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
                'prevId' => $request->get('prevId', 0),
                'status' => $request->get('status', Product::STATUS_NEW)
            ];
        } catch (ORMException $e) {
            return [];
        }

        /** @var QueryBuilder $query */
        $query = $this->entityManager
            ->getRepository(Product::class)
            ->getProductsQuery($filter['limit'], $filter['page'], $filter['prevId']);

        $query->andWhere('p.project = :project AND p.status = :status')
            ->setParameter('project', $filter['project'])
            ->setParameter('status', $filter['status']);

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
                'id' => $product->getId(),
                'link' => $product->getLink(),
                'params' => $service->parseProduct($product->getCode())
            ];
        }

        return $result;
    }
}