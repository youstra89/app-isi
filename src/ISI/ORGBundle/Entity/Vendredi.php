<?php

namespace ISI\ORGBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Vendredi
 * @ORM\Table(name="vendredi")
 * @ORM\Entity(repositoryClass="ISI\ORGBundle\Repository\VendrediRepository")
 */
class Vendredi
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity="Imam")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="imam", referencedColumnName="id", nullable=false)
     * })
     */
    private $imam;

    /**
     * @ORM\ManyToOne(targetEntity="Mosquee")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="mosquee", referencedColumnName="id", nullable=false)
     * })
     */
    private $mosquee;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updated_at;


    public function __construct()
    {
      $this->created_at = new \Datetime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getMosquee(): ?Mosquee
    {
        return $this->mosquee;
    }

    public function setMosquee($mosquee): self
    {
        $this->mosquee = $mosquee;

        return $this;
    }

    public function getImam(): ?Imam
    {
        return $this->imam;
    }

    public function setImam($imam): self
    {
        $this->imam = $imam;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }
}
