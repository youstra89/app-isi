<?php

namespace ISI\ORGBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TourneePays
 * @ORM\Table(name="tourneePays")
 * @ORM\Entity(repositoryClass="ISI\ORGBundle\Repository\TourneePaysRepository")
 */
class TourneePays
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
     * @ORM\ManyToOne(targetEntity="Tournee", inversedBy="pays")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tournee", referencedColumnName="id", nullable=false)
     * })
     */
    private $tournee;

    /**
     * @ORM\ManyToOne(targetEntity="Pays")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pays", referencedColumnName="id", nullable=false)
     * })
     */
    private $pays;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTournee(): ?Tournee
    {
        return $this->tournee;
    }

    public function setTournee($tournee): self
    {
        $this->tournee = $tournee;

        return $this;
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
}
