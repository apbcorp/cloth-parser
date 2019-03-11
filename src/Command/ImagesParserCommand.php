<?php

namespace App\Command;

use App\Dictionary\ParamsDictionary;
use App\Entity\Product;
use App\Entity\ProductParam;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ImagesParserCommand
 * @package App\Command
 */
class ImagesParserCommand extends Command
{
    const TARGET_DIR = 'E:\xampp\images';
    const LIMIT = 10;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * @var OutputInterface
     */
    private $output;


    public function __construct($name = 'parser:images:download', EntityManagerInterface $entityManager)
    {
        parent::__construct($name);

        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(ProductParam::class);
    }

    /**
     *
     */
    protected function configure()
    {
        $this
            ->setDescription('Parse images')
            ->addArgument('project', InputOption::VALUE_REQUIRED);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $project = $input->getArgument('project');

        $offest = 0;
        $images = $this->getImagesData($project, self::LIMIT, $offest);
        $count = 0;

        while ($images) {
            foreach ($images as $image) {
                $this->output->writeln('Parse product #' . ($count + 1));
                $imageList = explode(',', $image['images']);
                $imageList = array_map(
                    function ($item) {
                        return trim($item);
                    },
                    $imageList
                );
                $this->downloadImages($image['id'], $image['project'], $imageList);
                $count++;
            }

            $this->entityManager->clear();
            $offest += self::LIMIT;
            $images = $this->getImagesData(self::LIMIT, $offest);
        }
    }

    /**
     * @param string $project
     * @param int $limit
     * @param int $offset
     * @return array
     */
    private function getImagesData(string $project, int $limit, int $offset): array
    {
        $qb = $this->repository->createQueryBuilder('pp');
        $qb->select('pp.value as images, p.project, p.id')
            ->leftJoin(Product::class, 'p', Join::WITH, 'pp.product = p.id')
            ->where('pp.name = :param')
            ->andWhere('p.project = :project')
            ->orderBy('pp.id')
            ->setParameter('param', ParamsDictionary::PARAM_IMAGE)
            ->setParameter('project', $project)
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param int $id
     * @param string $project
     * @param array $imageList
     */
    private function downloadImages(int $id, string $project, array $imageList)
    {
        foreach ($imageList as $image) {
            if (!$image) {
                continue;
            }

            $imageParts = explode('/', $image);
            $imageName = end($imageParts);
            $filename = $id . '-' . $imageName;

            $dir = self::TARGET_DIR . DIRECTORY_SEPARATOR . $project;

            if (!file_exists($dir)) {
                mkdir($dir);
            }

            $path = $dir . DIRECTORY_SEPARATOR . $filename;

            if (file_exists($path)) {
                continue;
            }

            file_put_contents($path, file_get_contents($image));
            sleep(1);
        }

        $this->output->writeln('Parse ' . count($imageList) . ' images');
    }
}