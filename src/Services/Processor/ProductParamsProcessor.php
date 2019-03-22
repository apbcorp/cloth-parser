<?php

namespace App\Services\Processor;

use App\Entity\ApprovedProductParams;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class ProductParamsProcessor
 * @package App\Services\Processor
 */
class ProductParamsProcessor
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ParamVariantsProcessor
     */
    private $productVariantsProcessor;

    /**
     * ProductProcessor constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param ParamVariantsProcessor $processor
     */
    public function __construct(EntityManagerInterface $entityManager, ParamVariantsProcessor $processor)
    {
        $this->entityManager = $entityManager;
        $this->productVariantsProcessor = $processor;
    }

    /**
     * @param int   $productId
     * @param array $params
     * @param bool  $autosave
     */
    public function upsertParams(int $productId, array $params, $autosave = false)
    {
        $this->removeAllProductParams($productId);

        foreach ($params as $param => $data) {
            if (is_array($data['value'])) {
                $this->processArrayParam($productId, $param, $data['value']);
            } else {
                $this->processParam($productId, $param, (string) $data['value']);
            }
        }

        $this->productVariantsProcessor->upsertVariants($params);

        if ($autosave) {
            $this->entityManager->flush();
        }
    }

    /**
     * @param int $productId
     */
    private function removeAllProductParams(int $productId)
    {
        $sql = 'DELETE FROM `approvedProductParams` where productId = :productId';

        $stmt = $this->entityManager->getConnection()->prepare($sql);
        $stmt->execute(['productId' => $productId]);
    }

    /**
     * @param int    $productId
     * @param string $param
     * @param array  $values
     */
    private function processArrayParam(int $productId, string $param, array $values)
    {
        foreach ($values as $value) {
            $this->processParam($productId, $param, (string) $value);
        }
    }

    /**
     * @param int    $productId
     * @param string $param
     * @param string $value
     */
    private function processParam(int $productId, string $param, string $value)
    {
        $entity = (new ApprovedProductParams())
            ->setProductId($productId)
            ->setParam($param)
            ->setValue($value);

        $this->entityManager->persist($entity);
    }
}