<?php

namespace App\Command;

use App\Dictionary\ParamsDictionary;
use App\Entity\RealProduct;
use App\Entity\RealProductParam;
use App\Helper\TranslitHelper;
use Symfony\Component\Console\Command\Command;
use App\Entity\ProductParam;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BuildExportFileCommand extends Command
{
    const TARGET_DIR = 'E:\xampp\igor\exports\images';
    const FILES_DIR = 'E:\xampp\igor\exports';
    const LIMIT = 10;

    const DATA = [
        [
            'file' => 'products.csv',
            /*'header' => 'product_id;name(ru-ru);categories;sku;upc;ean;jan;isbn;mpn;location;quantity;model;manufacturer;image_name;shipping;price;points;date_added;date_modified;date_available;weight;weight_unit;length;width;height;length_unit;status;tax_class_id;seo_keyword;description(ru-ru);meta_title(ru-ru);meta_description(ru-ru);meta_keywords(ru-ru);stock_status_id;store_ids;layout;related_ids;tags(ru-ru);sort_order;subtract;minimum;',
            'row' => '{id};{title};{categories};;;;;;;;100;{model};Fasion Lab;;yes;{price};0;{now};{now};{now};0;г;{length};{width};{height};0;true;0;{title-translit};{description};{meta-description};{meta-description};;7;0;0:;;;1;false;1;'*/
            'header' => 'product_id,name(ru-ru),categories,sku,upc,ean,jan,isbn,mpn,location,quantity,model,manufacturer,image_name,shipping,price,points,date_added,date_modified,date_available,weight,weight_unit,length,width,height,length_unit,status,tax_class_id,seo_keyword,description(ru-ru),meta_title(ru-ru),meta_description(ru-ru),meta_keywords(ru-ru),stock_status_id,store_ids,layout,related_ids,tags(ru-ru),sort_order,subtract,minimum',
            'row' => '{id},{title},{categories},,,,,,,,100,{model},Fasion Lab,{image},yes,{price},0,{now},{now},{now},0,г,{length},{width},{height},0,true,0,{title-translit},{description},{meta-description},{meta-description},,7,0,0:,,,1,false,1'
        ], [
            'file' => 'additionalImages.csv',
            'header' => 'product_id,image,sort_order',
            'row' => '{id},{image},0'
        ], [
            'file' => 'specials.csv',
            'header' => 'product_id,customer_group,priority,price,date_start,date_end',
            'row' => '{id},Default,0,{price},{now},{nextDate}'
        ], [
            'file' => 'productOptions.csv',
            'header' => 'product_id,option,default_option_value,required',
            'row' => '{id},{param},,true'
        ], [
            'file' => 'productOptionValues.csv',
            'header' => 'product_id,option,option_value,quantity,subtract,price,price_prefix,points,points_prefix,weight,weight_prefix',
            'row' => '{id},{param},{value},0,false,0,+,0,+,0,+'
        ]
    ];

    const PARAMS = [
        'color' => 'Цвет',
        'size' => 'Размер',
    ];

    const PROJECT_ID_PREFIX = [
        'Zean' => '10000',
        'Komistar' => '11000'
    ];

    const PROJECT_MODEL_PREFIX = [
        'Zean' => 'z1-0000',
        'Komistar' => 'k1-0000',
    ];

    const PATH_TO_CATEGORY = [
        '0' => 'catalog/',
        '62' => 'catalog/verx_odezda/',
        '63' => 'catalog/platya/',
        '66' => 'catalog/sport/',
        '67' => 'catalog/kombez/',
        '69' => 'catalog/futbolki-bluzu/',
        '70' => 'catalog/odezhda_dom/',
        '72' => 'catalog/beremenue/',
        '74' => 'catalog/bigsize/',
        '91' => 'catalog/tuniki/',
        '97' => 'catalog/yubki/',
        '104' => 'catalog/kostumu/',
        '114' => 'catalog/bruki_shortu/',
        '109' => 'catalog/futbolki-bluzu/',
    ];

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

    /**
     * @var string
     */
    private $project;


    public function __construct($name = 'parser:export:build', EntityManagerInterface $entityManager)
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
            ->setDescription('Build export files')
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
        $this->project = $input->getArgument('project');

        if (!$this->project) {
            echo 'Project param require';

            return;
        }

        $products = $this->entityManager->getRepository(RealProduct::class)->findBy(['project' => $this->project]);

        $this->buildProducts($products, self::DATA[0]);
        $this->buildAdditionalImages($products, self::DATA[1]);
        $this->buildSpecials($products, self::DATA[2]);
        $this->buildProductOptions($products, self::DATA[3]);
        $this->buildProductOptionValues($products, self::DATA[4]);
    }

    private function buildProducts(array $products, array $data)
    {
        $file = $data['header'] . "\r\n";
        $now = date('Y-m-d') . ' 00:00:00';

        /** @var RealProduct $product */
        foreach ($products as $product) {
            $params = [
                'title' => '',
                'categories' => '0',
                'description' => '',
                'id' => $this->getId($product->getId()),
                'model' => $this->getModel($product),
                'now' => $now,
                'price' => '0',
                'length' => '',
                'width' => '',
                'height' => '',
            ];

            /** @var RealProductParam $item */
            foreach ($product->getParams() as $item) {
                $value = str_replace("\r", '', $item->getValue());
                $value = str_replace("\n", '', $value);
                $params[$item->getName()] = $value;
            }

            $params['meta-description'] = strip_tags($params['description']);
            $params['title-translit'] = TranslitHelper::translit($params['title']);
            if (isset($params[ParamsDictionary::PARAM_IMAGE])) {
                $params[ParamsDictionary::PARAM_IMAGE] = $this->getImage($params[ParamsDictionary::PARAM_IMAGE], $params[ParamsDictionary::PARMA_CATEGORIES]);
            }

            $template = $data['row'];
            foreach ($params as $key => $param) {
                $value = str_replace("\r", ' ', $param);
                $value = str_replace("\n", ' ', $value);
                $template = str_replace('{' . $key . '}', '"' . $value . '"', $template);
            }

            $file .= $template . "\r\n";
        }

        file_put_contents(self::FILES_DIR . DIRECTORY_SEPARATOR . $data['file'], $file);
    }

    private function buildAdditionalImages(array $products, array $data)
    {
        $file = $data['header'] . "\r\n";
        $newImages = [];

        /** @var RealProduct $product */
        foreach ($products as $product) {
            $images = '';
            $categories = '0';
            /** @var RealProductParam $param */
            foreach ($product->getParams() as $param) {
                if ($param->getName() == ParamsDictionary::PARAM_IMAGE) {
                    $images = $param->getValue();
                }

                if ($param->getName() == ParamsDictionary::PARMA_CATEGORIES) {
                    $categories = $param->getValue();
                }
            }

            if (!$images) {
                continue;
            }

            $images = explode(',', $images);
            foreach ($images as $image) {
                $image = trim($image);
                $imagePath = self::TARGET_DIR . DIRECTORY_SEPARATOR . $this->project . DIRECTORY_SEPARATOR . $image;

                if (!file_exists($imagePath)) {
                    continue;
                }

                $newImage = $this->getImage($image, $categories);
                $newImagePath = self::TARGET_DIR . DIRECTORY_SEPARATOR . $this->project . '_export' . DIRECTORY_SEPARATOR . $newImage;

                $imageParts = explode(DIRECTORY_SEPARATOR, str_replace('/', DIRECTORY_SEPARATOR, $newImagePath));
                unset($imageParts[count($imageParts) - 1]);
                $newImageDir = implode(DIRECTORY_SEPARATOR, $imageParts);

                if (!file_exists($newImageDir)) {
                    mkdir($newImageDir, 0777, true);
                }

                copy($imagePath, str_replace('/', DIRECTORY_SEPARATOR, $newImagePath));

                if (!isset($newImages[$this->getId($product->getId())])) {
                    $newImages[$this->getId($product->getId())] = [];
                }

                $newImages[$this->getId($product->getId())][] = $newImage;
            }
        }

        foreach ($newImages as $id => $images) {
            foreach ($images as $image) {
                $template = $data['row'];

                $template = str_replace('{id}', $id, $template);
                $template = str_replace('{image}', $image, $template);

                $file .= $template . "\r\n";
            }
        }

        file_put_contents(self::FILES_DIR . DIRECTORY_SEPARATOR . $data['file'], $file);
    }

    private function buildSpecials(array $products, array $data)
    {
        $file = $data['header'] . "\r\n";
        $now = date('Y-m-d') . ' 00:00:00';
        $nextDate = '2020-' . date('m-d') . ' 00:00:00';

        /** @var RealProduct $product */
        foreach ($products as $product) {
            $params = [
                'id' => $this->getId($product->getId()),
                'now' => $now,
                'price' => '0',
                'nextDate' => $nextDate,
            ];

            /** @var RealProductParam $item */
            foreach ($product->getParams() as $item) {
                $params[$item->getName()] = $item->getValue();
            }

            $template = $data['row'];
            foreach ($params as $key => $param) {
                $value = str_replace("\r", ' ', $param);
                $value = str_replace("\n", ' ', $value);
                $template = str_replace('{' . $key . '}', '"' . $value . '"', $template);
            }

            $file .= $template . "\r\n";
        }

        file_put_contents(self::FILES_DIR . DIRECTORY_SEPARATOR . $data['file'], $file);
    }

    private function buildProductOptions(array $products, array $data)
    {
        $file = $data['header'] . "\r\n";

        /** @var RealProduct $product */
        foreach ($products as $product) {
            /** @var RealProductParam $param */
            foreach ($product->getParams() as $param) {
                if (!isset(self::PARAMS[mb_strtolower($param->getName())])) {
                    continue;
                }

                $name = self::PARAMS[mb_strtolower($param->getName())];

                $template = $data['row'];
                $template = str_replace('{id}', '"' . $this->getId($product->getId()) . '"', $template);
                $template = str_replace('{param}', '"' . $name . '"', $template);

                $file .= $template . "\r\n";
            }
        }

        file_put_contents(self::FILES_DIR . DIRECTORY_SEPARATOR . $data['file'], $file);
    }

    private function buildProductOptionValues(array $products, array $data)
    {
        $file = $data['header'] . "\r\n";

        /** @var RealProduct $product */
        foreach ($products as $product) {
            /** @var RealProductParam $param */
            foreach ($product->getParams() as $param) {
                if (!isset(self::PARAMS[mb_strtolower($param->getName())])) {
                    continue;
                }

                $name = self::PARAMS[mb_strtolower($param->getName())];
                $values = explode(',', $param->getValue());

                foreach ($values as $value) {
                    $template = $data['row'];
                    $template = str_replace('{id}', '"' . $this->getId($product->getId()) . '"', $template);
                    $template = str_replace('{param}', '"' . $name . '"', $template);
                    $template = str_replace('{value}', '"' . trim($value) . '"', $template);

                    $file .= $template . "\r\n";
                }
            }
        }

        file_put_contents(self::FILES_DIR . DIRECTORY_SEPARATOR . $data['file'], $file);
    }

    private function getImage(string $images, string $categories): string
    {
        $categoriesParts = explode(',', $categories);
        $category = trim($categoriesParts[0]);

        $path = isset(self::PATH_TO_CATEGORY[$category]) ? self::PATH_TO_CATEGORY[$category] : self::PATH_TO_CATEGORY['0'];
        $parts = explode(',', $images);

        return $path . trim($parts[0]);
    }

    private function getId(int $id): string
    {
        $template = self::PROJECT_ID_PREFIX[$this->project];
        $idString = (string) $id;

        $template = substr($template, 0, strlen($template) - strlen($idString));

        return $template . $idString;
    }

    private function getModel(RealProduct $product): string
    {
        $template = self::PROJECT_MODEL_PREFIX[$this->project];

        $model = (string) $product->getId();

        /** @var RealProductParam $param */
        foreach ($product->getParams() as $param) {
            if ($param->getName() == ParamsDictionary::PARAM_MODEL) {
                $model = $param->getValue();

                break;
            }
        }

        $template = substr($template, 0, strlen($template) - strlen($model));

        return $template . $model;
    }
}