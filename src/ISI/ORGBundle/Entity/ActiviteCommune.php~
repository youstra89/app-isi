<?php

namespace ISI\ORGBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CommuneActivite
 * @ORM\Table(name="activiteCommune")
 * @ORM\Entity(repositoryClass="ISI\ORGBundle\Repository\ActiviteCommuneRepository")
 */
class ActiviteCommune
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
     * @ORM\ManyToOne(targetEntity="Commune", inversedBy="activites")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="commune_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $commune;

    /**
     * @ORM\ManyToOne(targetEntity="Activite", inversedBy="communes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="activite_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $activite;


    public function getId(): ?int
    {
        return $this->id;
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
