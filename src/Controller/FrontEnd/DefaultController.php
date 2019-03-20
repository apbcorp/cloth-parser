<?php

namespace App\Controller\FrontEnd;

use App\Helper\ViewHelper;
use App\Interfaces\AuthenticatedControllerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DefaultController
 * @package App\Controller\FrontEnd
 */
class DefaultController implements AuthenticatedControllerInterface
{
    /**
     * @return Response
     */
    public function projectAction(): Response
    {
        return ViewHelper::getResponse(['/js/project.js']);
    }

    /**
     * @param int $projectId
     *
     * @return Response
     */
    public function productListAction(int $projectId): Response
    {
        return ViewHelper::getResponse(['/js/paramVariants.js', '/js/product.js']);
    }
}