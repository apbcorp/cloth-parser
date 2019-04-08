<?php

namespace App\Command;

use App\Services\Parser\Instagram\AbstractInstagramParserService;
use Symfony\Component\Console\Command\Command;
use App\Entity\Product;
use App\Entity\Project;
use App\Exceptions\UnknownEntityException;
use App\Exceptions\UnknownServiceException;
use App\Services\ParserManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class InstagramParseCommand
 * @package App\Command
 */
class InstagramParseCommand extends Command
{
    const CHUNK_SIZE = 20;
    const DEFAULT_INSTAGRAM_PATH = 'https://www.instagram.com/p/{id}/';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var AbstractInstagramParserService
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
    public function __construct($name = 'parser:instagram:parse', EntityManagerInterface $entityManager, ParserManager $manager)
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
            ->setDescription('Universal parser for instagram')
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
        $html = $this->getHtml($this->service->getInitPage());
        $hashFile = $this->service->getHashFile($html);
        $hashFileHtml = $this->getHtml($hashFile);
        $this->service->getHash($hashFileHtml);

        $page = 1;
        $cursor = '';
        $html = $this->getHtml($this->service->getCatalogPageUrl($cursor));
        $ids = $this->service->getProductLinks($html);
        $repository = $this->entityManager->getRepository(Product::class);
        $hasNextPage = true;

        while ($hasNextPage) {var_dump($ids);
            $this->output->writeln('Parse catalog page #' . $page);

            foreach ($ids as $id) {
                $link = str_replace('{id}', $id, self::DEFAULT_INSTAGRAM_PATH);
                /*if ($repository->findBy(['link' => $link])) {
                    continue;
                }

                $entity = (new Product())
                    ->setProject($this->project)
                    ->setLink($link);

                $this->entityManager->persist($entity);*/
            }

            $this->entityManager->flush();
            $this->entityManager->clear();

            $hasNextPage = $this->service->hasNextPage($html);

            if ($hasNextPage) {
                $page++;
                $cursor = $this->service->getCursor($html);
                var_dump($this->service->getApiLink($cursor));
                $html = $this->getHtml($this->service->getApiLink($cursor));var_dump($html);
                $ids = $this->service->getProductLinks($html);var_dump($ids);
            }
            exit();
        }
    }

    /**
     *
     */
    private function parseProducts()
    {
        $params = [
            'projectId' => $this->project->getId(),
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

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_COOKIESESSION, true);
        curl_setopt($curl, CURLOPT_COOKIEJAR, 'instagramCookie.txt');
        curl_setopt($curl, CURLOPT_COOKIEFILE, 'instagramCookie.txt');
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/73.0.3683.86 Safari/537.36',
            /*'X-IG-App-ID: 936619743392459',
            'X-Instagram-GIS: 069b67e589857f62e5ce57bfa8d005af',
            'X-Requested-With: XMLHttpRequest'*/
        ]);

        try {
            $data = curl_exec($curl);
            var_dump(curl_getinfo($curl));
            curl_close($curl);

            return $data;
        } catch (\Exception $e) {
            return '';
        }
    }
}