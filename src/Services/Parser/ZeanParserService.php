<?php

namespace App\Services\Parser;

use App\Dictionary\ParamsDictionary;
use App\Interfaces\ParserServiceInterface;

/**
 * Class ZeanParserService
 * @package App\Services\Parser
 */
class ZeanParserService implements ParserServiceInterface
{
    /**
     * @var string
     */
    private $catalogLink = 'https://www.zean.ua/index.php?route=module/journal2_super_filter/products&module_id=5&filters=%2Fsort%3Dp.sort_order%2Forder%3DASC%2Flimit%3D10%2Fpage%3D{page}&oc_route=product%2Fcategory&path=243&manufacturer_id=&search=&tag=';

    /**
     * @var string
     */
    private $productLinkRegex = '/<h4 class="name"><a href="(https:\/\/www\.zean\.ua\/.*)">/Us';

    /**
     * @param string $html
     *
     * @return array
     */
    public function parseProduct(string $html): array
    {
        return [
            [
                'name' => 'Название',
                'type' => ParamsDictionary::PARAM_TEXT,
                'value' => $this->getTitle($html)
            ],
            [
                'name' => 'Фото',
                'type' => ParamsDictionary::PARAM_PHOTO,
                'value' => $this->getPhoto($html)
            ],
            [
                'name' => 'Описание',
                'type' => ParamsDictionary::PARAM_LONGTEXT,
                'value' => $this->getDescription($html)
            ],
            [
                'name' => 'Модель',
                'type' => ParamsDictionary::PARAM_TEXT,
                'value' => $this->getModel($html)
            ],
            [
                'name' => 'Цена',
                'type' => ParamsDictionary::PARAM_TEXT,
                'value' => $this->getPrice($html)
            ],
            [
                'name' => 'Размер',
                'type' => ParamsDictionary::PARAM_TEXT_OR_CHECK,
                'value' => $this->getSize($html)
            ],
            [
                'name' => 'Цвет',
                'type' => ParamsDictionary::PARAM_TEXT_OR_CHECK,
                'value' => $this->getColor($html)
            ],
        ];
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
    public function getCatalogPageUrl(int $page): string
    {
        return str_replace('{page}', $page, $this->catalogLink);
    }

    /**
     * @param string $html
     *
     * @return string
     */
    private function getTitle(string $html): string
    {
        if (preg_match('/<h1 class="heading-title" itemprop="name">(.*)<\/h1>/Us', $html, $matches)) {
            return $matches[1];
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
        if (preg_match('/<div class="tab-pane tab-content active" id="tab-description">(.*)<\/div>/Us', $html, $matches)) {
            $result = $matches[1];
            $result = str_replace('<br>', "\r\n", $result);
            $result = str_replace('<br/>', "\r\n", $result);
            $result = str_replace('<br />', "\r\n", $result);

            $parts = explode("\r\n", $result);

            foreach ($parts as $key => $part) {
                $parts[$key] = str_replace(' ', '', $parts[$key]);
                $parts[$key] = trim($parts[$key]);

                if (!$parts[$key]) {
                    unset($parts[$key]);
                }
            }

            return implode("\r\n", $parts);
        }

        return '';
    }

    /**
     * @param string $html
     *
     * @return string
     */
    private function getModel(string $html): string
    {
        if (preg_match('/<span class="p-model" itemprop="model">(.*)<\/span>/Us', $html, $matches)) {
            return $matches[1];
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
        if (preg_match('/<li class="price-new">(.*)грн<\/li>/Us', $html, $matches)) {
            return $matches[1];
        }

        return '';
    }

    /**
     * @param string $html
     *
     * @return string[]
     */
    private function getPhoto(string $html): array
    {
        if (preg_match_all('/<a class="swiper-slide" style="" href="(.*)" title/Us', $html, $matches)) {
            return $matches[1];
        }

        return [];
    }

    /**
     * @param string $html
     *
     * @return array
     */
    private function getSize(string $html): array
    {
        if (preg_match_all('/<div class="checkbox">.*<input type="checkbox".*>(.*)<\/label>/Us', $html, $matches)) {
            $result = [];
            foreach ($matches[1] as $value) {
                $result[] = trim($value);
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
        if (preg_match_all('/<div class="radio">.*<img.*>.*<img.*>(.*)<\/label>/Us', $html, $matches)) {
            $result = [];
            foreach ($matches[1] as $value) {
                $result[] = trim($value);
            }

            return $result;
        }

        return [];
    }
}