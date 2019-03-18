<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class RealProductParam
 * @package App\Entity
 * @ORM\Table(name="real_params")
 * @ORM\Entity()
 */
class RealProductParam
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
     * @ORM\Column(name="field", type="string")
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="string")
     */
    private $value;

    /**
     * @var Product
     *
     * @ORM\ManyToOne(targetEntity="RealProduct", inversedBy="params")
     * @ORM\JoinColumn(name="productId", referencedColumnName="id")
     */
    private $product;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setValue(string $value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return Product
     */
    public function getProduct(): Product
    {
        return $this->product;
    }

    /**
     * @param RealProduct $product
     *
     * @return $this
     */
    public function setProduct(RealProduct $product)
    {
        $this->product = $product;

        return $this;
    }
}