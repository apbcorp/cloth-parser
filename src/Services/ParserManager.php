<?php

namespace App\Services;

use App\Entity\Project;
use App\Exceptions\UnknownEntityException;
use App\Exceptions\UnknownServiceException;
use App\Interfaces\ParserServiceInterface;
use App\Services\Parser\ZeanParserService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;

/**
 * Class ParserManager
 * @package App\Services
 */
class ParserManager
{
    /**
     * @var Project[]
     */
    private $projects = [];

    /**
     * @var ParserServiceInterface[]
     */
    private $parserServices = [];

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * ParserManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param ContainerInterface     $container
     */
    public function __construct(EntityManagerInterface $entityManager, ContainerInterface $container)
    {
        $this->entityManager = $entityManager;
        $this->container = $container;
    }

    /**
     * @param int $projectId
     *
     * @return Project
     * @throws UnknownEntityException
     */
    public function getProject(int $projectId): Project
    {
        if (!isset($this->projects[$projectId])) {
            $this->projects[$projectId] = $this->entityManager->getRepository(Project::class)->find($projectId);

            if (!$this->projects[$projectId]) {
                throw new UnknownEntityException();
            }
        }

        return $this->projects[$projectId];
    }

    /**
     * @param Project $project
     *
     * @return ParserServiceInterface
     * @throws UnknownServiceException
     */
    public function getProjectService(Project $project): ParserServiceInterface
    {
        if (!isset($this->parserServices[$project->getId()])) {
            if (!$this->container->has($project->getServiceName())) {
                throw new UnknownServiceException();
            }

            $this->parserServices[$project->getId()] = $this->container->get($project->getServiceName());
        }

        return $this->parserServices[$project->getId()];
    }

    /**
     * @param int $projectId
     *
     * @return ParserServiceInterface
     * @throws UnknownEntityException
     * @throws UnknownServiceException
     */
    public function getProjectServiceById(int $projectId): ParserServiceInterface
    {
        if (!isset($this->parserServices[$projectId])) {
            $project = $this->getProject($projectId);

            return $this->getProjectService($project);
        }

        return $this->parserServices[$projectId];
    }
}