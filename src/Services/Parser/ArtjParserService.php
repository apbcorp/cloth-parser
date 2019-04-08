<?php

namespace App\Services\Parser;

use App\Dictionary\ParamsDictionary;
use App\Interfaces\ParserServiceInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class ArtjParserService
 * @package App\Services\Parser
 */
class ArtjParserService implements ParserServiceInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var string
     */
    private $catalogLink = 'http://artj.com.ua/catalog/artj/?page={page}';

    /**
     * @var string
     */
    private $productLinkRegex = '/"(http:\/\/artj\.com\.ua\/catalog\/[^"]*\.html)"/Us';

    /**
     * ZeanParserService constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param string $html
     *
     * @return array
     */
    public function getProductLinks(string $html): array
    {
        return preg_match_all($this->productLinkRegex, $html, $matches)
            ? $matches[1]
            : [];
    }

    /**
     * @param int $page
     *
     * @return string
     */
    public function getCatalogPageUrl($page): string
    {
        return str_replace('{page}', $page, $this->catalogLink);
    }

    /**
     * @param int $productId
     *
     * @return array
     */
    public function getProduct(int $productId): array
    {
        return [];
    }

    /**
     * @param string $html
     *
     * @return array
     */
    public function parseProduct(string $html): array
    {
        $html = str_replace("\r", '', $html);
        $html = str_replace("\n", '', $html);

        $result = $this->getFields();
        $result[0]['value'] = $this->getTitle($html);
        $result[1]['value'] = $this->getPhoto($html);
        $result[2]['value'] = $this->getDescription($html);
        $result[3]['value'] = $this->getModel($html);
        $result[4]['value'] = $this->getPrice($html);
        $result[5]['value'] = $this->getSize($html);
        $result[6]['value'] = $this->getColor($result[0]['value']);

        return $result;
    }

    /**
     * @param string $html
     *
     * @return string
     */
    private function getTitle(string $html): string
    {
        if (preg_match('/"product-info__title".*>(.*)<\/h1>/Ui', $html, $matches)) {
            return $matches[1];
        }

        return '';
    }

    /**
     * @param string $html
     *
     * @return array
     */
    private function getPhoto(string $html): array
    {
        if (preg_match_all('/<img.*src="(http:\/\/artj\.com\.ua.*)".*"product-image-zoom".*>/Ui', $html, $matches)) {
            $result = [];

            foreach ($matches[1] as $img) {
                if (!in_array($img, $result)) {
                    $result[] = $img;
                }
            }

            return $result;
        }

        return [];
    }

    /**
     * @param string $html
     *
     * @return array
     */
    private function getColor(string $html): array
    {
        $parts = explode(' ', $html);

        return [$parts[count($parts) - 1]];
    }

    /**
     * @param string $html
     *
     * @return array
     */
    private function getSize(string $html): array
    {
        if (preg_match_all('/"m-amount-row".*>.*<div>(.*)<\/div>/Ui', $html, $matches)) {
            return $matches[1];
        }

        return [];
    }

    /**
     * @param string $html
     *
     * @return string
     */
    private function getModel(string $html): string
    {
        if (preg_match('/Модель.*<\/span>(.*)<\/li>/Ui', $html, $matches)) {
            return trim($matches[1]);
        }

        return '';
    }

    /**
     * @param string $html
     *
     * @return string
     */
    private function getDescription(string $html): string
    {
        if (preg_match('/"tab-description".*>(.*)<\/div>/Ui', $html, $matches)) {
            return trim($matches[1]);
        }

        return '';
    }

    /**
     * @param string $html
     *
     * @return string
     */
    private function getPrice(string $html): string
    {
        if (preg_match('/"price".*>.*(\d* грн).*<\/div>/Ui', $html, $matches)) {
            return trim(str_replace('грн', '', $matches[1]));
        }

        return '';
    }

    /**
     * @return array
     */
    private function getFields(): array
    {
        return [
            [
                'name' => 'Название',
                'type' => ParamsDictionary::PARAM_TYPE_TEXT,
            ],
            [
                'name' => 'Фото',
                'type' => ParamsDictionary::PARAM_TYPE_PHOTO,
            ],
            [
                'name' => 'Описание',
                'type' => ParamsDictionary::PARAM_TYPE_LONGTEXT,
            ],
            [
                'name' => 'Модель',
                'type' => ParamsDictionary::PARAM_TYPE_TEXT,
            ],
            [
                'name' => 'Цена',
                'type' => ParamsDictionary::PARAM_TYPE_TEXT,
            ],
            [
                'name' => 'Размер',
                'type' => ParamsDictionary::PARAM_TYPE_MULTI,
            ],
            [
                'name' => 'Цвет',
                'type' => ParamsDictionary::PARAM_TYPE_MULTI,
            ],
        ];
    }
}