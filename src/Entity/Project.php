<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Project
 * @package App\Entity
 * @ORM\Table(name="project")
 * @ORM\Entity()
 */
class Project
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
     * @ORM\Column(name="name", type="string")
     */
    private $name = '';

    /**
     * @var string
     *
     * @ORM\Column(name="service", type="string")
     */
    private $service = '';

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="Product", mappedBy="project", cascade={"persist", "remove"})
     */
    private $products;

    /**
     * Project constructor.
     */
    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    public function getService(): string
    {
        return $this->service;
    }

    public function setService(string $service)
    {
        $this->service = $service;

        return $this;
    }

    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product)
    {
        if (!$this->products->contains($product)) {
            $product->setProject($this);
            $this->products->add($product);
        }

        return $this;
    }
}