<?php

namespace ISI\ORGBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PaysActivite
 * @ORM\Table(name="activitePays")
 * @ORM\Entity(repositoryClass="ISI\ORGBundle\Repository\ActivitePaysRepository")
 */
class ActivitePays
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
     * @ORM\ManyToOne(targetEntity="Pays", inversedBy="activites")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pays", referencedColumnName="id", nullable=false)
     * })
     */
    private $pays;

    /**
     * @ORM\ManyToOne(targetEntity="Activite", inversedBy="payss")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="activite", referencedColumnName="id", nullable=false)
     * })
     */
    private $activite;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPays(): ?Pays
    {
        return $this->pays;
    }

    public function setPays($pays): self
    {
        $this->pays = $pays;

        return $this;
    }

    public function getActivite(): ?Activite
    {
        return $this->activite;
    }

    public function setActivite($activite): self
    {
        $this->activite = $activite;

        return $this;
    }
}
