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
        $products = $this->entityManager->getRepository(Product::class)->getProducts(self::LIMIT, $offset);

        $this->output->writeln('Parsing product started');
        while ($products) {
            $this->output->writeln(
                'Parsing products ' . ($offset) . '...' . ($offset + self::LIMIT - 1)
            );

            /** @var Product $product */
            foreach ($products as $product) {
                if (!$updateOldProducts && $product->getParams()->count() > 0) {
                    continue;
                }

                $params = ParserHelper::getProductParams($product->getLink(), self::PARAMS);
                $params = array_merge($params, $this->getAdditionalParams($product->getLink()));

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
            $products = $this->entityManager->getRepository(Product::class)->getProducts(self::LIMIT, $offset);
        }

        $this->output->writeln('Parsing product finished');
    }

    private function getAdditionalParams(string $link)
    {
        $html = file_get_contents($link);

        if (!preg_match('/product_description">(.*)<\/div>/Us', $html, $matches)) {
            return [];
        }

        $code = $matches[1];

        if (preg_match_all('/<img.*>/Us', $html, $matches)) {
            foreach ($matches as $match) {
                $code = str_replace($match, '', $code);
                $code = str_replace('<p></p>', '', $code);
                $code = str_replace("\r", '', $code);
                $code = str_replace("\n", '', $code);
            }
        }

        $parts = explode('>', $code);

        $params = ['description' => ''];
        foreach ($parts as $part) {
            if (!$part) {
                continue;
            }
            $part = strip_tags($part);
            if (strpos($part, '•') !== false) {
                continue;
            }

            if (strpos($part, '-') === false) {
                continue;
            } else {
                $parts2 = explode(' ', $part);
                $key = mb_strtolower(trim(str_replace('🔹', '', $parts2[0])));
                if (!$key || $key == 'застёжка' || $key == 'застежка') {
                    $params['description'] = str_replace('🔹', '', $part);

                    continue;
                }
                $parts2 = explode('-', $part);
                $params[$key] = trim($parts2[1]);
            }
        }

        if (strlen($params['description']) < 20) {
            unset($params['description']);
        } else {
            $params['description'] = trim($params['description']);
        }

        return $params;
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