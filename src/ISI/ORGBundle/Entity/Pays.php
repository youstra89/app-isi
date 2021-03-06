<?php

namespace ISI\ORGBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Pays
 * @UniqueEntity("nom")
 * @ORM\Table(name="pays")
 * @ORM\Entity(repositoryClass="ISI\ORGBundle\Repository\PaysRepository")
 */
class Pays
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min=5, max=255)
     */
    private $nom;

    /**
     * @ORM\OneToMany(targetEntity="Converti", mappedBy="pays")
     */
    private $convertis;


    public function __construct()
    {
      $this->createdAt = new \Datetime();
      // $this->mosquees = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Add converti
     *
     * @param \ISI\ORGBundle\Entity\Converti $converti
     *
     * @return Pays
     */
    public function addConverti(\ISI\ORGBundle\Entity\Converti $converti)
    {
        $this->convertis[] = $converti;

        return $this;
    }

    /**
     * Remove converti
     *
     * @param \ISI\ORGBundle\Entity\Converti $converti
     */
    public function removeConverti(\ISI\ORGBundle\Entity\Converti $converti)
    {
        $this->convertis->removeElement($converti);
    }

    /**
     * Get convertis
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getConvertis()
    {
        return $this->convertis;
    }
}
