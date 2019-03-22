<?php

namespace App\Services\Processor;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class ProductProcessor
 * @package App\Services\Processor
 */
class ProductProcessor
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ProductParamsProcessor
     */
    private $productParamsProcessor;

    /**
     * ProductProcessor constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param ProductParamsProcessor $processor
     */
    public function __construct(EntityManagerInterface $entityManager, ProductParamsProcessor $processor)
    {
        $this->entityManager = $entityManager;
        $this->productParamsProcessor = $processor;
    }

    /**
     * @param int   $productId
     * @param array $data
     * @param bool  $autosave
     */
    public function upsertProduct(int $productId, array $data, bool $autosave = true)
    {
        /** @var Product $product */
        $product = $this->entityManager->getRepository(Product::class)->find($productId);

        if (isset($data['status'])) {
            $this->processSetStatus($product, $data['status']);
        }

        if (isset($data['params'])) {
            $this->productParamsProcessor->upsertParams($product->getId(), $data['params'], false);
        }

        if ($autosave) {
            $this->entityManager->flush();
        }
    }

    /**
     * @param Product $product
     * @param int     $status
     */
    private function processSetStatus(Product $product, int $status)
    {
        if (!in_array($status, Product::STATUS_LIST)) {
            throw new \Exception('Unknown status');
        }

        $product->setStatus($status);
    }
}