<?php

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class ProjectController
 * @package App\Controller\Api
 */
class ProjectController
{
    /**
     * @return JsonResponse
     */
    public function listAction(): JsonResponse
    {
        return new JsonResponse(['success' => true, 'result' => [1 => 'Zean', 2 => 'Komistar']]);
    }
}