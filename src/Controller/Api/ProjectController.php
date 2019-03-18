<?php

namespace App\Controller\Api;

use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class ProjectController
 * @package App\Controller\Api
 */
class ProjectController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * ProjectController constructor.
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
        /** @var Project[] $projects */
        $projects = $this->entityManager->getRepository(Project::class)->findAll();

        $result = [];

        foreach ($projects as $project) {
            $result[$project->getId()] = $project->getName();
        }

        return new JsonResponse(['success' => true, 'result' => $result]);
    }
}