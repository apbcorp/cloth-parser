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
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ZeanParserCommand
 * @package App\Command
 */
class ZeanParserCommand extends Command
{
    private const SHOP = 'Zean';
    private const CATALOG_LINK = 'https://www.zean.ua/vse-tovary/#/sort=p.sort_order/order=ASC/limit=10/page={page}';
    private const PRODUCT_LINK_PATTERN = '/<h4 class="name"><a href="(https:\/\/www\.zean\.ua\/.*)">/Us';

    private const PARAMS = [
        'title' => [
            'pattern' => '/<h1 class="heading-title" itemprop="name">(.*)<\/h1>/Us',
            'type' => ParamsDictionary::TYPE_STRING
        ],
        'image' => [
            'pattern' => '/<a class="swiper-slide" style="" href="(.*)" title/Us',
            'type' => ParamsDictionary::TYPE_ARRAY
        ],
        'description' => [
            'pattern' => '/<div class="tab-pane tab-content active" id="tab-description">(.*)<\/div>/Us',
            'type' => ParamsDictionary::TYPE_STRING
        ],
        'model' => [
            'pattern' => '/<span class="p-model" itemprop="model">(.*)<\/span>/Us',
            'type' => ParamsDictionary::TYPE_STRING
        ],
        'priceOld' => [
            'pattern' => '/<li class="price-old">(.*)грн<\/li>/Us',
            'type' => ParamsDictionary::TYPE_INT
        ],
        'priceNew' => [
            'pattern' => '/<li class="price-new">(.*)грн<\/li>/Us',
            'type' => ParamsDictionary::TYPE_INT
        ],
        'size' => [
            'pattern' => '/<div class="checkbox">.*<input type="checkbox".*>(.*)<\/label>/Us',
            'type' => ParamsDictionary::TYPE_ARRAY
        ],
        'color' => [
            'pattern' => '/<div class="radio">.*<img.*>.*<img.*>(.*)<\/label>/Us',
            'type' => ParamsDictionary::TYPE_ARRAY
        ]
    ];

    private const LIMIT = 10;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;


    public function __construct($name = 'parser:shop:zean', EntityManagerInterface $entityManager)
    {
        parent::__construct($name);

        $this->entityManager = $entityManager;
    }

    /**
     *
     */
    protected function configure()
    {
        $this
            ->setDescription('Parser for "zean.ua"')
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

        $updateOldProducts = true;

        //$this->parseCatalog();
        $this->parseProducts($updateOldProducts);
    }

    /**
     *
     */
    private function parseCatalog()
    {
        $page = 1;

        $url = str_replace('{page}', $page, self::CATALOG_LINK);
        $html = file_get_contents($url);
        $links = $this->getProductLinks($html);

        $this->output->writeln('Parsing catalog started');
        while ($links) {
            $this->output->writeln('Parsing page #' . $page);
            foreach ($links as $link) {
                $product = $this->entityManager->getRepository(Product::class)->findOneBy(
                    ['project' => self::SHOP, 'link' => $link]
                );

                if ($product) {
                    continue;
                }

                $product = (new Product())
                    ->setProject(self::SHOP)
                    ->setLink($link);

                $this->entityManager->persist($product);
            }

            $this->entityManager->flush();
            $this->entityManager->clear();

            $page++;
            $url = str_replace('{page}', $page, self::CATALOG_LINK);
            $html = file_get_contents($url);
            $links = $this->getProductLinks($html);
        }

        $this->output->writeln('Parsing catalog finished');
    }

    /**
     * @param bool $updateOldProducts
     */
    private function parseProducts(bool $updateOldProducts)
    {
        $offset = 0;
        $products = $this->getProductsFromDB(self::LIMIT, $offset);

        $this->output->writeln('Parsing product started');
        while ($products) {
            $this->output->writeln(
                'Parsing products ' . ($offset * self::LIMIT) . '...' . ((($offset + 1) * self::LIMIT) - 1)
            );

            /** @var Product $product */
            foreach ($products as $product) {
                if (!$updateOldProducts && $product->getParams()->count() > 0) {
                    continue;
                }

                $params = $this->getProductParams($product->getLink());

                foreach ($params as $name => $value) {
                    $param = (new ProductParam())
                        ->setName($name)
                        ->setValue($value);

                    $product->addParam($param);
                }
            }

            $this->entityManager->flush();
            $this->entityManager->clear();

            $offset += self::LIMIT;
            $products = $this->getProductsFromDB(self::LIMIT, $offset);
        }

        $this->output->writeln('Parsing product finished');
    }

    /**
     * @param string $html
     *
     * @return array
     */
    private function getProductLinks(string $html): array
    {
        if (!preg_match_all(self::PRODUCT_LINK_PATTERN, $html, $matches)) {
            return [];
        }

        return $matches[1];
    }

    /**
     * @param string $link
     *
     * @return array
     */
    private function getProductParams(string $link): array
    {
        $html = file_get_contents($link);

        $result = [];
        foreach (self::PARAMS as $paramName => $paramInfo) {
            if (!preg_match_all($paramInfo['pattern'], $html, $matches)) {
                continue;
            }

            $result[$paramName] = $this->formatParamValue($matches[1], $paramInfo['type']);
        }

        return $result;
    }

    /**
     * @param array  $values
     * @param string $type
     *
     * @return string
     */
    private function formatParamValue(array $values, string $type): string
    {
        switch ($type) {
            case ParamsDictionary::TYPE_STRING:
                return trim($values[0]);
            case ParamsDictionary::TYPE_INT:
                $value = trim($values[0]);
                $value = (int) $value;

                return (string) $value;
            case ParamsDictionary::TYPE_ARRAY:
                $result = [];
                foreach ($values as $value) {
                    $result[] = trim($value);
                }

                return implode(', ', $result);
        }

        return '';
    }

    /**
     * @param int $limit
     * @param int $offset
     *
     * @return array
     */
    private function getProductsFromDB(int $limit, int $offset): array
    {
        /** @var EntityRepository $repository */
        $repository = $this->entityManager->getRepository(Product::class);
        $qb = $repository->createQueryBuilder('p');
        $qb->leftJoin(ProductParam::class, 'pp', Join::WITH, 'p.id = pp.product')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }
}