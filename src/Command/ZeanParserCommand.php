<?php

namespace App\Command;

use App\Dictionary\ParamsDictionary;
use App\Entity\OldProduct;
use App\Entity\ProductParam;
use App\Helper\ParserHelper;
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
    //private const CATALOG_LINK = 'https://www.zean.ua/vse-tovary/#/sort=p.sort_order/order=ASC/limit=10/page={page}';
    private const CATALOG_LINK = 'https://www.zean.ua/index.php?route=module/journal2_super_filter/products&module_id=5&filters=%2Fsort%3Dp.sort_order%2Forder%3DASC%2Flimit%3D10%2Fpage%3D{page}&oc_route=product%2Fcategory&path=243&manufacturer_id=&search=&tag=';
    private const PRODUCT_LINK_PATTERN = '/<h4 class="name"><a href="(https:\/\/www\.zean\.ua\/.*)">/Us';

    private const PARAMS = [
        'title' => [
            'pattern' => '/<h1 class="heading-title" itemprop="name">(.*)<\/h1>/Us',
            'type' => ParamsDictionary::TYPE_STRING
        ],
        ParamsDictionary::PARAM_IMAGE => [
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

        $this->parseCatalog();
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
                $product = $this->entityManager->getRepository(OldProduct::class)->findOneBy(
                    ['project' => self::SHOP, 'link' => $link]
                );

                if ($product) {
                    continue;
                }

                $product = (new OldProduct())
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
        $products = $this->entityManager->getRepository(OldProduct::class)->getProducts(self::LIMIT, $offset);

        $this->output->writeln('Parsing product started');
        while ($products) {
            $this->output->writeln(
                'Parsing products ' . ($offset) . '...' . ($offset + self::LIMIT - 1)
            );

            /** @var OldProduct $product */
            foreach ($products as $product) {
                if (!$updateOldProducts && $product->getParams()->count() > 0) {
                    continue;
                }

                $params = ParserHelper::getProductParams($product->getLink(), self::PARAMS);

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
            $products = $this->entityManager->getRepository(OldProduct::class)->getProducts(self::LIMIT, $offset);
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
}