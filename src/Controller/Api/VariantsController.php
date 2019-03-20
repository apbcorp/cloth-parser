<?php

namespace App\Controller\Api;

use App\Entity\ParamVariants;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class VariantsController
 * @package App\Controller\Api
 */
class VariantsController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * VariantsController constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return JsonResponse
     */
    public function listAction(): JsonResponse
    {
        $repository = $this->entityManager->getRepository(ParamVariants::class);
        /** @var ParamVariants[] $params */
        $params = $repository->findAll();

        $result = [];
        foreach ($params as $param) {
            $result[$param->getParam()] = $param->getVariantsAsArray();
        }

        return new JsonResponse(['success' => true, 'result' => $result]);
    }
}