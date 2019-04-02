<?php

namespace App\Controller\Api;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Services\ParserManager;
use App\Services\Processor\ProductProcessor;
use Doctrine\ORM\EntityManagerInterface;
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
        /** @var ProductRepository $repository */
        $repository = $this->entityManager->getRepository(Product::class);
        $productsData = $repository->getProducts($projectId, $request, $this->manager);
        if ($productsData) {
            $firstProduct = reset($productsData);
            $prevPageProductId = $repository->getPrevPageProductId($projectId, $request, $firstProduct['id']);
        } else {
            $prevPageProductId = 0;
        }

        return new JsonResponse(
            [
                'success' => true,
                'result' => $productsData,
                'firstId' => $repository->getFirstId($projectId, $request),
                'lastId' => $repository->getLastId($projectId, $request),
                'prevPageProductId' => $prevPageProductId
            ]
        );
    }

    /**
     * @param int $projectId
     * @param int $productId
     * @param int $status
     *
     * @return JsonResponse
     */
    public function changeStatusAction(int $projectId, int $productId, int $status): JsonResponse
    {
        $this->processor->upsertProduct($productId, ['status' => $status]);

        return new JsonResponse(['success' => true, 'result' => ['id' => $productId]]);
    }

    /**
     * @param int     $projectId
     * @param int     $productId
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function approveAction(int $projectId, int $productId, Request $request): JsonResponse
    {
        $data = [
            'status' => Product::STATUS_APPROVE,
            'params' => $request->request->all()
        ];

        $this->processor->upsertProduct($productId, $data);

        return new JsonResponse(['success' => true, 'result' => ['id' => $productId]]);
    }
}