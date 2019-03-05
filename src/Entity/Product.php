<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Product
 * @package App\Entity
 * @ORM\Table(name="product")
 * @ORM\Entity()
 */
class Product
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
}