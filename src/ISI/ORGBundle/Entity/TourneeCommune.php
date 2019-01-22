<?php

namespace ISI\ORGBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TourneeCommune
 * @ORM\Table(name="tourneeCommune")
 * @ORM\Entity(repositoryClass="ISI\ORGBundle\Repository\TourneeCommuneRepository")
 */
class TourneeCommune
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
     * @ORM\ManyToOne(targetEntity="Tournee", inversedBy="communes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tournee", referencedColumnName="id", nullable=false)
     * })
     */
    private $tournee;

    /**
     * @ORM\ManyToOne(targetEntity="Commune")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="commune", referencedColumnName="id", nullable=false)
     * })
     */
    private $commune;


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

    public function getCommune(): ?Commune
    {
        return $this->commune;
    }

    public function setCommune($commune): self
    {
        $this->commune = $commune;

        return $this;
    }
}
