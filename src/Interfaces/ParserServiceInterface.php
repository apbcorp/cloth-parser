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
}