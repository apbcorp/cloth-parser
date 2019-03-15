<?php

namespace App\Controller\Api;

use App\Entity\Product;
use App\Entity\Project;
use App\Interfaces\ParserServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
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
     * @var ContainerInterface
     */
    private $container;

    /**
     * ProjectController constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager, ContainerInterface $container)
    {
        $this->entityManager = $entityManager;
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
                'prevId' => $request->get('prevId', 1)
            ];
        } catch (ORMException $e) {
            return [];
        }

        /** @var Project $project */
        $project = $this->entityManager->getRepository(Project::class)->find($projectId);
        /** @var Product[] $products */
        $products = $this->entityManager->getRepository(Product::class)->findBy($filter);

        $result = [];
        /** @var ParserServiceInterface $service */
        $service = $this->container->get($project->getService());
        foreach ($products as $product) {
            $result[$product->getId()] = [
                'link' => $product->getLink(),
                'params' => $service->parseProduct($product->getCode())
            ];
        }

        return $result;
    }
}