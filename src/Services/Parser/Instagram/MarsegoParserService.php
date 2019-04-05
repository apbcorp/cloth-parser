<?php

namespace App\Services\Parser\Instagram;

/**
 * Class MarsegoParserService
 * @package App\Services\Parser\Instagram
 */
class MarsegoParserService extends AbstractInstagramParserService
{
    /**
     * @var string
     */
    private $catalogLink = 'https://www.instagram.com/marsego_lovesyou/';

    /**
     * @param string $html
     *
     * @return array
     */
    public function parseProduct(string $html): array
    {
        return [];
    }

    /**
     * @param string $page
     *
     * @return string
     */
    public function getCatalogPageUrl($page): string
    {
        return $this->catalogLink;
    }

    /**
     * @param string $html
     *
     * @return array
     */
    public function getProductLinks(string $html): array
    {
        if (preg_match_all('/"shortcode":"(.*)"/U', $html, $matches)) {
            return $matches[1];
        }

        return [];
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
}