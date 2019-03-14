<?php

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class ProductController
 * @package App\Controller\Api
 */
class ProductController
{
    /**
     * @return JsonResponse
     */
    public function listAction(): JsonResponse
    {
        return new JsonResponse(['success' => true, 'result' => [1 => 'Zean', 2 => 'Komistar']]);
    }
}