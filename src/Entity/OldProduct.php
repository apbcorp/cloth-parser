<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Product
 * @package App\Entity
 * @ORM\Table(name="products")
 * @ORM\Entity(repositoryClass="App\Repository\OldProductRepository")
 */
class OldProduct
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
     * @ORM\Column(name="project", type="string")
     */
    private $project = '';

    /**
     * @var string
     *
     * @ORM\Column(name="link", type="string")
     */
    private $link = '';

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="ProductParam", mappedBy="product", cascade={"persist", "remove"})
     */
    private $params;

    /**
     * Product constructor.
     */
    public function __construct()
    {
        $this->params = new ArrayCollection();
    }

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
    public function getProject(): string
    {
        return $this->project;
    }

    /**
     * @param string $project
     *
     * @return $this
     */
    public function setProject(string $project)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * @return string
     */
    public function getLink(): string
    {
        return $this->link;
    }

    /**
     * @param string $link
     *
     * @return $this
     */
    public function setLink(string $link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getParams(): Collection
    {
        return $this->params;
    }

    /**
     * @param ProductParam $param
     *
     * @return $this
     */
    public function addParam(ProductParam $param)
    {
        /** @var ProductParam $item */
        foreach ($this->params as $item) {
            if ($item->getName() === $param->getName()) {
                $item->setValue($param->getValue());

                return $this;
            }
        }

        $param->setProduct($this);
        $this->params->add($param);

        return $this;
    }
}