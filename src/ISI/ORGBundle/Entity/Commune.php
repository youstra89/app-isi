<?php

namespace ISI\ORGBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Region
 * @UniqueEntity("nom")
 * @ORM\Table(name="commune")
 * @ORM\Entity(repositoryClass="ISI\ORGBundle\Repository\CommuneRepository")
 */
class Commune
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
     * @ORM\ManyToOne(targetEntity="Ville", inversedBy="communes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $ville;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Regex("/^[0-9]{8}$/")
     */
    private $nombre_habitants;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Regex("/^[0-9]{8}$/")
     */
    private $taux_musulmans;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity="ISI\ORGBundle\Entity\Mosquee", mappedBy="commune")
     */
    private $mosquees;

    /**
     * @ORM\OneToMany(targetEntity="Activite", mappedBy="commune")
     */
    private $activites;
    
    /**
     * @ORM\OneToMany(targetEntity="Converti", mappedBy="commune")
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

    public function getVille(): ?Ville
    {
        return $this->ville;
    }

    public function setVille($ville): self
    {
        $this->ville = $ville;

        return $this;
    }

    public function getNombreHabitants(): ?int
    {
        return $this->nombre_habitants;
    }

    public function setNombreHabitants(int $nombre_habitants): self
    {
        $this->nombre_habitants = $nombre_habitants;

        return $this;
    }

    public function getTauxMusulmans(): ?int
    {
        return $this->taux_musulmans;
    }

    public function setTauxMusulmans(int $taux_musulmans): self
    {
        $this->taux_musulmans = $taux_musulmans;

        return $this;
    }

    public function getcreated_at(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setcreated_at(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getupdated_at(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setupdated_at(\DateTimeInterface $updated_at): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }


    /**
     * @return Collection|Mosquee[]
     */
    public function getMosquees(): Collection
    {
        return $this->mosquees;
    }

    public function addMosquee(Mosquee $mosquee): self
    {
        if (!$this->mosquees->contains($mosquee)) {
            $this->mosquees[] = $mosquee;
            $mosquee->setCommune($this);
        }

        return $this;
    }

    public function removeMosquee(Mosquee $mosquee): self
    {
        if ($this->mosquees->contains($mosquee)) {
            $this->mosquees->removeElement($mosquee);
            // set the owning side to null (unless already changed)
            if ($mosquee->getCommune() === $this) {
                $mosquee->setCommune(null);
            }
        }

        return $this;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Commune
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt;
     *
     * @return Commune
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Add activite
     *
     * @param \ISI\ORGBundle\Entity\Activite $activite
     *
     * @return Commune
     */
    public function addActivite(\ISI\ORGBundle\Entity\Activite $activite)
    {
        $this->activites[] = $activite;

        return $this;
    }

    /**
     * Remove activite
     *
     * @param \ISI\ORGBundle\Entity\Activite $activite
     */
    public function removeActivite(\ISI\ORGBundle\Entity\Activite $activite)
    {
        $this->activites->removeElement($activite);
    }

    /**
     * Get activites
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getActivites()
    {
        return $this->activites;
    }

    /**
     * Add converti
     *
     * @param \ISI\ORGBundle\Entity\Converti $converti
     *
     * @return Commune
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
