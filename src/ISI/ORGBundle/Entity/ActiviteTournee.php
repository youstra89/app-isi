<?php

namespace ISI\ORGBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TourneeActivite
 * @ORM\Table(name="activiteTournee")
 * @ORM\Entity(repositoryClass="ISI\ORGBundle\Repository\ActiviteTourneeRepository")
 */
class ActiviteTournee
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
     * @ORM\ManyToOne(targetEntity="Tournee", inversedBy="activites")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tournee", referencedColumnName="id", nullable=false)
     * })
     */
    private $tournee;

    /**
     * @ORM\ManyToOne(targetEntity="Activite")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="activite", referencedColumnName="id", nullable=false)
     * })
     */
    private $activite;


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
