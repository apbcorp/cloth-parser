<?php

namespace App\Interfaces;

/**
 * Interface ParserServiceInterface
 * @package App\Interfaces
 */
interface ParserServiceInterface
{
    /**
     * @param string $html
     *
     * @return array
     */
    public function parseProduct(string $html): array;

    /**
     * @param int $page
     *
     * @return string
     */
    public function getCatalogPageUrl(int $page): string;

    /**
     * @param string $html
     *
     * @return array
     */
    public function getProductLinks(string $html): array;
}