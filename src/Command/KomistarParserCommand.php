<?php

namespace App\Command;

use App\Dictionary\ParamsDictionary;
use App\Entity\Product;
use App\Entity\ProductParam;
use App\Helper\ParserHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class KomistarParserCommand
 * @package App\Command
 */
class KomistarParserCommand extends Command
{
    private const SHOP = 'Komistar';
    private const CATALOG_LINK = 'https://komistar.in.ua/product_list/page_{page}?sort=&per_page=24#catalog_controls_block';
    private const PRODUCT_LINK_PATTERN = '/"cs-product-gallery__image-link".*href="(.*)"/Us';

    private const PARAMS = [
        'title' => [
            'pattern' => '/data-qaid="product_name">(.*)<\/span>/Us',
            'type' => ParamsDictionary::TYPE_STRING
        ],
        ParamsDictionary::PARAM_IMAGE => [
            'pattern' => '/src="([^\?<>]*)\?PIMAGE_ID=/Us',
            'type' => ParamsDictionary::TYPE_ARRAY,
        ],
        'price' => [
            'pattern' => '/"product_price">(.*)<\/span>/Us',
            'type' => ParamsDictionary::TYPE_INT
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


    public function __construct($name = 'parser:shop:komistar', EntityManagerInterface $entityManager)
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
            ->setDescription('Parser for "komistar.in.ua"')
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
        $html = ParserHelper::request($url);
        sleep(1);
        $links = $this->getProductLinks($html);
        $oldFirstLink = '';

        $this->output->writeln('Parsing catalog started');
        while ($links and $oldFirstLink != $links[0]) {
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
            $html = ParserHelper::request($url);
            sleep(1);
            $oldFirstLink = $links[0];
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
        $products = $this->entityManager->getRepository(Product::class)->getProducts(self::SHOP, self::LIMIT, $offset);

        $this->output->writeln('Parsing product started');
        while ($products) {
            $this->output->writeln(
                'Parsing products ' . ($offset) . '...' . ($offset + self::LIMIT - 1)
            );

            /** @var Product $product */
            foreach ($products as $product) {
                var_dump($product->getId());

                if (!$updateOldProducts && $product->getParams()->count() > 0) {
                    continue;
                }

                $params = ParserHelper::getProductParams($product->getLink(), self::PARAMS);
                $params = array_merge($params, $this->getAdditionalParams($product->getLink()));

                foreach ($params as $name => $value) {
                    /** @var ProductParam $productParam */
                    foreach ($product->getParams() as $productParam) {
                        if ($productParam->getName() == $name) {
                            continue;
                        }
                    }

                    $param = (new ProductParam())
                        ->setName($name)
                        ->setValue($value);

                    $product->addParam($param);
                }
            }

            $this->entityManager->flush();
            $this->entityManager->clear();

            $offset += self::LIMIT;
            $products = $this->entityManager->getRepository(Product::class)->getProducts(self::SHOP, self::LIMIT, $offset);
        }

        $this->output->writeln('Parsing product finished');
    }

    /**
     * @param string $link
     * @return array
     */
    private function getAdditionalParams(string $link): array
    {
        $html = ParserHelper::request($link);
        sleep(1);

        $parts = explode('product_description', $html);
        if (count($parts) == 1) {
            return [];
        }

        $parts2 = explode('</div>', $parts[1]);

        $parts3 = explode('<img', $parts2[0]);
        $result = '';
        foreach ($parts3 as $part) {
            $parts4 = explode('>', $part);
            unset($parts4[0]);

            $result .= implode('>', $parts4);
        }

        $result = trim(strip_tags($result));
        var_dump($result);
        return ['description' => $result];
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