<?php

namespace App\Command;

use App\Entity\Product;
use App\Entity\Project;
use App\Exceptions\UnknownEntityException;
use App\Exceptions\UnknownServiceException;
use App\Interfaces\ParserServiceInterface;
use App\Services\ParserManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ShopParseCommand
 * @package App\Command
 */
class ShopParseCommand extends Command
{
    const CHUNK_SIZE = 20;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ParserServiceInterface
     */
    private $service;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var Project
     */
    private $project;

    /**
     * @var bool
     */
    private $updateMode;

    /**
     * @var ParserManager
     */
    private $parserManager;

    /**
     * ShopParseCommand constructor.
     *
     * @param string                 $name
     * @param EntityManagerInterface $entityManager
     * @param ParserManager          $manager
     */
    public function __construct($name = 'parser:shop:parse', EntityManagerInterface $entityManager, ParserManager $manager)
    {
        parent::__construct($name);

        $this->entityManager = $entityManager;
        $this->parserManager = $manager;
    }

    /**
     *
     */
    protected function configure()
    {
        $this
            ->addArgument('projectId', InputArgument::REQUIRED, 'Project ID', null)
            ->addArgument('updateMode', InputArgument::OPTIONAL, 'Update old products', false)
            ->setDescription('Universal parser for shops')
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $projectId = $input->getArgument('projectId');
        $this->updateMode = (bool) $input->getArgument('updateMode');

        if (!$projectId) {
            $this->output->writeln('Unknown project with id ' . $projectId);

            return;
        }

        try {
            $this->service = $this->parserManager->getProjectServiceById($projectId);
            $this->project = $this->parserManager->getProject($projectId);
        } catch (UnknownServiceException $e) {
            $this->output->writeln('Project not contain service');

            return;
        } catch (UnknownEntityException $e) {
            $this->output->writeln('Unknown project with id ' . $projectId);

            return;
        }

        $this->parsePages();
        $this->parseProducts();
    }

    /**
     *
     */
    private function parsePages()
    {
        $page = 1;
        $html = $this->getHtml($this->service->getCatalogPageUrl($page));
        $productLinks = $this->service->getProductLinks($html);
        $repository = $this->entityManager->getRepository(Product::class);

        while ($productLinks) {
            $this->output->writeln('Parse catalog page #' . $page);

            foreach ($productLinks as $link) {
                if ($repository->findBy(['link' => $link])) {
                    continue;
                }

                $entity = (new Product())
                    ->setProject($this->project)
                    ->setLink($link);

                $this->entityManager->persist($entity);
            }

            $this->entityManager->flush();
            $this->entityManager->clear();

            $page++;
            $html = $this->getHtml($this->service->getCatalogPageUrl($page));
            $productLinks = $this->service->getProductLinks($html);
        }
    }

    /**
     *
     */
    private function parseProducts()
    {
        $params = [
            'project' => $this->project,
        ];

        if ($this->updateMode) {
            $params['code'] = '';
        }

        $repository = $this->entityManager->getRepository(Product::class);

        $offset = 0;
        /** @var Product[] $products */
        $products = $repository->findBy($params, ['id' => 'ASC'], self::CHUNK_SIZE, $offset);

        while ($products) {
            $this->output->writeln('Parse products ' . $offset . '...' . ($offset + self::CHUNK_SIZE));

            foreach ($products as $product) {
                $product->setCode($this->getHtml($product->getLink()));
            }

            $this->entityManager->flush();
            $this->entityManager->clear();

            $offset += self::CHUNK_SIZE;
            $products = $repository->findBy($params, ['id' => 'ASC'], self::CHUNK_SIZE, $offset);
        }
    }

    /**
     * @param string $url
     *
     * @return string
     */
    private function getHtml(string $url): string
    {
        sleep(1);

        try {
            return file_get_contents($url);
        } catch (\Exception $e) {
            return '';
        }
    }
}