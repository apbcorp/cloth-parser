<?php

namespace App\Entity;

/**
 * Class ApprovedProductParams
 * @package App\Entity
 * @ORM\Table(name="approvedProductParams")
 * @ORM\Entity()
 */
class ApprovedProductParams
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
     * @var int
     * @ORM\Column(name="productId", type="integer")
     */
    private $productId;

    /**
     * @var string
     *
     * @ORM\Column(name="param", type="string")
     */
    private $param = '';

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="string")
     */
    private $value = '';

    public function getId(): int
    {
        return $this->id;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function setProductId(int $id)
    {
        $this->productId = $id;

        return $this;
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

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value)
    {
        $this->value = $value;

        return $this;
    }
}