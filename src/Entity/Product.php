<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Product
 * @package App\Entity
 * @ORM\Table(name="product")
 * @ORM\Entity(repositoryClass="App\Repository\ProductRepository")
 */
class Product
{
    public const STATUS_NEW = 0;
    public const STATUS_APPROVE = 1;
    public const STATUS_DECLINE = 2;

    public const STATUS_LIST = [
        self::STATUS_NEW     => self::STATUS_NEW,
        self::STATUS_APPROVE => self::STATUS_APPROVE,
        self::STATUS_DECLINE => self::STATUS_DECLINE,
    ];

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id = 0;

    /**
     * @var Project|null
     *
     * @ORM\ManyToOne(targetEntity="Project", inversedBy="products")
     * @ORM\JoinColumn(name="projectId", referencedColumnName="id")
     */
    private $project;

    /**
     * @var string
     *
     * @ORM\Column(name="link", type="string")
     */
    private $link = '';

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string")
     */
    private $code = '';

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="integer")
     */
    private $status = self::STATUS_NEW;

    public function getId(): int
    {
        return $this->id;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project)
    {
        $this->project = $project;

        return $this;
    }

    public function getLink(): string
    {
        return $this->link;
    }

    public function setLink(string $link)
    {
        $this->link = $link;

        return $this;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code)
    {
        $this->code = $code;

        return $this;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(string $status)
    {
        $this->status = $status;

        return $this;
    }
}