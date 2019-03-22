<?php

namespace App\Services\Processor;


use App\Dictionary\ParamsDictionary;
use App\Entity\ParamVariants;
use Doctrine\ORM\EntityManagerInterface;

class ParamVariantsProcessor
{
    /**
     * @var
     */
    private $entityManager;

    /**
     * ProductVariantsProcessor constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param array $params
     * @param bool  $autosave
     */
    public function upsertVariants(array $params, bool $autosave = false)
    {
        foreach ($params as $param => $data) {
            if (!in_array($data['type'], ParamsDictionary::PARAM_TYPES_WITH_VARIANTS)) {
                continue;
            }

            $values = is_array($data['value']) ? $data['value'] : [$data['value']];

            $this->appendVariants($param, $values);
        }

        if ($autosave) {
            $this->entityManager->flush();
        }
    }

    /**
     * @param string $param
     * @param array  $values
     */
    private function appendVariants(string $param, array $values)
    {
        /** @var ParamVariants $entity */
        $entity = $this->entityManager->getRepository(ParamVariants::class)->findOneBy(['param' => $param]);

        if (!$entity) {
            $entity = (new ParamVariants())
                ->setParam($param);

            $this->entityManager->persist($entity);
        }

        $entityValues = $entity->getVariantsAsArray();
        foreach ($values as $value) {
            if (!in_array($value, $entityValues)) {
                $entityValues[] = $value;
            }
        }

        $entity->setVariantsAsArray($entityValues);
    }
}