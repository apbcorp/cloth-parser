<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class ParamVariants
 * @package App\Entity
 * @ORM\Table(name="paramVariants")
 * @ORM\Entity()
 */
class ParamVariants
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="param", type="string")
     */
    private $param = '';

    /**
     * @var string
     *
     * @ORM\Column(name="variants", type="string")
     */
    private $variants = '';

    public function getId(): int
    {
        return $this->id;
    }

    public function getParam(): string
    {
        return $this->param;
    }

    public function setParam(string $param)
    {
        $this->param = $param;

        return $this;
    }

    public function getVariants(): string
    {
        return $this->variants;
    }

    public function setVariants(string $variants)
    {
        $this->variants = $variants;

        return $this;
    }

    public function getVariantsAsArray(): array
    {
        return explode('|', $this->variants);
    }

    public function setVariantsAsArray(array $values)
    {
        $this->variants = implode('|', $values);

        return $this;
    }
}